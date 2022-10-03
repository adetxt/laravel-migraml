<?php

namespace Adetxt\Migraml\Commands;

use Adetxt\Migraml\Migraml;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ConvertYamlCommand  extends Command
{
    protected $signature = "migraml:convert
                            {--I|input= : Input path}
                            {--force}";

    protected $description = "Convert yaml files into laravel migration file";

    public function handle()
    {
        $this->line("Converting yaml files into laravel migration file");

        $input = $this->option('input') ?? base_path('migraml');
        $files = File::allFiles($input);

        foreach ($files as $key => $value) {
            if ($value->getExtension() !== 'yaml') {
                continue;
            }

            $data = Yaml::parse($value->getContents());

            $migraml = new Migraml($data);

            try {
                $data['connection'] = isset($data['connection']) ? "Schema::connection('{$data['connection']}')->" : 'Schema::';
                $data['up'] = $migraml->parseActions('up');
                $data['down'] = $migraml->parseActions('down');
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
                return;
            }

            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../stubs');
            $twig = new \Twig\Environment($loader);

            $result = $twig->render('schema.twig', $data);

            $filename = str_replace('.yaml', '', $value->getFilename());

            $path = database_path('migrations/' . $filename . '.php');

            if (file_exists($path)) {
                if ($this->option('force')) {
                    unlink($path);
                } else {
                    $this->error("File already exists: $path");
                    return;
                }
            }

            file_put_contents($path, $result);

            $this->info("Success generate " . $path);
            return;
        }
    }
}
