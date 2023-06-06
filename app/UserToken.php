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

class UserToken extends Model
{
    protected $table = 'user_tokens';
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
