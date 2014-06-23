<?php


namespace Shaunpersad\ApiFoundation\Models;


class OauthClient extends \Eloquent {

    protected $primaryKey = 'client_id';
    public $incrementing = false;
    protected $guarded = array();

    public function __construct(array $attributes = array()) {

        $this->table = \Config::get('api-foundation::client_table');

        parent::__construct($attributes);
    }

    public function accessTokens()
    {
        return $this->hasMany('OauthAccessToken');
    }

    public function authorizationCodes()
    {
        return $this->hasMany('OauthAuthorizationCode');
    }

    public function jwt()
    {
        return $this->hasMany('OauthJwt');
    }

    public function refreshTokens()
    {
        return $this->hasMany('OauthRefreshToken');
    }
} 