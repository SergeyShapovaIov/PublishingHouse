<?php

namespace App\Database\Storage;

use App\Exceptions\AuthorizationException;

class UserStorage{

    private \PDO $db;

    public function __construct(\PDO $db){
        $this->db = $db;
    }

    /**
     * @throws AuthorizationException
     */
    public function registration ($params){

        if(empty($params['login'])){
            throw new AuthorizationException("The login should not be empty!");
        }
        if(empty($params['password'])){
            throw new AuthorizationException("The password should not be empty!");
        }
        if($params['password_confirm'] !== $params['password']){
            throw new AuthorizationException("The password and confirm password must equal!");
        }
        if(empty($params['username'])){
            throw new AuthorizationException("The username should not be empty!");
        }
        if(empty($params['date_of_birth'])){
            throw new AuthorizationException("The date of birth should not be empty!");
        }
        if(empty($params['address'])){
            throw new AuthorizationException("The address should not be empty!");
        }
        if(empty($params['gender'])){
            throw new AuthorizationException("The address should not be empty!");
        }
        if(empty($params['interests'])){
            throw new AuthorizationException("The interests should not be empty!");
        }
        if(empty($params['vk'])){
            throw new AuthorizationException("The vk link should not be empty!");
        }
        if(empty($params['blood_group'])){
            throw new AuthorizationException("The blood group should not be empty!");
        }
        if(empty($params['rh_factor'])){
            throw new AuthorizationException("The rh factor should not be empty!");
        }

        $stmt = $this->db->prepare('INSERT INTO users (login, password, username, date_of_birth, 
                   address, gender, interests, vk, blood_group, rh_factor) 
                    VALUES (:login,:password, :username,:date_of_birth, :address, :gender,
                            :interests, :vk, :blood_group, :rh_factor)');
        $stmt->execute([
            'login' => $params['login'],
            'password' => password_hash($params['password'],PASSWORD_BCRYPT),
            'username' => $params['username'],
            'date_of_birth' => $params['date_of_birth'],
            'address' => $params['address'],
            'gender' => $params['gender'],
            'interests' => $params['interests'],
            'vk' => $params['vk'],
            'blood_group' => $params['blood_group'],
            'rh_factor' => $params['rh_factor']
        ]);
    }

    public function getUserIdByEmailAndPassword($email, $password) {
        //$stmt = $this->db->prepare()
    }

    /**
     * @throws AuthorizationException
     */
    public function login($login, $password){
        $stmt = $this->db->prepare('SELECT * FROM users WHERE login = :login');
        $stmt->execute([
            'login' => $login
        ]);

        $user = $stmt->fetch();

        if(empty($user)){
            throw new AuthorizationException("User with login not found!");
        }

        if(password_verify($password, $user['password'])){
            return $user;
        }

        throw new AuthorizationException("Incorrect email or password");
    }
}