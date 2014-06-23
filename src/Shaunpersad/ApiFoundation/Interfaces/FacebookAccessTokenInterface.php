<?php
namespace Shaunpersad\ApiFoundation\Interfaces;

use Facebook\GraphUser;

interface FacebookAccessTokenInterface {

    /**
     * Gets a User that has logs in with Facebook, given their Facebook id.
     *
     * @param $facebook_id
     * @return array
     */
    public function getUserInfoByFacebookId($facebook_id);

    /**
     * Creates a User that logs in with Facebook, based on a GraphUser
     *
     * @param GraphUser $facebook_user
     */
    public function createFacebookUser(GraphUser $facebook_user);
} 