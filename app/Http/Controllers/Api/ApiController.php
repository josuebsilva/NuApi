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

use App\UserToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiController extends Controller
{

    protected $status_code = 200;
    protected $auth_type = "";

    /**
     * @return string
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * @param string $auth_type
     */
    public function setAuthType($auth_type)
    {
        $this->auth_type = $auth_type;
    }

    /**
     * @return string
     */
    public function getAuthUser()
    {
        return $this->auth_user;
    }

    /**
     * @param string $auth_user
     */
    public function setAuthUser($auth_user)
    {
        $this->auth_user = $auth_user;
    }
    protected $auth_user;

    public function respond($data=[])
    {
        $toplevel = [];
        if($data instanceof LengthAwarePaginator)
        {
            $toplevel = [
                'has_more_pages' => $data->hasMorePages(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ];
            $data = $data->getCollection();
        }
        $results = [
            'status' => $this->getStatusCode(),
            'data' => $data
        ];

        $results = array_merge($results,$toplevel);
        return response()->json($results, $this->getStatusCode());
    }

    public function setStatusCode($code)
    {
        $this->status_code = $code;
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function successResponse($data, $code = \Illuminate\Http\Response::HTTP_OK)
    {
        return response()->json($data, $code);
    }
    public function errorResponse($message, $code)
    {
        return response()->json(['message' => $message, 'status' => $code], $code);
    }

    public function validate(\Illuminate\Http\Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException(
                $validator,
                $this->errorResponse(implode(',', $validator->messages()->all()), 422)
            );
        }
    }

    public function isBearer()
    {
        return ($this->getAuthType() == 'bearer');
    }

    public function __construct(Request $request)
    {

        $sent_request = explode(" ",$request->header('Authorization'));
        if(count($sent_request) == 1)
        {
            $this->setAuthType('token');
        }
        elseif(count($sent_request) == 2)
        {
            $token = UserToken::where("token","=",$sent_request[1])->first();

            $request->attributes->add(['token' => $token]);
            if($token){
                $this->setAuthType('bearer');
                $this->setAuthUser($token->user_id);
            }
        }

    }
}
