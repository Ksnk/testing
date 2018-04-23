<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ksnk\testing\Apiupdater\doitall;
use Illuminate\Console\Application;

class Cron extends Command
{

    public $bar =null;

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
     */
    public function handle()
    {
        $doitall= new doitall;
        $that=$this;
        $doitall->db_read(
            $this->argument('api'),
            function($reason, $data=null) use($that){
                switch ($reason){
                    case 'total':
                        // initprogressbar
                        $that->bar= $that->output->createProgressBar($data);
                        break;
                    case '+1':
                        // move
                        if($that->bar)
                            $that->bar->advance();
                        break;
                    default:
                        $that->info($reason. ' '. print_r($data, true));
                        break;
                }
            }
        );
        if($this->bar) $this->bar->finish();
    }
}