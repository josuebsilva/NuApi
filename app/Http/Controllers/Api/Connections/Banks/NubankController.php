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
use App\Http\Services\NubankService;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NubankController extends ApiController
{
    private $nubankService;
    public function __construct(NubankService $nubankService) {
        $this->nubankService = $nubankService;
    }

    //Gera o token enviado por email
    public function getToken(Request $request){
        $inputs = $request->all();
        if(empty($inputs['password']) || empty($inputs['cpf'])) {
            return response()->json([
                'status'   => 400,
                'message' => 'Senha ou CPF não informados'
            ]);
        }

        $result = $this->nubankService->auth($inputs);
        if (is_array($result)) {
            $email = $result['sent-to'];
            $encryptedCode = $result['encrypted-code'];
        } else {
            return response()->json([
                'status'   => 500,
                'message' => 'Erro ao tentar requisitar token'
            ]);
        }

        return $this->respond($result);
    }

    //Gera o certificado
    public function updateCertificate(Request $request){
        $inputs = $request->all();
        if(empty($inputs['encrypted_code']) || empty($inputs['code'])) {
            return response()->json([
                'status'   => 400,
                'message' => 'Informe o código'
            ]);
        }

        $wallet = $this->nubankService->updateCertificate($inputs);

        return $this->respond($wallet);
    }

    public function getExtract(Request $request){
        $inputs = $request->all();
        if(empty($inputs['account']) ) {
            return response()->json([
                'status'   => 400,
                'message' => 'Informe o código'
            ]);
        }
        $user   = Auth::user();
        $wallet = Wallet::where(['uuid' => $inputs['account'], 'user' => $user->id])->first();
        if(empty($wallet) ) {
            return response()->json([
                'status'   => 400,
                'message' => 'Carteira não encontrada'
            ]);
        }
        $inputs['wallet'] = $wallet;
        $result = $this->nubankService->extract($inputs);
        return $this->respond($result);
    }

    public function sync(Request $request){
        $inputs = $request->all();
        if(empty($inputs['account']) ) {
            return response()->json([
                'status'   => 400,
                'message' => 'Informe o código'
            ]);
        }

        $wallet = Wallet::where(['uuid' => $inputs['account'], 'user' => Auth::user()->id])->first();
        if(empty($wallet) ) {
            return response()->json([
                'status'   => 400,
                'message' => 'Carteira não encontrada'
            ]);
        }
        $inputs['wallet'] = $wallet;
        $transactionsUpdate = $this->nubankService->sync($inputs);
        return $this->respond($transactionsUpdate);
    }
}
