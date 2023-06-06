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

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CommandDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:dbhost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        echo "Finish...";

        return 0;
    }
}
