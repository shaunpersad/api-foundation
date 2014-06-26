<?php
return array(
    'client_table' => 'oauth_clients',
    'access_token_table' => 'oauth_access_tokens',
    'refresh_token_table' => 'oauth_refresh_tokens',
    'code_table' => 'oauth_authorization_codes',
    'user_table' => 'users',
    'user_table_id_field' => 'id',
    'user_table_username_field' => 'email',
    'user_table_password_field' => 'password',
    'jwt_table'  => 'oauth_jwt',
    'scope_table'  => 'oauth_scopes',
    'public_key_table'  => 'oauth_public_keys',
    'supported_grant_types' => array(
        'authorization_code',
        'password',
        'client_credentials',
        'refresh_token',
        'fb_access_token'
    ),
    'fb_app_id' => null,
    'fb_app_secret' => null
);