<?php


namespace Shaunpersad\ApiFoundation\OAuth2\Storage;


use Facebook\GraphUser;
use Hash;
use OAuth2\Storage\Pdo;
use User;

class LaravelPdoStorage extends Pdo {


    /**
     * @var \PDO
     */
    protected $db;
    protected $config;

    public function __construct(\PDO $connection, $config = array('user_table' => 'users', 'username_field' => 'email'))
    {
        parent::__construct($connection, $config);
    }

    protected function checkPassword($user, $password)
    {
        $hashed = $user['password'];
        return Hash::check($password, $hashed);
    }


    public function getUser($username)
    {
        $stmt = $this->db->prepare(
            $sql = sprintf(
                'SELECT * from %s where %s=:username AND login_type = :login_type',
                $this->config['user_table'],
                $this->config['username_field']
            )
        );
        $stmt->execute(array('username' => $username, 'login_type' => User::LOGIN_TYPE_NORMAL));

        if (!$userInfo = $stmt->fetch()) {
            return false;
        }

        return array_merge(array(
            'user_id' => $userInfo['id']
        ), $userInfo);
    }


    public function getFacebookUser($fb_id) {

        $stmt = $this->db->prepare(
            $sql = sprintf(
                'SELECT * from %s where %s=:fb_id AND login_type = :login_type',
                $this->config['user_table'],
                'fb_id'
            )
        );
        $stmt->execute(array('fb_id' => $fb_id, 'login_type' => User::LOGIN_TYPE_FACEBOOK));

        if (!$userInfo = $stmt->fetch()) {
            return false;
        }

        return array_merge(array(
            'user_id' => $userInfo['id']
        ), $userInfo);
    }

    public function createFacebookUser(GraphUser $facebook_user) {

        $user = new User();
        $user->email = $facebook_user->getProperty('email');
        $user->password = Hash::make(str_random(20));
        $user->fb_id = $facebook_user->getId();
        $user->first_name = $facebook_user->getFirstName();
        $user->last_name = $facebook_user->getLastName();
        $user->login_type = OAuthUser::LOGIN_TYPE_FACEBOOK;
        $user->save();
    }
} 