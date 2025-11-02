<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\InvestorsImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportInvestorsCommand extends Command
{
    protected $signature = 'import:investors {file}';
    protected $description = 'Import nhà đầu tư từ file Excel với cấu trúc phức tạp';

    public function handle()
    {
        $filePath = $this->argument('file');
        $configPath = base_path('app/Imports/import_settings.json');

        if (!file_exists($filePath)) {
            $this->error("Không tìm thấy file import: $filePath");
            return 1;
        }
        if (!file_exists($configPath)) {
            $this->error("Không tìm thấy file cấu hình: $configPath");
            return 1;
        }
        $this->info("Bắt đầu import từ file: $filePath");
        $config = json_decode(file_get_contents($configPath), true);
        $importer = new InvestorsImport($config);
        $a = Excel::import($importer, $filePath);
        dd($importer);

        return 0;
    }
}
