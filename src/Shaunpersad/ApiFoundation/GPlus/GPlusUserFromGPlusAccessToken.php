<?php


namespace Shaunpersad\ApiFoundation\GPlus;


use Google_Client;
use Google_Http_Request;
use Google_Service_Oauth2;
use Google_Service_Plus;
use Google_Service_Plus_Person;

class GPlusUserFromGPlusAccessToken {

    /**
     * @param Google_Client $client
     * @param $gplus_access_token
     * @return Google_Service_Plus_Person
     */
    public static function make(Google_Client $client, $gplus_access_token) {

        $service = new Google_Service_Oauth2($client);

        $plus_service = new Google_Service_Plus($client);

        $servicePath = $plus_service->servicePath;

        $url = $servicePath.'people/me?access_token='.$gplus_access_token;

        $httpRequest = new Google_Http_Request($url);
        $httpRequest->setBaseComponent($client->getBasePath());
        $httpRequest->setExpectedClass('Google_Service_Plus_Person');
        /**
         * @var Google_Service_Plus_Person
         */
        $response = $client->execute($httpRequest);

        return $response;
    }
} 