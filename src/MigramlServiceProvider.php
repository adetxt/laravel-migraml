<?php

namespace Adetxt\Migraml;

use Adetxt\Migraml\Commands\MigramlCommand;
use Illuminate\Support\ServiceProvider;

class MigramlServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([MigramlCommand::class]);
        }
    }

    public function register()
    {
        //
    }
}
