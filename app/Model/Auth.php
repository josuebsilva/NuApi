<?php
/*
 * NuAPI
 *
 * Copyright 2023 Josué Barbosa
 * https://jedibit.com.br
 * https://stimper.com.br
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

namespace App\Model;


use App\Helper;
use Ramsey\Uuid\Uuid;
use App\User;
use App\UserToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth as AuthUser;

class Auth {

    /**
     * Authenicate a user
     *
     * @param   \Std    $user
     * @return  void
     */
    public static function authenticate($user) {
        JWTAuth::factory()->setTTL(Carbon::now()->addMonth()->timestamp);
        return JWTAuth::fromUser($user);
    }


    /**
     * Check if the user is authenticated
     *
     * @return  void
     */
    public static function check() {
        return session()->has(config('auth.session'));
    }

    /**
     * Log out the authenticated user
     *
     * @return  void
     */
    public static function deauthenticate() {
        if(isset($_COOKIE['cmVtZW1iZXI'])) {
            cookie('cmVtZW1iZXI', '', -7);
        }
        session()->flush();
    }

    /**
     * Create a valid password
     *
     * @param   string  $string
     * @return  string
     */
    public static function password($str) {
        return hash_hmac('sha256', $str, config('auth.secret'));
    }



    /**
     * Get the authenticated user
     *
     * @return \Std
     */
    public static function user() {
        return DB::table("users")->find(session(config('auth.session')) + 0);
    }

    /**
     * Login a user
     *
     * @param string $username
     * @param password $password
     * @param string $options
     * @return mixed
     */
    public static function login($username, $password, $options = array()) {
        $givenPassword = self::password($password);
        $user = User::where('email',$username)->first();

        if (!empty($user)) {
            if (isset($options["status"])) {
                $statusColumnName = config('auth.statusColumn');
                if ($options["status"] != $user->$statusColumnName) {
                    return array(
                        "status" => "error",
                        "title" => "Conta inativa",
                        "message" => "Sua conta não está ativa, por favor contate-nos para mais informações."
                    );
                }
            }

            if(Helper::hash_compare($user->password, self::password($password))){


                $tz = 'America/Sao_Paulo';
                $timestamp = time();
                $dt = new \DateTime("now", new \DateTimeZone($tz)); //first argument "must" be a string
                $dt->setTimestamp($timestamp); //adjust the object to correct timestamp

                DB::table('users')->where('id',$user->id)->update(['last_login' => $dt->format('Y-m-d H:i:s')]);

                //$token = self::authenticate($user);
                $userToken = UserToken::where(['user_id' => $user->id])->first();
                $response = array(
                    "message" => "success",
                    "status"  => 200,
                    "user"    => $user,
                    "token"   => $userToken->token
                );
            }else{
                $response = array(
                    "status" => 400,
                    "title" => "Dados inválido",
                    "message" => "Usuário ou senha estão inválidos"
                );
            }
        }else{
            $response = array(
                "status" => 400,
                "title" => "Login incorreto",
                "message" => "Usuário ou senha estão inválidos"
            );
        }

        return $response;
    }



    /**
     * Update pass user
     *
     * @param array $dataUser
     * @param array $options
     * @return mixed
     */
    public static function update($dataUser) {
        $duser = AuthUser::user();
        $user = User::where(['id' => $duser->id])->first();
        $user->update($dataUser);
        $user = User::where(['id' => $duser->id])->first();
        return array(
            "message" => "success",
            "status"  => 200,
            "user"    => $user,
        );
    }

    /**
     * Sign up new user
     *
     * @param array $dataUser
     * @param array $options
     * @return mixed
     */
    public static function signup($dataUser) {

        $uuidUser = Uuid::uuid4();
        $dataUser['uuid'] = $uuidUser->toString();
        $dataUser = $dataUser;
        $dataUser['type'] = 'user';

        $db = DB::table("users");
        $newUserId = $db->insertGetId($dataUser);
        $user = User::find($newUserId);

        $uuid = Uuid::uuid4();
        $userToken = $uuid->toString();
        DB::table('user_tokens')->insert([
            'token' => $userToken,
            'user_id' => $newUserId
        ]);

        return [
            'token' => $userToken,
            'user' => $user,
        ];
    }
}
