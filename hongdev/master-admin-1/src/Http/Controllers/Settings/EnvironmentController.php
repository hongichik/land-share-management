<?php

namespace Hongdev\MasterAdmin\Http\Controllers\Settings;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class EnvironmentController extends Controller
{
    /**
     * Show environment settings
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('master-admin::master-admin.page.settings.environment.index');
    }

    /**
     * Change the application environment
     *
     * @param string $env The environment to set (local, testing, production)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function change($env)
    {
        if (!in_array($env, ['local', 'testing', 'production'])) {
            return redirect()->back()->with('error', 'Invalid environment specified');
        }
        
        try {
            $envPath = base_path('.env');
            $envContent = File::get($envPath);
            
            // Replace APP_ENV value
            $updatedContent = preg_replace('/^APP_ENV=(.*)$/m', "APP_ENV={$env}", $envContent);
            
            if ($updatedContent !== $envContent) {
                File::put($envPath, $updatedContent);
                
                // Clear config cache to reload environment
                Artisan::call('config:clear');
                
                return redirect()->back()->with('success', "Environment changed to {$env}. Restart server to apply changes.");
            }
            
            return redirect()->back()->with('info', "Environment was already set to {$env}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error changing environment: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle debug mode
     *
     * @param string $mode Either 'on' or 'off'
     * @return \Illuminate\Http\RedirectResponse
     */
    public function debug($mode)
    {
        if (!in_array($mode, ['on', 'off'])) {
            return redirect()->back()->with('error', 'Invalid debug mode specified');
        }
        
        $debug = ($mode === 'on') ? 'true' : 'false';
        
        try {
            $envPath = base_path('.env');
            $envContent = File::get($envPath);
            
            // Replace APP_DEBUG value
            $updatedContent = preg_replace('/^APP_DEBUG=(.*)$/m', "APP_DEBUG={$debug}", $envContent);
            
            if ($updatedContent !== $envContent) {
                File::put($envPath, $updatedContent);
                
                // Clear config cache to reload environment
                Artisan::call('config:clear');
                
                $status = ($mode === 'on') ? 'enabled' : 'disabled';
                return redirect()->back()->with('success', "Debug mode {$status}. Restart server to apply changes.");
            }
            
            $status = ($mode === 'on') ? 'enabled' : 'disabled';
            return redirect()->back()->with('info', "Debug mode was already {$status}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error changing debug mode: ' . $e->getMessage());
        }
    }
}
