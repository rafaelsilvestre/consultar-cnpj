<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class DownloadFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-files';

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
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::withOptions([
                'debug' => true,
            ])->get('https://dadosabertos.rfb.gov.br/CNPJ/Empresas0.zip');

            print_r($response);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
