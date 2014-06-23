<?php


namespace Shaunpersad\ApiFoundation\Models;


class OauthAccessToken extends \Eloquent {

    protected $primaryKey = 'access_token';
    public $incrementing = false;
    protected $guarded = array();

    public function __construct(array $attributes = array()) {

        $this->table = \Config::get('api-foundation::access_token_table');

        parent::__construct($attributes);
    }

    public function client() {

        return $this->belongsTo('OauthClient', 'client_id', 'client_id');
    }

} 