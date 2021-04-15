<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\CsvImport\ExhibitorCsvImportService;

class ExpoJoinExhibitorCsvImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:expo_join_exhibitor_csvImport {csv_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the CSV output from Innovent core system';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ExhibitorCsvImportService $ExhibitorCsvImportService)
    {
        parent::__construct();

        $this->ExhibitorCsvImportService = $ExhibitorCsvImportService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $csv_path = $this->argument("csv_path");

            $result = $this->ExhibitorCsvImportService->expoJoinExhibitorCsvImport($csv_path);

            if ($result === false) throw new \RunTimeException("Missing csv file");
        } catch (\Exception $e) {
            $this->error($e);
        }
    }
}
