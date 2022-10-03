<?php

namespace Adetxt\Migraml\Commands;

use Illuminate\Console\Command;

class MigramlCommand  extends Command
{
    protected $signature = "migraml:example {--force}";

    protected $description = "Convert yaml files into laravel migration file";

    public function handle()
    {
        $this->line("Converting yaml files into laravel migration file");
    }
}
