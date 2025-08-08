<?php

namespace Hongdev\MasterAdmin\Http\Controllers\Settings;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    /**
     * Show mail configuration form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $config = [
            'driver' => config('mail.mailer'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
        
        return view('master-admin::master-admin.page.settings.mail.index', [
            'config' => $config
        ]);
    }
    
    /**
     * Update mail configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'driver' => 'required',
            'host' => 'required_unless:driver,log',
            'port' => 'required_unless:driver,log',
            'username' => 'required_unless:driver,log',
            'encryption' => 'nullable',
            'from_address' => 'required|email',
            'from_name' => 'required',
        ]);
        
        try {
            $envPath = base_path('.env');
            $envContent = File::get($envPath);
            
            $updates = [
                'MAIL_MAILER' => $request->driver,
                'MAIL_HOST' => $request->host,
                'MAIL_PORT' => $request->port,
                'MAIL_USERNAME' => $request->username,
                'MAIL_ENCRYPTION' => $request->encryption,
                'MAIL_FROM_ADDRESS' => $request->from_address,
                'MAIL_FROM_NAME' => '"' . $request->from_name . '"',
            ];
            
            if ($request->filled('password')) {
                $updates['MAIL_PASSWORD'] = $request->password;
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
            
            return redirect()->route('master-admin.settings.mail.index')
                ->with('success', 'Mail configuration updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating mail configuration: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Test mail connection
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function test()
    {
        try {
            Mail::raw('This is a test email from Master Admin.', function ($message) {
                $message->to(config('mail.from.address'))
                       ->subject('Master Admin Test Email');
            });
            
            return redirect()->back()->with('success', 'Test email sent successfully!');
        } catch (\Exception $e) {
            Log::error('Mail test failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Mail test failed: ' . $e->getMessage());
        }
    }
}
