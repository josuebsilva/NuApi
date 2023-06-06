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

namespace App\Http\Services;

use App\Http\Services\Interfaces\IBankService;
use App\Connections\Banks\Nubank\Nubank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use App\Transaction;
use App\Wallet;
use App\Category;

class NubankService implements IBankService
{
    const OPENSSL_CNF_LOCATION  =  "/private/etc/ssl/openssl.cnf"; //Local do openssl na sua máquina
    private $nubank;
    public function __construct(Nubank $nubank) {
        $this->nubank = $nubank;
    }

    public function extract($data)
    {
        $user   = Auth::user();
        $path          = "/certs/u/{$user->uuid}/nu/";
        $fileName      = "cert_{$user->uuid}.p12";
        $pathFile      = storage_path() .'/app'.$path.$fileName;

        $this->nubank = new Nubank($data['wallet']->bank_refresh, $pathFile);
        $this->nubank->autenticarComTokenECertificado();

        return $this->nubank->getAccountFeed();;
    }

    public function extractCard($data)
    {
        $user   = Auth::user();
        $path          = "/certs/u/{$user->uuid}/nu/";
        $fileName      = "cert_{$user->uuid}.p12";
        $pathFile      = storage_path() .'/app'.$path.$fileName;

        $this->nubank = new Nubank($data['wallet']->bank_refresh, $pathFile);
        $this->nubank->autenticarComTokenECertificado();

        return $this->nubank->getEvents();;
    }

    public function sync($data) {
        date_default_timezone_set('America/Sao_Paulo');
        $user   = Auth::user();
        $path          = "/certs/u/{$user->uuid}/nu/";
        $fileName      = "cert_{$user->uuid}.p12";
        $pathFile      = storage_path() .'/app'.$path.$fileName;

        $this->nubank = new Nubank($data['wallet']->bank_refresh, $pathFile);
        $this->nubank->autenticarComTokenECertificado();

        $sessionInfo = json_decode($this->nubank->sessionInfo);
        $walletSync = [
            'last_sync' => date("Y-m-d H:i"),
            'session_info' => $this->nubank->sessionInfo,
            'bank_token' => $sessionInfo->access_token,
            'bank_refresh' => $sessionInfo->refresh_token,
            'balance' => $this->nubank->getAccountBalance(),
        ];

        $result = $this->nubank->getAccountFeed();
        $transactionsUpdate = [];

        $date = date('Y-m-d');
        $newDate = date('Y-m-d', strtotime($date. '-31 days'));
        $dateLocal = new \DateTime($newDate);
        foreach ($result as $feed) {
            $transactionDate = new \DateTime(date('Y-m-d', $feed->postDate));
            if($transactionDate >= $dateLocal) {
                $category = null;
                if(strpos($feed->title, "enviada")) {
                    $type = 'expense';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Conta%" '
                        . 'AND categories.type = "Expense" '
                    )->get()[0];
                }
                if(strpos($feed->title, "recebida")) {
                    $type = 'income';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Depósito%" '
                        . 'AND categories.type = "Income" '
                    )->get()[0];
                }
                if(strpos($feed->title, "efetuado")) {
                    $type = 'expense';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Conta%" '
                        . 'AND categories.type = "Expense" '
                    )->get()[0];
                }
                if(strpos($feed->title, "débito")) {
                    $type = 'expense';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Conta%" '
                        . 'AND categories.type = "Expense" '
                    )->get()[0];
                }

                if(strpos($feed->title, "da fatura")) {
                    $type = 'expense';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Fatura%" '
                        . 'AND categories.type = "Expense" '
                    )->get()[0];
                }

                if(strpos($feed->title, "guardado")) {
                    $type = 'expense';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Contas%" '
                        . 'AND categories.type = "Expense" '
                    )->get()[0];
                }

                if(strpos($feed->title, "resgatado")) {
                    $type = 'income';
                    $category =  Category::select()->whereRaw(''
                        . 'categories.user = ' . $user->id . ' '
                        . 'AND categories.name LIKE "%Depósito%" '
                        . 'AND categories.type = "Income" '
                    )->get()[0];
                }

                $transaction = Transaction::where(['uuid' => $feed->id, 'user' => $user->id])->first();
                if(empty($transaction)) {
                    $day      =  date('d',strtotime($feed->postDate));
                    $year     =  date('Y',strtotime($feed->postDate));
                    $month    =  date('m',strtotime($feed->postDate));

                    $titlePos = strpos($feed->detail, "\nR$");
                    $title    = substr($feed->detail, 0, $titlePos);
                    $transaction = new Transaction();
                    $transaction->title = $title;
                    $transaction->user = $user->master_id;
                    $transaction->day = $day;
                    $transaction->year = $year;
                    $transaction->month = $month;
                    $transaction->amount = $feed->amount;
                    $transaction->payment_value = $feed->amount;
                    $transaction->account = $data['wallet']->id;
                    $transaction->description = $title;
                    $transaction->automatic = true;
                    $transaction->transaction_type = $type;
                    $transaction->category = !empty($category) ? $category->id : null;
                    $transaction->transaction_date = date('Y-m-d H:i:s', $feed->postDate);
                    $transaction->payment_date = date('Y-m-d H:i:s', $feed->postDate);
                    $transaction->device = 'nu_api';
                    $transaction->save();

                    $transactionsUpdate[] = $transaction;
                }
            }
        }

        $data['wallet']->update($walletSync);
        return [
            'wallet' => $data['wallet'],
            'transactions' => $transactionsUpdate
        ];
    }

    public function auth($data)
    {
        return $this->nubank->solicitarCodigoPorEmail($data['cpf'], $data['password'], 'NuApi', self::OPENSSL_CNF_LOCATION);
    }

    public function updateCertificate($data) {
        date_default_timezone_set('America/Sao_Paulo');

        $user = Auth::user();
        $path          = "/certs/u/{$user->uuid}/nu/";
        $fileName      = "cert_{$user->uuid}.p12";
        $pathFile      = storage_path() .'/app'.$path.$fileName;

        Storage::disk()->put($path.$fileName,  '');

        $result = $this->nubank->confirmarCodigoEmail($data['encrypted_code'], $data['code'], $pathFile, $data['cpf'], $data['password'], 'NuApi', self::OPENSSL_CNF_LOCATION);

        $result = json_decode($this->nubank->obterTokenDeAcesso($data['cpf'], $data['password'], $pathFile));

        $accessToken = $result->access_token;
        $refreshToken = $result->refresh_token;
        $refreshBefore = date("d/m/Y H:i", strtotime($result->refresh_before));

        $this->nubank = new Nubank($refreshToken, $pathFile);
        $this->nubank->autenticarComTokenECertificado();

        $result = $this->nubank->getAccountFeed();

        return $result;
    }
}
