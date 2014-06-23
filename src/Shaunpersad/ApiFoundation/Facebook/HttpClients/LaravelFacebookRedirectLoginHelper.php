<?php


namespace Shaunpersad\ApiFoundation\Facebook\HttpClients;


use Config;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;
use Session;

class LaravelFacebookRedirectLoginHelper extends FacebookRedirectLoginHelper {


    /**
     * @var string Prefix to use for session variables
     */
    private $sessionPrefix = 'FBRLH_';

    private $app_id;
    private $app_secret;

    /**
     * Constructs a RedirectLoginHelper for a given appId and redirectUrl.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to
     *                            after login
     * @param string $appId The application id
     * @param string $appSecret The application secret
     * @throws (\LoginException)
     */
    public function __construct($redirectUrl, $appId = null, $appSecret = null)
    {
        if (!empty($appId)) {

            $this->app_id = $appId;
        } elseif ($appId = Config::get('api-foundation::fb_app_id')) {

            $this->app_id = $appId;
        } else {

            throw new \LogicException('Facebook APP ID not set.');
        }

        if (!empty($appSecret)) {

            $this->app_secret = $appSecret;
        } elseif ($appSecret = Config::get('api-foundation::fb_app_secret')) {

            $this->app_secret = $appSecret;
        } else {

            throw new \LogicException('Facebook APP SECRET not set.');
        }

        FacebookSession::setDefaultApplication($this->app_id, $this->app_secret);

        parent::__construct($redirectUrl, $this->app_id, $this->app_secret);
    }

    /**
     * Stores a state string in session storage for CSRF protection.
     *
     * @param string $state
     */
    protected function storeState($state)
    {
        Session::put($this->sessionPrefix . 'state', $state);
    }

    /**
     * Loads a state string from session storage for CSRF validation.  May return
     *   null if no object exists.
     *
     * @return string|null
     */
    protected function loadState()
    {
        $this->state = Session::get($this->sessionPrefix . 'state');
        return $this->state;
    }
} 