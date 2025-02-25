<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\View\View;

use App\Models\Account;
use App\Models\Country;
use App\Models\City;

class RegisterController extends Controller
{
    /**
     * Display a register form.
     */
    public function showRegistrationForm(): View
    {
        $countries = Country::orderBy('name', 'asc')->get();
        $isAdmin = false;
        if(Auth::check() && Auth::user()->admin){
            $isAdmin = true;
        }
        return view('auth.register', compact('countries', 'isAdmin'));
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:8|max:250|unique:account,username',
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:account,email',
            'password' => 'required|string|min:8|confirmed',
            'workfield' => 'nullable|string|max:250',
            'city' => 'nullable|integer|exists:city,id',
        ]);

        Account::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'workfield' => $request->workfield,
            'city' => $request->city,
            'admin' => false,
        ]);

         if (Auth::check()) {
             session()->flash('message', [
                 'type' => 'success',
                 'text' => 'User created successfully.',
             ]);
            return redirect()->route('admin.accounts')
                ->withSuccess('You have successfully created a new account');
        } else {
            $credentials = $request->only('email', 'password');
            Auth::attempt($credentials);
            $request->session()->regenerate();

            session()->flash('message', [
                'type' => 'success',
                'text' => 'Successfully registered and logged in.',
            ]);
            return redirect()->route('projects')
                ->withSuccess('You have successfully registered & logged in!');
        }
        
    }
}
