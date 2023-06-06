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

namespace App\Http\Middleware;

use App\UserToken;
use Closure;
use Illuminate\Support\Facades\Auth;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $key = false;
        $oauth_enabled = true;

        $sent_request = explode(" ",$request->header('Authorization'));
        if( ($oauth_enabled && isset($sent_request[0]) ) )
        {
            $token = UserToken::where("token","=",$sent_request[0])->first();
            if(!$token)
            {
                return response()->json(['status' => '401', 'message' => 'Token inválido']);
            }

            Auth::setUser($token->user);

            return $next($request);
        }
        elseif( ($oauth_enabled && $request->is('api/auth/*')) )
        {
            return $next($request);
        }
        else{
            return response()->json(['status' => '401', 'message' => 'Token não encontrado']);
        }
        return $next($request);
    }
}
