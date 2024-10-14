<?php

namespace App\Console\Commands;

use Carbon\Exceptions\InvalidFormatException;
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
    protected $signature = 'read-estabelecimentos-file {filename}';

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
            if (! $this->argument('filename')) {
                $this->error('File name not provided');
                return;
            }

            if (! file_exists(storage_path('Estabelecimentos/' . $this->argument('filename')))) {
                $this->error('File not exists');
                return;
            }

            $handle = fopen(storage_path('Estabelecimentos/' . $this->argument('filename')), "r");

            $row = 0;
            while ($line = fgetcsv($handle, 500, ";")) {
                if ($row++ == 0) {
                    continue;
                }

                if (!isset($line[0]) || !isset($line[1]) || !isset($line[2])) {
                    $this->info(print_r($line));
                    continue;
                }

                $document = $line[0] . $line[1] . $line[2];

                if (! $this->verifyCNPJ($document)) {
                   $this->error('CNPJ inválido');
                   $this->error($document);
                   continue;
                }

                $this->info("----------------------------------------");
                $this->info("CNPJ - " . $line[0] . $line[1] . $line[2]);
                $this->info("NOME FANTASIA - " . ($line[4] ?? ''));
                $this->info("SITUAÇÃO CADASTRAL - " . ($line[5] ?? ''));
                $this->info("DATA SITUAÇÃO CADASTRAL - " . ($line[6] ?? ''));
                $this->info("CNAES - " . ($line[11] ?? ''));

                try {
                    $registration_status_at = null;

                    if (isset($line[6])) {
                        $registration_status_at = Carbon::parse($line[6]);
                    }
                } catch (InvalidFormatException $e) {
                    $registration_status_at = null;
                }

                try {
                    DB::table('companies')
                        ->updateOrInsert(
                            [
                                'cnpj' => $line[0] . $line[1] . $line[2]
                            ],
                            [
                                'fantasy_name' => ($line[4] ?? null),
                                'registration_status' => ($line[5] ?? null),
                                'registration_status_at' => $registration_status_at,
                                'main_activity' => ($line[11] ?? null),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        );
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    private function verifyCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
