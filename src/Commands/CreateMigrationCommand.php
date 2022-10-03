<?php

namespace Adetxt\Migraml\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;

class CreateMigrationCommand  extends Command
{
    protected $signature = "migraml:make {name}
                            {--d|database=default : Database connection}
                            {--t|table= : Table name}
                            {--o|output= : Output path}
                            {--c|create= : Create Table}
                            {--force}";

    protected $description = "Make yaml file";

    public function handle()
    {
        $name = str_replace(' ', '_', $this->argument('name'));

        $table = $this->option('table') ?? 'table_name';
        $action = $this->option('create') ? 'create_table' : 'alter_table';
        $output = $this->option('output') ?? base_path('migraml');

        $array = collect([
            'up' => [
                [
                    'action' => $action,
                    'table' => $table,
                    'columns' => [
                        'column_name' => 'column_type',
                    ],
                ],
            ],
            'down' => [
                [
                    'action' => 'drop_table',
                    'table' => $table,
                ]
            ],
        ]);

        if ($this->option('database') !== 'default') {
            $array->prepend($this->option('database'), 'connection');
        }

        $yaml = Yaml::dump($array->toArray(), 6, 2);

        $version = date("Y_m_d_His", time());
        $filename = $version . '_' . $name;
        $path = $output . '/' . $filename . '.yaml';

        if (!file_exists($output)) {
            mkdir($output);
        }

        if (!$this->option('force')) {
            if (file_exists($path)) {
                $this->error("File already exists: $path");
                return;
            }
        }

        file_put_contents($path, $yaml);

        $this->info("File created: $path");

        return;
    }
}
