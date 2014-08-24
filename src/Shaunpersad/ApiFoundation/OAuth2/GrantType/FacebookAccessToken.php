<?php


namespace Shaunpersad\ApiFoundation\OAuth2\GrantType;


use Config;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Shaunpersad\ApiFoundation\Interfaces\FacebookAccessTokenInterface;

class FacebookAccessToken implements GrantTypeInterface {


    protected $userInfo;

    protected $storage;

    /**
     * @param FacebookAccessTokenInterface $storage
     * REQUIRED Storage class for retrieving user credentials information
     */
    public function __construct(FacebookAccessTokenInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getQuerystringIdentifier()
    {
        return 'fb_access_token';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {

        $identifier = $this->getQuerystringIdentifier();

        if (!$request->request($identifier)) {

            $response->setError(400, 'invalid_request', 'Missing parameters: "'.$identifier.'" required');
            return null;
        }

        $fb_app_id = Config::get('api-foundation::fb_app_id');
        $fb_app_secret = Config::get('api-foundation::fb_app_secret');

        if (empty($fb_app_id)) {

            throw new \LogicException('Facebook APP ID not set.');
        }

        if (empty($fb_app_secret)) {

            throw new \LogicException('Facebook APP SECRET not set.');
        }

        FacebookSession::setDefaultApplication($fb_app_id, $fb_app_secret);

        try {
            $session = new FacebookSession($request->request($identifier));

        } catch(FacebookRequestException $e) {

            $response->setError(401, 'invalid_grant', $e->getMessage());
            return null;

        } catch(\Exception $e) {

            $response->setError(401, 'invalid_grant', $e->getMessage());
            return null;
        }

        if(!empty($session)) {

            try {

                $user_profile = (new FacebookRequest(
                    $session, 'GET', '/me'
                ))->execute()->getGraphObject(GraphUser::className());

                $email = $user_profile->getProperty('email');

                if (empty($email)) {

                    $response->setError(400, 'invalid_request', "User's email address not available.");
                    return null;
                } else {

                    $userInfo = $this->storage->getUserInfoByFacebookId($user_profile->getId());

                    if (empty($userInfo)) {

                        $this->storage->createFacebookUser($user_profile);

                        $userInfo = $this->storage->getUserInfoByFacebookId($user_profile->getId());
                    }

                }

            } catch(FacebookRequestException $e) {

                $response->setError(401, 'invalid_grant', $e->getMessage());
                return null;
            }

        } else {

            $response->setError(401, 'invalid_grant', 'Facebook session could not be set with supplied access token.');
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