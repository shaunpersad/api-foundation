<?php


namespace Shaunpersad\ApiFoundation\Models;


class OauthAuthorizationCode extends \Eloquent {

    protected $primaryKey = 'authorization_code';
    public $incrementing = false;
    protected $guarded = array();

    public function __construct(array $attributes = array()) {

        $this->table = \Config::get('api-foundation::code_table');

        parent::__construct($attributes);
    }

    public function client() {

        return $this->belongsTo('OauthClient', 'client_id', 'client_id');
    }
} 