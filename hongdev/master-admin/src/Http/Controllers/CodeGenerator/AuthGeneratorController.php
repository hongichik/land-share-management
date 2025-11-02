<?php

namespace Hongdev\MasterAdmin\Http\Controllers\CodeGenerator;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AuthGeneratorController extends Controller
{
    public function index()
    {
        return view('master-admin::master-admin.page.code-generator.auth.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'auth_type' => 'required|in:single,dual',
            'features' => 'array',
        ]);

        try {
            $authType = $request->input('auth_type');
            $features = $request->input('features', []);

            if ($authType === 'single') {
                $this->generateSingleAuth($features);
                $message = 'Single authentication system generated successfully!';
            } else {
                $this->generateDualAuth($features);
                $message = 'Dual authentication system (Admin + User) generated successfully!';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating auth: ' . $e->getMessage());
        }
    }

    private function generateSingleAuth($features)
    {
        dd($features);
    }

    private function generateDualAuth($features)
    {
        // Generate migrations
        Artisan::call('make:migration', [
            'name' => 'create_users_table',
            '--create' => 'users'
        ]);

        Artisan::call('make:migration', [
            'name' => 'create_admins_table',
            '--create' => 'admins'
        ]);

        // Generate models
        Artisan::call('make:model', ['name' => 'User']);
        Artisan::call('make:model', ['name' => 'Admin']);

        // Generate auth controllers
        $this->generateAuthControllers('dual', $features);

        // Generate auth views
        $this->generateAuthViews('dual', $features);

        // Generate routes
        $this->generateAuthRoutes('dual');
    }

    private function generateAuthControllers($type, $features)
    {
        if ($type === 'single') {
            Artisan::call('make:controller', ['name' => 'Auth/LoginController']);
            Artisan::call('make:controller', ['name' => 'Auth/RegisterController']);
            
            if (in_array('forgot_password', $features)) {
                Artisan::call('make:controller', ['name' => 'Auth/ForgotPasswordController']);
                Artisan::call('make:controller', ['name' => 'Auth/ResetPasswordController']);
            }
        } else {
            // User controllers
            Artisan::call('make:controller', ['name' => 'Auth/User/LoginController']);
            Artisan::call('make:controller', ['name' => 'Auth/User/RegisterController']);
            
            // Admin controllers
            Artisan::call('make:controller', ['name' => 'Auth/Admin/LoginController']);
            
            if (in_array('forgot_password', $features)) {
                Artisan::call('make:controller', ['name' => 'Auth/User/ForgotPasswordController']);
                Artisan::call('make:controller', ['name' => 'Auth/Admin/ForgotPasswordController']);
            }
        }
    }

    private function generateAuthViews($type, $features)
    {
        // This would generate the actual view files
        // For now, we'll just create placeholder directories
        $viewsPath = base_path('resources/views/auth');
        
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        if ($type === 'dual') {
            $userViewsPath = base_path('resources/views/auth/user');
            $adminViewsPath = base_path('resources/views/auth/admin');
            
            if (!File::exists($userViewsPath)) {
                File::makeDirectory($userViewsPath, 0755, true);
            }
            
            if (!File::exists($adminViewsPath)) {
                File::makeDirectory($adminViewsPath, 0755, true);
            }
        }
    }

    private function generateAuthRoutes($type)
    {
        $routesPath = base_path('routes/auth.php');
        
        if (!File::exists($routesPath)) {
            $routeContent = $this->getRouteTemplate($type);
            File::put($routesPath, $routeContent);
        }
    }

    private function getRouteTemplate($type)
    {
        if ($type === 'single') {
            return "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n// Single Auth Routes\nRoute::group(['middleware' => 'guest'], function () {\n    Route::get('/login', 'Auth\\LoginController@showLoginForm')->name('login');\n    Route::post('/login', 'Auth\\LoginController@login');\n    Route::get('/register', 'Auth\\RegisterController@showRegistrationForm')->name('register');\n    Route::post('/register', 'Auth\\RegisterController@register');\n});\n\nRoute::post('/logout', 'Auth\\LoginController@logout')->name('logout');\n";
        } else {
            return "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n// User Auth Routes\nRoute::prefix('user')->group(function () {\n    Route::get('/login', 'Auth\\User\\LoginController@showLoginForm')->name('user.login');\n    Route::post('/login', 'Auth\\User\\LoginController@login');\n    Route::get('/register', 'Auth\\User\\RegisterController@showRegistrationForm')->name('user.register');\n    Route::post('/register', 'Auth\\User\\RegisterController@register');\n    Route::post('/logout', 'Auth\\User\\LoginController@logout')->name('user.logout');\n});\n\n// Admin Auth Routes\nRoute::prefix('admin')->group(function () {\n    Route::get('/login', 'Auth\\Admin\\LoginController@showLoginForm')->name('admin.login');\n    Route::post('/login', 'Auth\\Admin\\LoginController@login');\n    Route::post('/logout', 'Auth\\Admin\\LoginController@logout')->name('admin.logout');\n});\n";
        }
    }
}
