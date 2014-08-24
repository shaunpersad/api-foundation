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
    'user_table_fb_id_field' => 'fb_id',
    'user_table_gplus_id_field' => 'gplus_id',
    'user_table_login_type_field' => 'login_type',
    'user_table_login_type_facebook' => \Shaunpersad\ApiFoundation\Models\OAuthUser::LOGIN_TYPE_FACEBOOK,
    'user_table_login_type_gplus' => \Shaunpersad\ApiFoundation\Models\OAuthUser::LOGIN_TYPE_GPLUS,
    'jwt_table'  => 'oauth_jwt',
    'scope_table'  => 'oauth_scopes',
    'public_key_table'  => 'oauth_public_keys',
    'supported_grant_types' => array(
        'authorization_code',
        'password',
        'client_credentials',
        'refresh_token',
        'fb_access_token',
        'gplus_access_token',
        'gplus_server_code'
    ),
    'fb_app_id' => null,
    'fb_app_secret' => null,
    'gplus_application_name' => 'API Project',
    'gplus_client_id' => '115594317690-muon4jan1iqupcu5lfndhm5pokh1nndm.apps.googleusercontent.com',
    'gplus_client_secret' => 'QWw5NAAguerCCvjZnc3tXHCD'
);