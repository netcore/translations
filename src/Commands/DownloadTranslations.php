<?php

namespace Netcore\Translator\Commands;

use Illuminate\Console\Command;
use Netcore\Translator\Models\Translation;
use Requests;

class DownloadTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads translations from other server';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $downloadFrom = config('translations.api.download_from');
        
        if(!$downloadFrom) {
            $this->warn('Data source for translations is not configured. Aborting.');
            return;
        }

        $this->line('');
        $this->info('Attempting to download translations from ' . $downloadFrom);

        $headers = [];
        $options = [
            'connect-timeout' => 60, // 1min
            'timeout' => 60 // 1min
        ];

        $request = Requests::get($downloadFrom, $headers, $options);

        $statusCode = $request->status_code;

        if($statusCode != 200) {
            $this->info('Status code ' . $statusCode . ' was received, but 200 was expected. Aborting.');
        }

        $json = $request->body;
        
        $newTranslations = array_map(function($stdClass){
            return (array) $stdClass;
        }, json_decode($json));

        $this->line('');
        $this->info('Downloaded! Attempting to import them in local system...');
        
        // Delete existing translations
        Translation::truncate();

        // Import new translations
        $this->line('');
        $bar = $this->output->createProgressBar(count($newTranslations));
        foreach (array_chunk($newTranslations, 300) as $chunk) {
            Translation::insert($chunk);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->line('');

        // @TODO refactor to use Facade (or something better)
        $translation = new Translation();
        $translation->import()->flushCache($json);

        $this->line('');
        $this->info('All done!');
        $this->line('');
    }
}