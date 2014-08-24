<?php


namespace Shaunpersad\ApiFoundation\GPlus;


use App;
use Google_Client;
use Session;

class LaravelGPlusRedirectLoginHelper
{


    /**
     * @var Google_Client|null
     */
    protected $google_client = null;

    /**
     * Constructs a RedirectLoginHelper for a given client_id and redirect_url.
     *
     * @param string $redirect_url The URL Google should redirect users to
     *                            after login
     */
    public function __construct($redirect_url = null)
    {
        $this->google_client = App::make('google_client');
        if (!empty($redirect_url)) {

            $this->google_client->setRedirectUri($redirect_url);
        } else {
            $this->google_client->setRedirectUri('postmessage');
        }
        $this->google_client->setAccessType('offline');
    }

    /**
     * Stores CSRF state and returns a URL to which the user should be sent to
     *   in order to continue the login process with Facebook.  The
     *   provided redirectUrl should invoke the handleRedirect method.
     *
     * @param array $scope List of permissions to request during login
     *
     * @return string
     */
    public function getLoginUrl($scope = array())
    {
        $state = md5(rand());
        $this->google_client->setState($state);
        $this->google_client->setScopes($scope);

        return $this->google_client->createAuthUrl();
    }


    /**
     * @return Google_Client|mixed|null
     */
    public function getClientFromRedirect()
    {
        $this->google_client->authenticate(\Input::get('code'));
        return $this->google_client;
    }

}