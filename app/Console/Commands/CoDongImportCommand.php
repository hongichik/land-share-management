<?php

namespace App\Console\Commands;

use App\Imports\CoDongImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class CoDongImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codong:import {file : Đường dẫn file Excel cần import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dữ liệu cổ đông từ file Excel vào bảng securities_management';

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
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Validate file exists
        if (!file_exists($filePath)) {
            $this->error("❌ File không tồn tại: {$filePath}");
            return 1;
        }

        // Validate file is Excel
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['xlsx', 'xls', 'csv'])) {
            $this->error("❌ File phải có định dạng Excel (xlsx, xls) hoặc CSV");
            return 1;
        }

        try {
            $this->info("🔄 Đang import file: {$filePath}");
            $this->newLine();

            $import = new CoDongImport();
            
            // Import file
            Excel::import($import, $filePath);

            $errors = $import->getErrors();
            $processedRows = $import->getProcessedRows();

            // Display results
            $this->info("✅ Import thành công!");
            $this->info("📊 Tổng số dòng xử lý: {$processedRows}");
            $this->newLine();

            if (count($errors) > 0) {
                $this->warn("⚠️  Có " . count($errors) . " dòng lỗi:");
                $this->newLine();

                foreach ($errors as $error) {
                    $this->line("<fg=red>Dòng {$error['row']}: {$error['message']}</>");
                }

                Log::warning("Import completed with errors", [
                    'file' => $filePath,
                    'processed_rows' => $processedRows,
                    'error_count' => count($errors),
                    'errors' => $errors
                ]);

                return 0;
            }

            Log::info("Import completed successfully", [
                'file' => $filePath,
                'processed_rows' => $processedRows
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Lỗi import file:");
            $this->error($e->getMessage());
            
            Log::error("Import error", [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
