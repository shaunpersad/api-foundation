<?php namespace Shaunpersad\ApiFoundation;

use App;
use Auth;
use Illuminate\Support\ServiceProvider;
use OAuth2\Server;
use Request;
use Route;
use Shaunpersad\ApiFoundation\Http\ErrorResponse;
use Shaunpersad\ApiFoundation\Http\OAuthRequest;
use Shaunpersad\ApiFoundation\Http\OAuthResponse;
use Shaunpersad\ApiFoundation\Http\SuccessResponse;
use Shaunpersad\ApiFoundation\OAuth2\Storage\ModelStorage;
use User;

class ApiFoundationServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('shaunpersad/api-foundation');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
        $this->makeOauth2();
        $this->makeRouteFilter();
        $this->makeAuthorizeRequest();
        $this->makeAuthorizeResponse();
        $this->makeTokenResponse();
        $this->makeAPIResponseArray();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}


    public function getAllGrantTypes() {

        return array(
            'authorization_code' => '\OAuth2\GrantType\AuthorizationCode',
            'password' => '\OAuth2\GrantType\UserCredentials',
            'client_credentials' => '\OAuth2\GrantType\ClientCredentials',
            'refresh_token' => '\OAuth2\GrantType\RefreshToken',
            'fb_access_token' => '\Shaunpersad\ApiFoundation\OAuth2\GrantType\FacebookAccessToken'
        );
    }

    /**
     * Creates an instance of the oauth2 server to handle all things oauth-related.
     */
    protected function makeOauth2() {

        $this->app->singleton('oauth2', function() {

            /*
             * Custom storage for application
             */
            $storage = new ModelStorage();

            $refresh_token_lifetime = strtotime('+2 years') - time();
            $access_lifetime = strtotime('+1 year') - time();
            $auth_code_lifetime = strtotime('+2 minutes') - time();

            $config = array(
                'always_issue_new_refresh_token' => true,
                'refresh_token_lifetime'         => $refresh_token_lifetime,
                'access_lifetime'                => $access_lifetime,
                'auth_code_lifetime'             => $auth_code_lifetime,
                'allow_implicit' => true,
            );

            $server = new Server($storage, $config);

            $all_grant_types = $this->getAllGrantTypes();

            $supported_grant_types = \Config::get('api-foundation::supported_grant_types');

            foreach ($supported_grant_types as $grant_type) {

                if (array_key_exists($grant_type, $all_grant_types)) {

                    $grant_type_class = $all_grant_types[$grant_type];
                    $server->addGrantType(new $grant_type_class($storage, $config));
                }
            }
            return $server;
        });
    }

    /**
     * Allows access only if a valid access token (access_token) is supplied.
     */
    protected function makeRouteFilter() {

        Route::filter('requires_oauth_token', function()
        {
            $bridgedRequest = OAuthRequest::createFromRequest(Request::instance());
            $bridgedResponse = new OAuthResponse();

            $oauth2 = $this->app->make('oauth2');

            if ($oauth2->verifyResourceRequest($bridgedRequest, $bridgedResponse)) {

                $token = $oauth2->getAccessTokenData($bridgedRequest);

                $user = User::find($token['user_id']);

                if ($user) {

                    App::instance('access_token_data', $token);
                    Auth::loginUsingId($token['user_id']);
                } else {

                    return ErrorResponse::make('User not found.', 410);
                }

            }
            else {

                return ErrorResponse::make('Unauthorized.', 401);
            }
        });

    }

    /**
     * Basic response to having an invalid authorize request.
     *
     * @param OAuthRequest $request
     * @param OAuthResponse $response
     * @return string
     */
    public function defaultValidateAuthorizeRequestErrorCallback(OAuthRequest $request, OAuthResponse $response) {

        $response_params = $response->getOriginalParams();

        $error_description = 'Invalid request';

        if (!empty($response_params['error_description'])) {

            $error_description = $response_params['error_description'];
        }

        return $error_description;
    }

    /**
     * Basic response to having a valid authorize request.
     *
     * @param OAuthRequest $request
     * @param OAuthResponse $response
     * @return string
     */
    public function defaultValidateAuthorizeRequestSuccessCallback(OAuthRequest $request, OAuthResponse $response) {

        $error = $request->get('error_description', '');
        return '<p class="error">'.$error.'</p>
                    <form method="post">
                      <label>Do You Authorize TestClient?</label><br />
                      <input type="text" name="email" placeholder="email" />
                      <input type="text" name="password" placeholder="password" />
                      <input type="submit" name="authorized" value="yes">
                      <input type="submit" name="authorized" value="no">
                    </form>';
    }

    /**
     * This will typically either show a form for the user to log in and authorize|deauthorize,
     * or show the error.
     */
    protected function makeAuthorizeRequest() {

        $this->app->bind('authorize_request', function($app, $parameters = array()) {

            $request = OAuthRequest::createFromRequest(Request::instance());
            $response = new OAuthResponse();

            $oauth2 = $this->app->make('oauth2');

            // validate the authorize request
            if (!$oauth2->validateAuthorizeRequest($request, $response)) {

                if (!empty($parameters['validate_error_callback'])) {

                    return call_user_func($parameters['validate_error_callback'], $request, $response);
                } else {
                    return $this->defaultValidateAuthorizeRequestErrorCallback($request, $response);
                }
            }

            if (!empty($parameters['validate_success_callback'])) {

                return call_user_func($parameters['validate_success_callback'], $request, $response);
            } else {
                return $this->defaultValidateAuthorizeRequestSuccessCallback($request, $response);
            }

        });

    }

    /**
     * This should redirect the user to the specified redirect_uri,
     * along with either the code or access_token, depending if
     * the grant type is explicit or implicit
     */
    protected function makeAuthorizeResponse() {

        $this->app->bind('authorize_response', function($app, $parameters = array()) {

            $is_authorized = false;
            $user_id = null;

            extract($parameters);

            $request = OAuthRequest::createFromRequest(Request::instance());
            $response = new OAuthResponse();

            $response = $this->app->make('oauth2')->handleAuthorizeRequest($request, $response, $is_authorized, $user_id);

            return $response;

        });

    }

    /**
     * Respond with access_token data.
     */
    protected function makeTokenResponse() {

        $this->app->bind('token_response', function() {

            $request = OAuthRequest::createFromRequest(Request::instance());
            $response = new OAuthResponse();

            $response = $this->app->make('oauth2')->handleTokenRequest($request, $response);

            $code = $response->getStatusCode();
            $response_params = $response->getOriginalParams();

            if ($code != 200 && !empty($response_params['error'])) {

                return ErrorResponse::make($response_params['error_description'], $code);
            } else {
                return SuccessResponse::make($response_params);

            }
        });
    }

    /**
     * Standardizes all of the API's responses with this format.
     */
    protected function makeAPIResponseArray() {


        $this->app->bind('api_response_array', function($app, $parameters = array()) {

            $defaults = array(
                'content' => '',
                'error' => false,
                'error_description' => null
            );

            return array_merge($defaults, $parameters);
        });
    }

}
