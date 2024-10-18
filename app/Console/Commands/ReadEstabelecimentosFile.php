<?php

namespace App\Console\Commands;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            while ($line = fgetcsv($handle, 1000, ";")) {
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
                $this->info("COUNT - " . $row + 1);
                $this->info("CNPJ - " . $line[0] . $line[1] . $line[2]);
                $this->info("NOME FANTASIA - " . ($line[4] ?? ''));
                $this->info("SITUAÇÃO CADASTRAL - " . ($line[5] ?? ''));
                $this->info("DATA SITUAÇÃO CADASTRAL - " . ($line[6] ?? ''));
                $this->info("CNAES - " . ($line[11] ?? ''));

                try {
                    $registration_status_at = null;
                    $startOfActivity = null;

                    if (isset($line[6])) {
                        $registration_status_at = Carbon::parse($line[6]);
                    }

                    if (isset($line[10])) {
                        $startOfActivity = Carbon::parse($line[10]);
                    }

                    $alternativePhoneNumber = null;

                    if (! empty($line[23]) && ! empty($line[24])) {
                        $alternativePhoneNumber = $line[23] . $line[24];
                        if ($alternativePhoneNumber === '000000000000') {
                            $alternativePhoneNumber = null;
                        }
                    }

                    $addressAdditional = $line[16];
                    $count = 0;

                    do {
                        $addressAdditional = str_replace('  ', ' ', trim(mb_convert_encoding($addressAdditional, "UTF-8")), $count);
                    } while ($count > 0);
                } catch (InvalidFormatException $e) {
                    $registration_status_at = null;
                    $startOfActivity = null;
                }

                try {
                    DB::table('companies')
                        ->updateOrInsert(
                            [
                                'cnpj' => $line[0] . $line[1] . $line[2]
                            ],
                            [
                                'type' => $line[3] ?? null,
                                'name' => null,
                                'fantasy_name' => ($line[4] ?? null),
                                'registration_status' => ($line[5] ?? null),
                                'registration_status_at' => $registration_status_at,
                                'main_activity' => ($line[11] ?? null),

                                'start_of_activity' => $startOfActivity,
                                'address_type_of_street' => ! empty($line[13]) ? mb_convert_encoding($line[13], "UTF-8") : null,
                                'address_street' => ! empty($line[14]) ? mb_convert_encoding($line[14], "UTF-8") : null,
                                'address_number' => ! empty($line[15]) ? mb_convert_encoding($line[15], "UTF-8") : null,
                                'address_additional' => ! empty($line[16]) ? $addressAdditional : null,
                                'address_neighborhood' => ! empty($line[17]) ? mb_convert_encoding($line[17], "UTF-8") : null,
                                'address_zip_code' => ! empty($line[18]) ? mb_convert_encoding($line[18], "UTF-8") : null,
                                'address_state' => ! empty($line[19]) ? mb_convert_encoding($line[19], "UTF-8") : null,
                                'address_city' => ! empty($line[20]) ? mb_convert_encoding($line[20], "UTF-8") : null,
                                'phone_number' => ($line[21] ?? null) . ($line[22] ?? null),
                                'alternative_phone_number' => $alternativePhoneNumber,
                                'email' => ! empty($line[27]) ? Str::lower(mb_convert_encoding($line[27], "UTF-8")) : null,

                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                } catch (Throwable $e) {
                    report($e);
                }
            }
        } catch (Throwable $e) {
            report($e);
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
