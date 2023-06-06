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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\User;
use Illuminate\Http\Request;
use App\Model\Auth as Authenticate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth as AuthUser;

class AuthController extends ApiController
{

    public function getUser($id){
        return $this->respond(User::findOrFail($id));
    }

    public function getInfo(Request $request){
        $inputs = $request->all();
        $user   = AuthUser::user();
        return $this->respond($user);
    }

    public function login(Request $request){
        $inputs = $request->all();
        $login  = Authenticate::login($inputs['email'], $inputs['password']);
        if($login['status'] == 400) {
            $this->setStatusCode(400);
        }
        return $this->respond($login);
    }

    public function signup(Request $request){
        $inputs = $request->all();
        if(empty($inputs['fname'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "Nome precisa ser preenchido"
            ));
        }

        if(empty($inputs['email'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "O email precisa ser preenchido"
            ));
        }

        if(empty($inputs['password'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "A senha precisa ser preenchido"
            ));
        }

        $user = DB::table("users")->where('email',$inputs["email"])->first();
        if (!empty($user)) {
            $this->setStatusCode(400);
            return $this->respond(array(
                "message" => "O email já está cadastrado"
            ));
        }

        $register = Authenticate::signup(array(
            "fname" => $inputs['fname'],
            "email" => $inputs['email'],
            "password" => Authenticate::password($inputs['password']),
            "role" => 'user',
            "status" => 'Active',
        ));

        return $this->respond($register);
    }


    public function update(Request $request){
        $inputs = $request->all();
        $user   = AuthUser::user();
        if(empty($inputs['fname'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "Nome precisa ser preenchido"
            ));
        }

        if(empty($inputs['email'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "O email precisa ser preenchido"
            ));
        }

        if(!empty($inputs['password']) && empty($inputs['new_password'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "A nova senha precisa ser preenchida"
            ));
        }

        if( $user->email != $inputs['email']) {
            $user = DB::table("users")->where('email', $inputs["email"])->first();
            if (!empty($user)) {
                $this->setStatusCode(400);
                return $this->respond(array(
                    "message" => "O email já está cadastrado"
                ));
            }
        }

        $dataUpdate = array(
            "fname" => $inputs['fname'],
            "email" => $inputs['email'],
        );

        if(!empty($inputs['password']) && !empty($inputs['new_password'])){
            if($user->password != Authenticate::password($inputs['password'])) {
                $this->setStatusCode(400);
                return $this->respond(array(
                    "status" => 400,
                    "message" => "A senha não confere"
                ));
            } else {
                $dataUpdate['password'] = Authenticate::password($inputs['new_password']);
            }

        }

        $register = Authenticate::update($dataUpdate);

        return $this->respond($register);
    }

    public function updateAvatar(Request $request){
        $inputs = $request->all();

        if(empty($inputs['url'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "Url precisa ser preenchido"
            ));
        }

        $dataUpdate  = ['avatar' => $inputs['url'] ];

        $update = Authenticate::updateAvatar($dataUpdate);
        return $this->respond($update);
    }

    public function forgot(Request $request){
        $inputs = $request->all();

        if(empty($inputs['email'])){
            $this->setStatusCode(400);
            return $this->respond(array(
                "status" => 400,
                "message" => "O email precisa ser preenchido"
            ));
        }

        $user = DB::table("users")->where('email',$inputs["email"])->first();
        if (empty($user)) {
            $this->setStatusCode(400);
            return $this->respond(array(
                "message" => "Não encontramos nenhuma conta associada a esse email"
            ));
        }

        $forgot = Authenticate::forgot($inputs['email']);

        return $this->respond($forgot);
    }

    public function reset(Request $request){
        $inputs = $request->all();

        if(empty($inputs['password'])){
            return $this->respond(array(
                "status" => 400,
                "message" => "A senha precisa ser preenchida"
            ));
        }

        if(empty($inputs['password_confirm'])){
            return $this->respond(array(
                "status" => 400,
                "message" => "A senha de confirmação precisa ser preenchida"
            ));
        }

        if($inputs['password_confirm'] != $inputs['password']){
            return $this->respond(array(
                "status" => 400,
                "message" => "A senha não confere"
            ));
        }

        $reset = Authenticate::reset($inputs['token'], $inputs['password']);

        return $this->respond($reset);
    }
}
