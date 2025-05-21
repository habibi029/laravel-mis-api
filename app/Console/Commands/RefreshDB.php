<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class RefreshDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refreshDB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh DB Structure/Data and Install Passport';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(!$this->confirm('This will format the database. Do you want to proceed?')) return false;

        $exitCode = Artisan::call('migrate:fresh', [
            '--seed' => true
        ]);


        echo "Migrate Fresh Done! \n";

        $exitCode = Artisan::call('passport:keys',[
            '--no-interaction' => true
        ]);

        $exitCode = Artisan::call('passport:client', [
            '--personal' => true,
            '--no-interaction' => true
        ]);


        echo "Passport Install Done! \n";
        echo "Finished! \n";
    }
}
