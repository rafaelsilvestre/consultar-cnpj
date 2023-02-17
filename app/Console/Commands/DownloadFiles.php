<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use \GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
     * @var Collection
     */
    private $files;

    /**
     * @var Client
     */
    private $http;

    /**
     * @var string
     */
    private $url = 'https://dadosabertos.rfb.gov.br';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->http = new Client([
            'base_uri' => $this->url,
            'timeout'  => 1800,
        ]);

        $this->files = collect(array(
            'Estabelecimentos0.zip',
            'Estabelecimentos1.zip',
            'Estabelecimentos2.zip',
            'Estabelecimentos3.zip',
            'Estabelecimentos4.zip',
            'Estabelecimentos5.zip',
            'Estabelecimentos6.zip',
            'Estabelecimentos7.zip',
            'Estabelecimentos8.zip',
            'Estabelecimentos9.zip',
        ));
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->files
            ->each(function ($file) {
                $this->comment('[' . $file . '] Processing');

                $attempts = 0;
                $maxAttempts = 3;

                while (true) {
                    try {
                        $attempts++;

                        $headers = get_headers($this->url . '/CNPJ/' . $file, true);

                        if (!isset($headers['Last-Modified'])) {
                            return;
                        }

                        $headers['Last-Modified'] = Carbon::parse($headers['Last-Modified']);

                        $lastModified = Cache::get('Last-Modified:' . $file);

                        if (!$lastModified) {
                            $this->info('Not exists  last modified');
                            $this->handleDownloadFile($file);

                            return;
                        }

                        if ($lastModified->lt($headers['Last-Modified'])) {
                            $this->info('New file exists');
                            $this->handleDownloadFile($file);
                        }
                    } catch (Throwable $e) {
                        Log::channel('daily')->error('Erro ao baixar arquivo de dados abertos', [
                            'file' => $file,
                            'error' => $this->exceptionToArray($e),
                        ]);

                        if($attempts < $maxAttempts) {
                            sleep(30);

                            continue;
                        }
                    }

                    break;
                }
            });
    }

    private function handleDownloadFile(string $file)
    {
        $this->comment('[' . $file . '] Handle Download');

        $result = $this->http->request('GET', '/CNPJ/' . $file, [
            'debug' => true,
        ]);

        Storage::disk(config('filesystems.default'))
            ->put('estabelecimentos/' . $file, $result->getBody()->getContents());

        $this->info('[' . $file . '] File downloaded');
    }

    private function exceptionToArray($exception)
    {
        return [
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ];
    }
}
