<?php

namespace Webkul\B2BSuite\Console\Commands;

use Illuminate\Console\Command;
use Webkul\B2BSuite\Database\Seeders\DatabaseSeeder;
use Webkul\B2BSuite\Providers\B2BSuiteServiceProvider;

class InstallB2BSuite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'b2b-suite:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->description = trans('marketplace::app.commands.install.description');

        parent::__construct();
    }

    /**
     * Install and configure B2B Suite.
     */
    public function handle()
    {
        $this->call('migrate');

        $this->call(DatabaseSeeder::class);

        $this->callSilently('vendor:publish', [
            '--provider' => B2BSuiteServiceProvider::class,
            '--force'    => true,
        ]);

        $this->call('optimize:clear');

        $this->line('<fg=white>
             ____              _     _          ____  ____  ____    ____        _ _       
            | __ )  __ _  __ _(_)___| |_ ___   | __ )|___ \| __ )  / ___| _   _(_) |_ ___ 
            |  _ \ / _` |/ _` | / __| __/ _ \  |  _ \  __) |  _ \  \___ \| | | | | __/ _ \
            | |_) | (_| | (_| | \__ \ || (_) | | |_) |/ __/| |_) |  ___) | |_| | | ||  __/
            |____/ \__,_|\__, |_|___/\__\___/  |____/______|____/  |____/ \__|_|_|\__\___|
                         |___/                                                          
        </>');

        $this->components->info(trans('b2b_suite::app.commands.install.finish'));
    }
}
