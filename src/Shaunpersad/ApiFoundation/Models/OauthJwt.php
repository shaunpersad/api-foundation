<?php


namespace Shaunpersad\ApiFoundation\Models;


class OauthJwt extends \Eloquent {

    protected $primaryKey = 'client_id';
    public $incrementing = false;
    protected $guarded = array();

    public function __construct(array $attributes = array()) {

        $this->table = \Config::get('api-foundation::jwt_table');

        parent::__construct($attributes);
    }

    public function client() {

        return $this->belongsTo('OauthClient', 'client_id', 'client_id');
    }
} 