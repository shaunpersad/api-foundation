<?php

namespace Shaunpersad\ApiFoundation\Models;

class OAuthUser extends \Eloquent {

    const LOGIN_TYPE_NORMAL = 'normal';
    const LOGIN_TYPE_FACEBOOK = 'facebook';

    public function __construct(array $attributes = array()) {

        $this->table = \Config::get('api-foundation::user_table');

        parent::__construct($attributes);

    }

    /**
     * Returns an OAuthUser via their username
     *
     * @param $username
     * @return OAuthUser
     */
    public static function getUserByUsername($username) {

        $username_field = \Config::get('api-foundation::user_table_username_field');

        return self::where($username_field, '=', $username)
            ->where('login_type', '=', self::LOGIN_TYPE_NORMAL)->first();
    }

} 