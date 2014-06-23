<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        $schema = <<<SCHEMA
CREATE TABLE oauth_clients (client_id VARCHAR(80) NOT NULL, client_secret VARCHAR(80) NOT NULL, redirect_uri VARCHAR(2000) NOT NULL, grant_types VARCHAR(80), scope VARCHAR(100), user_id VARCHAR(80), CONSTRAINT client_id_pk PRIMARY KEY (client_id), created_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00');
CREATE TABLE oauth_access_tokens (access_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT access_token_pk PRIMARY KEY (access_token), created_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00');
CREATE TABLE oauth_authorization_codes (authorization_code VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), redirect_uri VARCHAR(2000), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code), created_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00');
CREATE TABLE oauth_refresh_tokens (refresh_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token), created_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00');
CREATE TABLE oauth_scopes (scope TEXT, is_default BOOLEAN, created_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00');
CREATE TABLE oauth_jwt (client_id VARCHAR(80) NOT NULL, subject VARCHAR(80), public_key VARCHAR(2000), CONSTRAINT client_id_pk PRIMARY KEY (client_id), created_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00');
SCHEMA;

        foreach (explode("\n", $schema) as $statement) {
            DB::statement($statement);
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        DB::statement('DROP TABLE oauth_clients, oauth_access_tokens, oauth_authorization_codes, oauth_refresh_tokens, oauth_scopes, oauth_jwt');

    }
}
