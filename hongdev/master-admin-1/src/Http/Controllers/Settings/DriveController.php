<?php

namespace Hongdev\MasterAdmin\Http\Controllers\Settings;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DriveController extends Controller
{
    /**
     * Show Google Drive configuration form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $config = [
            'client_id' => env('GOOGLE_DRIVE_CLIENT_ID', ''),
            'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET') ? '••••••••' : '',
            'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN') ? '••••••••' : '',
            'access_token' => env('GOOGLE_DRIVE_ACCESS_TOKEN') ? '••••••••' : '',
            'folder' => env('GOOGLE_DRIVE_FOLDER', ''),
        ];
        
        return view('master-admin::master-admin.page.settings.drive.index', [
            'config' => $config
        ]);
    }
    
    /**
     * Update Google Drive configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'filesystem_cloud' => 'nullable',
            'folder' => 'nullable',
            'access_token' => 'nullable',
            'client_secret' => 'nullable',
            'refresh_token' => 'nullable',
        ]);
        
        try {
            $envPath = base_path('.env');
            $envContent = File::get($envPath);
            
            $updates = [
                'GOOGLE_DRIVE_CLIENT_ID' => $request->client_id,
                'FILESYSTEM_CLOUD' => $request->filesystem_cloud ?: 'google',
                'GOOGLE_DRIVE_FOLDER' => $request->folder ?: '',
                'GOOGLE_DRIVE_ACCESS_TOKEN' => $request->access_token ?: '',
            ];
            
            if ($request->client_secret && $request->client_secret !== '••••••••') {
                $updates['GOOGLE_DRIVE_CLIENT_SECRET'] = $request->client_secret;
            }
            
            if ($request->refresh_token && $request->refresh_token !== '••••••••') {
                $updates['GOOGLE_DRIVE_REFRESH_TOKEN'] = $request->refresh_token;
            }
            
            foreach ($updates as $key => $value) {
                if (strpos($envContent, $key . '=') !== false) {
                    $envContent = preg_replace('/^' . $key . '=.*$/m', $key . '=' . $value, $envContent);
                } else {
                    $envContent .= "\n" . $key . '=' . $value;
                }
            }
            
            File::put($envPath, $envContent);
            Artisan::call('config:clear');
            
            return redirect()->route('master-admin.settings.drive.index')
                ->with('success', 'Google Drive configuration updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating Google Drive configuration: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Test Google Drive connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function test()
    {
        try {
            $disk = Storage::disk('google');
            $contents = $disk->files('/');
            
            return redirect()->back()->with('success', "Google Drive connection successful!");

        } catch (\Exception $e) {
            Log::error('Google Drive test failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Google Drive test failed: ' . $e->getMessage());
        }
    }
}
