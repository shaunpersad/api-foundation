<?php


namespace Shaunpersad\ApiFoundation\OAuth2\GrantType;


use App;
use Google_Client;
use Google_Service_Oauth2;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Shaunpersad\ApiFoundation\GPlus\GPlusUserFromGPlusAccessToken;
use Shaunpersad\ApiFoundation\Interfaces\GPlusAccessTokenInterface;
class GPlusServerCode implements GrantTypeInterface {


    /**
     * @var Google_Client
     */
    protected $google_client;

    protected $userInfo;

    protected $storage;

    public function __construct(GPlusAccessTokenInterface $storage) {

        $this->storage = $storage;

        $this->google_client = App::make('google_client');
        $this->google_client->setRedirectUri('postmessage');
    }

    public function getQuerystringIdentifier()
    {
        return 'gplus_server_code';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {

        $identifier = $this->getQuerystringIdentifier();

        if (!$request->request($identifier)) {

            $response->setError(400, 'invalid_request', 'Missing parameters: "'.$identifier.'" required');
            return null;
        }

        $gplus_server_code = $request->request($identifier);

        try {

            $this->google_client->authenticate($gplus_server_code);

            $token_data = json_decode($this->google_client->getAccessToken());

            $gplus_access_token = $token_data->access_token;

            $token_service = new Google_Service_Oauth2($this->google_client);

            $token_info = $token_service->tokeninfo(array('access_token' => $gplus_access_token));

            if ($token_info->getAudience() != \Config::get('api-foundation::gplus_client_id')) {

                $response->setError(400, 'invalid_request', "Google+ access token audience does not match.");
                return null;
            }

            $gplus_id = $token_info->getUserId();
            $email = $token_info->getEmail();

            if (empty($email)) {

                $response->setError(400, 'invalid_request', "User's Google+ email addresses are not available.");
                return null;
            }

            if (empty($gplus_id)) {

                $response->setError(400, 'invalid_request', "User's Google+ id not available.");
                return null;
            }

            $userInfo = $this->storage->getUserInfoByGPlusId($gplus_id);

            if (empty($userInfo)) {

                $gplus_user = GPlusUserFromGPlusAccessToken::make($this->google_client, $gplus_access_token);

                $this->storage->createGPlusUser($gplus_user, $token_info);

                $userInfo = $this->storage->getUserInfoByGPlusId($gplus_id);
            }

        } catch (\Google_Service_Exception $e) {

            $response->setError($e->getCode(), 'invalid_request', "Google Plus server code is invalid.");
            return null;
        } catch (\Google_Auth_Exception $e) {

            $response->setError($e->getCode(), 'invalid_request', "Google Plus server code is invalid.");
            return null;
        }


        if (empty($userInfo)) {
            $response->setError(400, 'invalid_grant', 'Unable to retrieve user information.');

            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException("You must set the user_id on the array.");
        }

        $this->userInfo = $userInfo;

        return true;

    }

    public function getClientId()
    {
        return null;
    }

    public function getUserId()
    {
        return $this->userInfo['user_id'];
    }

    public function getScope()
    {
        return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }
} 