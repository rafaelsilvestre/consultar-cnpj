<?php

namespace App\Console\Commands;

use App\Models\Cnae;
use App\Models\Nature;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReadNaturesFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read-natures-file';

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
            $handle = fopen(storage_path() . '/natures', "r");

            $row = 0;
            while ($line = fgetcsv($handle, 1000, ";")) {
                if ($row++ == 0) {
                    continue;
                }

                $this->info("----------------------------------------");
                $this->info("CODE - " . $line[0]);
                $this->info("NOME - " . utf8_encode($line[1]));

                Nature::updateOrCreate([
                    'code' => $line[0],
                ], [
                    'name' => utf8_encode($line[1]),
                ]);
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
