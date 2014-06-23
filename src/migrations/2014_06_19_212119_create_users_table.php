<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Shaunpersad\ApiFoundation\Models\OAuthUser;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('users', function(Blueprint $table) {

            $table->increments('id');
            $table->string('email');
            $table->string('password');
            $table->string('login_type')->default(OAuthUser::LOGIN_TYPE_NORMAL);
            $table->string('fb_id')->nullable()->default(null);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('remember_me')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('users');
	}

}
