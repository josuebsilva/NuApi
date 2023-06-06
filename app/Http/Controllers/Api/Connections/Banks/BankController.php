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

namespace App\Http\Controllers\Api\Connections\Banks;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class BankController extends ApiController
{

    public function index(Request $request){
        $inputs = $request->all();
        return $this->respond([]);
    }
}
