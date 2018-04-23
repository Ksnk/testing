<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ksnk\testing\Apiupdater\doitall;
use Illuminate\Console\Application;

class Cron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doitall {api?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron test';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $doitall= new doitall;
        $this->info(
            print_r(
                $doitall->db_read($this->argument('api')), /*'Hello world!' */
                true)
        );
    }
}