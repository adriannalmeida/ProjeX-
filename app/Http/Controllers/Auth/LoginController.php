<?php
 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\View\View;

class LoginController extends Controller
{

    public function mainPage()
    {
        return view('pages.mainPage');
    }
    /**
     * Display a aboutUs.
     */
    public function aboutUs()
    {
        return view('pages.aboutUs');
    }

    /**
     * Display a login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Logged in successfully.',
            ]);
            return redirect('/projects');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username-email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginField = filter_var($credentials['username-email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = \App\Models\Account::where($loginField, $credentials['username-email'])->first();
 
        if ($user && $user->blocked) {
            return back()->withErrors([
                'username-email' => 'Your account has been blocked.',
            ])->onlyInput('username-email');
        }

        if (Auth::attempt([$loginField => $credentials['username-email'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $request->session()->regenerate();

            session()->flash('message', [
                'type' => 'success',
                'text' => 'Logged in successfully.',
            ]);
            return redirect()->intended('/projects');
        }

        return back()->withErrors([
            'username-email' => 'The provided credentials do not match our records.',
        ])->onlyInput('username-email');
    }

    /**
     * Log out the user from application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Logged out successfully!',
        ]);
        return redirect()->route('main.page')
            ->withSuccess('You have logged out successfully!');
    } 
}
