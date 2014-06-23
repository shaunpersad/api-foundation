<?php


namespace Shaunpersad\ApiFoundation\Models;


class OauthScope extends \Eloquent {

    protected $guarded = array();

    public function __construct(array $attributes = array()) {

        $this->table = \Config::get('api-foundation::scope_table');

        parent::__construct($attributes);
    }
}