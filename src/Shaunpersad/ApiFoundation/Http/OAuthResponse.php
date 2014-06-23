<?php
namespace Shaunpersad\ApiFoundation\Http;

use OAuth2\HttpFoundationBridge\Response;

class OAuthResponse extends Response {

    protected $original_params;

    public function setData($parameters = array()) {

        $this->original_params = $parameters;

        parent::setData($parameters);
    }

    public function getOriginalParams() {

        return $this->original_params;
    }
} 