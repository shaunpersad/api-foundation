<?php


namespace Shaunpersad\ApiFoundation\Interfaces;


use Google_Service_Oauth2_Tokeninfo;
use Google_Service_Plus_Person;

interface GPlusAccessTokenInterface {

    /**
     * Gets a User that has logs in with Google Plus, given their Google Plus id.
     *
     * @param $gplus_id;
     * @return array
     */
    public function getUserInfoByGPlusId($gplus_id);

    /**
     * Creates a User that logs in with Facebook, based on a GPlus user and their token info.
     *
     * @param Google_Service_Plus_Person $gplus_user
     * @param \Google_Service_Oauth2_Tokeninfo $token_info
     * @return
     */
    public function createGPlusUser(Google_Service_Plus_Person $gplus_user, Google_Service_Oauth2_Tokeninfo $token_info);
} 