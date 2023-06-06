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

use App\User;
use App\Http\Controllers\Api\ApiController;
use App\Subscription;
use App\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends ApiController
{

    public function deleteUser(Request $request) {
        $user = Auth::user();

        $userToken = UserToken::where(['user_id' => $user->id])->first();
        $userToken->delete();

        $subcription = Subscription::where(['user_id' => $user->id])->first();
        $subcription->delete();

        $user = User::where(['id' => $user->id])->first();
        $user->delete();

        return $this->respond([]);
    }
}
