<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        return view('auth.admin.login');
    }

    /**
     * Handle admin login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin) {
            return back()->withErrors([
                'email' => 'Email không tồn tại trong hệ thống.',
            ])->onlyInput('email');
        }

        if (is_null($admin->email_verified_at)) {
            return back()->withErrors([
                'email' => 'Tài khoản chưa được xác thực. Vui lòng kiểm tra email để xác thực.',
            ])->onlyInput('email');
        }

        if (!$admin->is_active) {
            return back()->withErrors([
                'email' => 'Tài khoản đã bị vô hiệu hóa.',
            ])->onlyInput('email');
        }

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.admin.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:admins,email']
        ], [
            'email.exists' => 'Email này không tồn tại trong hệ thống.'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (is_null($admin->email_verified_at)) {
            return back()->withErrors([
                'email' => 'Tài khoản chưa được xác thực.'
            ]);
        }

        // Generate reset token
        $token = Str::random(64);

        // Store token in database
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Send email
        try {
            Mail::send('emails.admin.reset-password', [
                'admin' => $admin,
                'token' => $token,
                'resetUrl' => route('admin.password.reset', ['token' => $token, 'email' => $request->email])
            ], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Đặt lại mật khẩu Admin');
            });

            return back()->with('status', 'Liên kết đặt lại mật khẩu đã được gửi đến email của bạn!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra khi gửi email. Vui lòng thử lại.'
            ]);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.admin.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Handle reset password request
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if token exists and is valid
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['token' => 'Token không hợp lệ hoặc đã hết hạn.']);
        }

        // Check if token is expired (24 hours)
        if (Carbon::parse($passwordReset->created_at)->addHours(24)->isPast()) {
            return back()->withErrors(['token' => 'Token đã hết hạn.']);
        }

        // Update password
        $admin = Admin::where('email', $request->email)->first();
        $admin->password = Hash::make($request->password);
        $admin->save();

        // Delete the token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('status', 'Mật khẩu đã được đặt lại thành công!');
    }

    /**
     * Handle admin logout request
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}