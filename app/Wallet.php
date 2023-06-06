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

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $fillable = [
        'id',
        'name',
        'balance',
        'color',
        'deleted',
        'uuid',
        'automatic',
        'bank',
        'bank_token',
        'last_sync',
        'connected',
        'automatic',
        'stage',
        'bank_refresh',
        'session_info'
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
