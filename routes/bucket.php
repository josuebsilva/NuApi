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


$router->group(['middleware' => ['api.req']], function () use ($router) {
    $router->post('/store', 'BucketController@store');
    $router->get('/all', 'BucketController@all');
    $router->delete('/delete/{file}', 'BucketController@delete');
    $router->delete('/delete-all', 'BucketController@deleteAll');
});

$router->get('/{u}/{role}/{folder}/{file}', 'BucketController@get');
