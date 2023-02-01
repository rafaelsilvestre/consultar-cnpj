<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReadEstabelecimentosFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read-estabelecimentos-file';

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
            $handle = fopen("C:\Users\cliente\Downloads\Estabelecimentos0\K3241.K03200Y0.D30114.ESTABELE", "r");

            $row = 0;
            while ($line = fgetcsv($handle, 1000, ";")) {
                if ($row++ == 0) {
                    continue;
                }

                $this->info("CNPJ - " . $line[0] . $line[1] . $line[2]);
                $this->info("NOME FANTASIA - " . ($line[4] ?? ''));
                $this->info("SITUAÇÃO CADASTRAL - " . ($line[5] ?? ''));
                $this->info("DATA SITUAÇÃO CADASTRAL - " . ($line[6] ?? ''));

                DB::table('companies')->insertOrIgnore([
                    [
                        'cnpj' => $line[0] . $line[1] . $line[2],
                        'fantasy_name' => ($line[4] ?? null),
                        'registration_status' => ($line[5] ?? null),
                        'registration_status_at' => isset($line[6]) ? Carbon::parse($line[6]) : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
