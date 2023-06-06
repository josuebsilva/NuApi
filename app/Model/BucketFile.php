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

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BucketFile extends Model
{
    protected $table = 'bucket_files';

    protected $casts = [
        'id' => 'integer',
    ];

    protected $fillable = [
        'url',
    ];
}
