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

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $casts = [
        'id' => 'integer',
    ];
    protected $fillable = [
        'title',
        'day',
        'year',
        'month',
        'amount',
        'payment_value',
        'description',
        'account',
        'category',
        'card_id',
        'is_card',
        'transaction_date',
        'status',
        'due_date',
        'automatic'
    ];

    public function account()
    {
        return $this->belongsTo('App\Wallet','account');
    }

    public function category()
    {
        return $this->belongsTo('App\Category','category');
    }
}
