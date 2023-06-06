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

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = [
        'id',
        'name',
    ];
    protected $casts = [
        'id' => 'integer',
    ];
}
