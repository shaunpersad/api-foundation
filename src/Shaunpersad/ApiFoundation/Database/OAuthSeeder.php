<?php


namespace Shaunpersad\ApiFoundation\Database;


use DB;
use Eloquent;
use Hash;
use Seeder;
use Shaunpersad\ApiFoundation\Models\OAuthUser;

class OAuthSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $this->call('OAuthClientsSeeder');
        $this->call('UsersSeeder');
    }
}



class OAuthClientsSeeder extends Seeder
{
    public function run()
    {
        /*
         * OAuth2.0 spec states that if secrets cannot be kept private,
         * then one should not be created, e.g. if API will be used by a mobile app,
         * or JavaScript, etc.
         */

        $route = url('login-redirect');

        DB::table('oauth_clients')->insert(array(
            'client_id' => "testclient",
            'client_secret' => "",
            'redirect_uri' => $route,
        ));
    }
}


class UsersSeeder extends Seeder
{
    public function run()
    {
        $user = new OAuthUser();
        $user->email = 'admin@local.com';
        $user->password = Hash::make('password');
        $user->first_name = 'Shaun';
        $user->last_name = 'Persad';
        $user->login_type = OAuthUser::LOGIN_TYPE_NORMAL;
        $user->save();
    }
}