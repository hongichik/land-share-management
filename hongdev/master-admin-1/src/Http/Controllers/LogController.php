<?php

namespace Hongdev\MasterAdmin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    /**
     * View Laravel logs
     *
     * @return \Illuminate\View\View
     */
    public function viewLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logContents = '';
        
        if (File::exists($logPath)) {
            // Get the last 1000 lines of the log file
            $logContents = $this->tailFile($logPath, 1000);
        }
        
        return view('master-admin::master-admin.page.logs', [
            'logContents' => $logContents
        ]);
    }
    
    /**
     * Clear Laravel logs
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        
        try {
            if (File::exists($logPath)) {
                File::put($logPath, '');
                Log::info('Logs were cleared by admin');
                return redirect()->back()->with('success', 'Laravel logs cleared successfully');
            }
            
            return redirect()->back()->with('info', 'Log file does not exist');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error clearing logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the last n lines of a file
     *
     * @param string $filePath
     * @param int $lines
     * @return string
     */
    private function tailFile($filePath, $lines = 100)
    {
        $file = File::get($filePath);
        $fileArray = explode("\n", $file);
        
        // Get the last n lines
        $fileArray = array_slice($fileArray, -$lines);
        
        return implode("\n", $fileArray);
    }
}
