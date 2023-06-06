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

namespace App\Http\Services\Interfaces;

interface IBankService {
    public function auth($data);
    public function sync($data);
    public function extract($data);
}
