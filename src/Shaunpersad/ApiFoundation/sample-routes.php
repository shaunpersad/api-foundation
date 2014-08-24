<?php

use Facebook\FacebookRequestException;
use Shaunpersad\ApiFoundation\Facebook\HttpClients\LaravelFacebookRedirectLoginHelper;
use Shaunpersad\ApiFoundation\GPlus\LaravelGPlusRedirectLoginHelper;
use Shaunpersad\ApiFoundation\Http\ErrorResponse;
use Shaunpersad\ApiFoundation\Http\OAuthRequest;
use Shaunpersad\ApiFoundation\Http\OAuthResponse;
use Shaunpersad\ApiFoundation\Http\SuccessResponse;

Route::pattern('id', '[0-9]+');

/**
 * TEST URL.
 * Will redirect you to Google+ to log in to the app.
 * This is an example of the server-side-flow: https://developers.google.com/+/web/signin/server-side-flow
 *
 * In, for example, a mobile app, this route would be useless, as the mobile app itself
 * would be required to redirect the user to Google (usually via the Google+ SDK)
 */
Route::get('get-gplus-login', function() {

    $helper = new LaravelGPlusRedirectLoginHelper($_ENV['url'].'/gplus-login-redirect');
    $loginUrl = $helper->getLoginUrl(array('https://www.googleapis.com/auth/plus.login','email'));

    return Redirect::to($loginUrl);
});

/**
 * TEST URL.
 * This presents you with a page that has a Google+ login button.
 * This is an example of the redirect-uri-flow: https://developers.google.com/+/web/signin/redirect-uri-flow.
 * If you wish to make use of this example, please publish the package's views with:
 * php artisan view:publish shaunpersad/api-foundation
 *
 * In, for example, a mobile app, this route would be useless, as the mobile app itself
 * would be required to redirect the user to Google (usually via the Google+ SDK)
 */
Route::get('gplus-login-js', function() {

    $client_id = \Config::get('api-foundation::gplus_client_id');

    return View::make('gplus-login', array('gplus_client_id' => $client_id));
});


/**
 * TEST URL.
 * Will process the Google+ login and spit out Google access token data.
 * You can then pass this Google Access Token to the get-token endpoint,
 * to exchange it for one of our access tokens.
 *
 * Note that there are two flows supported: redirect-uri-flow - https://developers.google.com/+/web/signin/redirect-uri-flow
 * and server-side-flow - https://developers.google.com/+/web/signin/server-side-flow
 *
 * redirect-uri-flow is assumed unless a gplus_flow parameter is set.
 * You can test out the redirect-uri-flow via the /gplus-login-js route.
 * You can test out the server-side-flow via the /get-gplus-login route.
 *
 *
 * In, for example, a mobile app, this route would be useless, as the mobile app itself
 * would be required to get the Google+ access token (usually via the Google+ SDK)
 */
Route::any('gplus-login-redirect', function() {


    $redirect_url = null;

    $flow = Input::get('gplus_flow', 'redirect-uri-flow');

    if ($flow == 'redirect-uri-flow') {

        $redirect_url = $_ENV['url'].'/gplus-login-redirect';
    }

    $helper = new LaravelGPlusRedirectLoginHelper($redirect_url);

    $client = $helper->getClientFromRedirect();

    if (!empty($client)) {
        // Logged in
        return 'access_token: '. $client->getAccessToken();
    }

    //return Input::all();

});

/**
 * TEST URL.
 * Will redirect you to Facebook to log in to the app.
 *
 * In, for example, a mobile app, this route would be useless, as the mobile app itself
 * would be required to redirect the user to Facebook (usually via the Facebook SDK)
 */
Route::get('get-facebook-login', function(){

    $helper = new LaravelFacebookRedirectLoginHelper($_ENV['url'].'/facebook-login-redirect');
    $loginUrl = $helper->getLoginUrl(array('email'));

    return Redirect::to($loginUrl);
});



/**
 * TEST URL.
 * Will process the Facebook login and spit out a Facebook Access Token.
 * You can then pass this Facebook Access Token to the get-token endpoint,
 * to exchange it for one of our access tokens.
 *
 * In, for example, a mobile app, this route would be useless, as the mobile app itself
 * would be required to get the facebook access token (usually via the Facebook SDK)
 */
Route::get('facebook-login-redirect', function() {

    $helper = new LaravelFacebookRedirectLoginHelper($_ENV['url'].'/facebook-login-redirect');
    try {
        $session = $helper->getSessionFromRedirect();
    } catch(FacebookRequestException $ex) {
        // When Facebook returns an error

        return $ex->getMessage();

    } catch(\Exception $ex) {
        // When validation fails or other local issues
        return $ex->getMessage();
    }
    if (!empty($session)) {
        // Logged in

        return 'access_token: '. $session->getToken();
    }
});

/**
 * This is where the authorization code will be outputted if grant_type = 'authorization_code' (explicit).
 * If implicit, the access_token will be outputted as a URL FRAGMENT (#) instead, so there will be no need to use the get-token endpoint.
 */
Route::get('login-redirect', array('as' => 'authorize_redirect', function() {

    if (Input::has('error')) {

        return Input::get('error_description');
    }
    if (Input::has('code')) {

        return 'code: '.Input::get('code');

    }
    return 'Check the URL fragment for your access_token';

}));

/**
 * This is the login dialog page
 * You must also have the response_type, client_id, state, and redirect_uri
 * set in the URL query, with
 * response_type = "code" if not implicit ("token" if implicit)
 * client_id = your client id,
 * state = any random thing,
 * redirect_uri = a valid uri from the database.
 *
 * You should specify two callbacks in the 'authorize_request' parameters:
 * 'validate_success_callback' handles what to do if there is nothing wrong with
 * the request.  Typically, this should result in a view showing a form where the
 * user can log in and either authorize or unauthorize this app.
 * 'validate_error_callback' handles what to do if there is an error, e.g. forgetting
 * any of the above required URL query parameters.
 *
 * APIOAuthRequest is an extension of Symfony's Request object,
 * while APIOAuthResposne is an extension of Symfony's JsonResponse object.
 */
Route::get('authorize', array('as'=> 'authorize_request', function()
{

    return App::make(
        'authorize_request',
        array(
            'validate_success_callback' => function(OAuthRequest $request, OAuthResponse $response) {

                    $error = $request->get('error_description', '');
                    return '<p class="error">'.$error.'</p>
                    <form method="post">
                      <label>Do You Authorize TestClient?</label><br />
                      <input type="text" name="email" placeholder="email" />
                      <input type="text" name="password" placeholder="password" />
                      <input type="submit" name="authorized" value="yes">
                      <input type="submit" name="authorized" value="no">
                    </form>';

                },
            'validate_error_callback' => function(OAuthRequest $request, OAuthResponse $response) {

                    $response_params = $response->getOriginalParams();

                    $error_description = 'Invalid request';

                    if (!empty($response_params['error_description'])) {

                        $error_description = $response_params['error_description'];
                    }

                    return $error_description;
                }
        )
    );
}));

/**
 * This handles the form data from the login dialog page.
 * This will then return a code in the url that you can then use with the get-token endpoint,
 * using grant_type = authorization_code
 */
Route::post('authorize', array('as'=> 'authorize_response', function()
{

    $is_authorized = Input::get('authorized') === 'yes';

    /*
     * Attempts to log the user in.
     * Favored instead of simply validating the user.
     */
    if (Auth::attempt(array('email' => Input::get('email'), 'password' => Input::get('password')))) {

        $user = Auth::user();

        $params = array(
            'is_authorized' => $is_authorized,
            'user_id' => Auth::id()
        );

        return App::make('authorize_response', $params);

        /*
         * Return the user to the login page, with an error message.
         */
    } else {

        $request = Request::instance();

        $query_params = $request->query->all();

        return Redirect::route(
            'authorize_request',
            array_merge(
                $query_params,
                array(
                    'error' => 'not_found',
                    'error_description' => 'User not found.'
                )
            )
        );
    }


}));

/**
 * All API endpoints are defined here.
 */
Route::group(array('prefix' => 'api'), function()
{

    Route::group(array('prefix' => 'v1'), function() {



        /**
         * NOTE: 'authorization_code' (explicit and implicit), 'password', 'client_credentials', 'fb_access_token' and 'refresh_token' grant types are supported.
         * 'password' will be the main method for getting access tokens,
         * 'fb_access_token' will be used for processing facebook logins,
         * while the 'refresh_token' method will be used to renew access tokens if the user is still logged in on the app.
         * 'authorization_code' is the typical three-legged OAuth2.0 approach, but it's a bit much for most applications.
         *
         *
         * Must POST to the 'get-token' endpoint to receive a token.
         * Must have the 'grant_type' param as part of the POST data.
         * 'grant_type' can be 'authorization_code', 'password', 'client_credentials', or 'refresh_token' to this endpoint.
         *
         * if 'grant_type' = 'authorization_code' and you have an authorization code (from the 'authorize' endpoint)
         * the 'code' param must also be present in the POST data,
         * set to the code sent by the 'authorize' endpoint.
         *
         * if 'grant_type' = 'password', the 'username', and 'password' params must also be present,
         * along with the 'client_id' and 'client_secret' (if available) params in the POST data,
         * or in the Authorize HTTP Header (Http Basic).
         * 'username' = user's email address.
         *
         * if 'grant_type' = 'fb_access_token', the 'fb_access_token' param must also be present in the POST data,
         * set to a Facebook access token received from Facebook.
         *
         * if 'grant_type' = 'client_credentials', the only other required params are
         * the 'client_id' and 'client_secret' (if available) params in the POST data,
         * or in the Authorize HTTP Header (Http Basic).  Note: there is no User associated with tokens
         * generated by this grant type.
         *
         * if 'grant_type' = 'refresh_token', the 'refresh_token' param must also be present.
         * Refresh tokens are generated by requests initially made with 'authorization_code' or 'password' grant types.
         * They are sent back in the data along with the access_token.
         * The refresh_token sent back can then be supplied to receive another access_token and another refresh_token.
         * This method is used to keep a user logged in, after their access_token expires.
         *
         */
        Route::post('get-token', array('as'=> 'token_endpoint', function()
        {
            return App::make('token_response');
        }));


        /**
         * Only authenticated users can access these endpoints.
         */
        Route::group(array('before' => 'requires_oauth_token'), function()
        {

            Route::any('me', function() {

                return SuccessResponse::make(Auth::user());
            });
        });

    });

});
App::missing(function($exception)
{
    if (Request::is('api/*')) {

        return ErrorResponse::make('API endpoint for this verb not found.', 404);
    }
    else {

        return 'Page not found.';
    }
});