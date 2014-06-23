<?php


namespace Shaunpersad\ApiFoundation\OAuth2\Storage;


use Auth;
use Facebook\GraphUser;
use Hash;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\UserCredentialsInterface;
use Shaunpersad\ApiFoundation\Interfaces\FacebookAccessTokenInterface;
use Shaunpersad\ApiFoundation\Models\OauthAccessToken;
use Shaunpersad\ApiFoundation\Models\OauthAuthorizationCode;
use Shaunpersad\ApiFoundation\Models\OauthClient;
use Shaunpersad\ApiFoundation\Models\OauthRefreshToken;
use Shaunpersad\ApiFoundation\Models\OauthScope;
use Shaunpersad\ApiFoundation\Models\OAuthUser;

class ModelStorage  implements AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    ScopeInterface,
    FacebookAccessTokenInterface {

    public function __construct() {


    }

    /**
     * Fetch authorization code data (probably the most common grant type).
     *
     * Retrieve the stored data for the given authorization code.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be check with.
     *
     * @return array
     * An associative array as below, and NULL if the code is invalid
     * @code
     * return array(
     *     "client_id"    => CLIENT_ID,      // REQUIRED Stored client identifier
     *     "user_id"      => USER_ID,        // REQUIRED Stored user identifier
     *     "expires"      => EXPIRES,        // REQUIRED Stored expiration in unix timestamp
     *     "redirect_uri" => REDIRECT_URI,   // REQUIRED Stored redirect URI
     *     "scope"        => SCOPE,          // OPTIONAL Stored scope values in space-separated string
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1
     *
     * @ingroup oauth2_section_4
     */
    public function getAuthorizationCode($code)
    {
        $code = OauthAuthorizationCode::find($code)->toArray();

        $code['expires'] = strtotime($code['expires']);

        return $code;
    }

    /**
     * Take the provided authorization code values and store them somewhere.
     *
     * This function should be the storage counterpart to getAuthCode().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param string $redirect_uri
     * Redirect URI(s) to be stored in a space-separated string.
     * @param int $expires
     * Expiration to be stored as a Unix timestamp.
     * @param string $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @return bool
     * @ingroup oauth2_section_4
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        $oauth_code = $user = OauthAuthorizationCode::firstOrNew(array('authorization_code' => $code));

        $expires = date('Y-m-d H:i:s', $expires);

        $oauth_code->client_id = $client_id;
        $oauth_code->user_id = $user_id;
        $oauth_code->redirect_uri = $redirect_uri;
        $oauth_code->expires = $expires;
        $oauth_code->scope = $scope;

        return $oauth_code->save();
    }

    /**
     * once an Authorization Code is used, it must be expired
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
     *
     *    The client MUST NOT use the authorization code
     *    more than once.  If an authorization code is used more than
     *    once, the authorization server MUST deny the request and SHOULD
     *    revoke (when possible) all tokens previously issued based on
     *    that authorization code
     *
     */
    public function expireAuthorizationCode($code)
    {
        return OauthAuthorizationCode::destroy($code);
    }

    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be check with.
     *
     * @return array
     * An associative array as below, and return NULL if the supplied oauth_token
     * is invalid:
     * - client_id: Stored client identifier.
     * - expires: Stored expiration in unix timestamp.
     * - user_id: (optional) Stored user identifier.
     * - scope: (optional) Stored scope values in space-separated string.
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken($oauth_token)
    {
        $token = OauthAccessToken::find($oauth_token)->toArray();

        $token['expires'] = strtotime($token['expires']);

        return $token;
    }

    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param int $expires
     * Expiration to be stored as a Unix timestamp.
     * @param string $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @return bool
     * @ingroup oauth2_section_4
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {

        $access_token = OauthAccessToken::firstOrNew(array('access_token' => $oauth_token));

        $expires = date('Y-m-d H:i:s', $expires);

        $access_token->client_id = $client_id;
        $access_token->user_id = $user_id;
        $access_token->expires = $expires;
        $access_token->scope = $scope;

        return $access_token->save();
    }

    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return bool
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {

        $client = OauthClient::find($client_id);

        // make this extensible
        return $client && $client->client_secret == $client_secret;
    }

    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return bool
     * TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
    {

        $client = OauthClient::find($client_id);

        return empty($client->client_secret);

    }

    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return array
     * Client details. The only mandatory key in the array is "redirect_uri".
     * This function MUST return FALSE if the given client does not exist or is
     * invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     * @code
     * return array(
     *     "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *     "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *     "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *     "user_id"      => USER_ID,           // OPTIONAL the user identifier associated with this client
     *     "scope"        => SCOPE,             // OPTIONAL the scopes allowed for this client
     * );
     * @endcode
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails($client_id)
    {
        return OauthClient::find($client_id)->toArray();
    }

    /**
     * Get the scope associated with this client
     *
     * @param $client_id
     * @return bool|null
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $grant_type
     * Grant type to be check with
     *
     * @return bool
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /**
     * @param $username
     * @return array|bool
     * ARRAY the associated "user_id" and optional "scope" values
     * @code
     * array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($username)
    {
        $user_info = $this->getUserInfoByUsername($username);

        if ($user_info) {

            $id_field = \Config::get('api-foundation::user_table_id_field');

            return array_merge(
                array(
                    'user_id' => $user_info[$id_field]
                ),
                $user_info
            );
        }
        return false;
    }

    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return array
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Refresh token identifier.
     * - client_id: Client identifier.
     * - user_id: User identifier.
     * - expires: Expiration unix timestamp, or 0 if the token doesn't expire.
     * - scope: (optional) Scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    public function getRefreshToken($refresh_token)
    {
        $token = OauthRefreshToken::find($refresh_token)->toArray();

        $token['expires'] = strtotime($token['expires']);

        return $token;
    }

    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * Expiration timestamp to be stored. 0 if the token doesn't expire.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @return bool
     * @ingroup oauth2_section_6
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        $expires = date('Y-m-d H:i:s', $expires);

        $token = new OauthRefreshToken();

        $token->refresh_token = $refresh_token;
        $token->client_id = $client_id;
        $token->user_id = $user_id;
        $token->expires = $expires;
        $token->scope = $scope;

        return $token->save();
    }

    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expired.
     *
     * @return int
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken($refresh_token)
    {
        return OauthRefreshToken::destroy($refresh_token);
    }

    /**
     * Check if the provided scope exists.
     *
     * @param $scope
     * A space-separated string of scopes.
     *
     * @return bool
     * TRUE if it exists, FALSE otherwise.
     */
    public function scopeExists($scope)
    {
        $scope = explode(' ', $scope);

        $count = OauthScope::whereIn('scope', $scope)->count('scope');

        return $count == count($scope);
    }

    /**
     * The default scope to use in the event the client
     * does not request one. By returning "false", a
     * request_error is returned by the server to force a
     * scope request by the client. By returning "null",
     * opt out of requiring scopes
     *
     * @param $client_id
     * An optional client id that can be used to return customized default scopes.
     *
     * @return string|null
     * string representation of default scope, null if
     * scopes are not defined, or false to force scope
     * request by the client
     *
     * ex:
     *     'default'
     * ex:
     *     null
     */
    public function getDefaultScope($client_id = null)
    {
        $results = OauthScope::where('is_default', '=', true)->get(array('scope'))->toArray();

        if (!empty($results)) {
            $default_scope = array_map(function ($row) {
                return $row['scope'];
            }, $results);

            return implode(' ', $default_scope);
        }

        return null;
    }

    /**
     * OVERRIDE THIS IF NECESSARY
     *
     * Grant access tokens for basic user credentials.
     *
     * Check the supplied username and password for validity.
     *
     * You can also use the $client_id param to do any checks required based
     * on a client, if you need that.
     *
     * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
     *
     * @param $username
     * Username to be check with.
     * @param $password
     * Password to be check with.
     *
     * @return bool
     * TRUE if the username and password are valid, and FALSE if it isn't.
     * Moreover, if the username and password are valid, and you want to
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.3
     *
     * @ingroup oauth2_section_4
     */
    public function checkUserCredentials($username, $password)
    {
        if ($user_info = $this->getUserInfoByUsername($username)) {

            return $this->checkPassword($username, $password);
        }

        return false;
    }

    /**
     * OVERRIDE THIS IF NECESSARY
     *
     * @param string $username
     * @return array|bool
     * ARRAY the associated "user_id" and optional other values
     * @code
     * array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id
     * );
     * @endcode
     */
    public function getUserInfoByUsername($username) {

        $user_info = OAuthUser::getUserByUsername($username)->toArray();

        if (empty($user_info)) {

            return false;
        }

        $id_field = \Config::get('api-foundation::user_table_id_field');

        return array_merge(array(
            'user_id' => $user_info[$id_field]
        ), $user_info);

    }

    /**
     * OVERRIDE THIS IF NECESSARY, e.g. you have a custom Auth
     *
     * @param $username
     * @param $password
     * @return bool
     */
    public function checkPassword($username, $password) {

        $username_field = \Config::get('api-foundation::user_table_username_field');
        $password_field = \Config::get('api-foundation::user_table_password_field');

        return Auth::validate(
            array(
                $username_field => $username,
                $password_field => $password
            )
        );
    }

    /**
     * OVERRIDE THIS IF NECESSARY
     *
     * @param $facebook_id
     * @return array|bool
     * ARRAY the associated "user_id" and optional other values
     * @code
     * array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id
     * );
     * @endcode
     */
    public function getUserInfoByFacebookId($facebook_id) {

        $user = OAuthUser::where('fb_id', '=', $facebook_id)
            ->where('login_type', '=', OAuthUser::LOGIN_TYPE_FACEBOOK)
            ->first();

        if (!empty($user)) {

            $user_info = $user->toArray();
            $id_field = \Config::get('api-foundation::user_table_id_field');

            return array_merge(array(
                'user_id' => $user_info[$id_field]
            ), $user_info);
        }

        return false;

    }

    /**
     * OVERRIDE THIS IF NECESSARY
     *
     * @param GraphUser $facebook_user
     */
    public function createFacebookUser(GraphUser $facebook_user) {

        $user = new OAuthUser();
        $user->email = $facebook_user->getProperty('email');
        $user->password = Hash::make(str_random(20));
        $user->fb_id = $facebook_user->getId();
        $user->first_name = $facebook_user->getFirstName();
        $user->last_name = $facebook_user->getLastName();
        $user->login_type = OAuthUser::LOGIN_TYPE_FACEBOOK;
        $user->save();
    }

}