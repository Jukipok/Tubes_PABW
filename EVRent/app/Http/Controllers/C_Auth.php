<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\M_User;
use App\Models\M_Pelanggan;

class C_Auth extends Controller
{
    public function viewLogin()
    {
        return view('auth.login');
    }

    public function viewRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required', // Assuming login by username as per diagram inputs
            'password' => 'required',
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) { // Attempt using username
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Redirect based on role
            switch ($user->role) {
                case 'admin_evrent':
                case 'admin_sewa':
                    return redirect()->route('admin.dashboard');
                case 'pemilik_rental':
                    return redirect()->route('owner.dashboard');
                default:
                    return redirect()->intended(route('katalog'));
            }
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'no_telepon' => 'required|string',
            'alamat' => 'required|string',
        ]);

        $user = M_User::create([
            'name' => $request->nama_lengkap, // Map nama_lengkap to required 'name' field
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
            'role' => 'pelanggan', // Default role for public registration
        ]);

        // Create Pelanggan entry
        M_Pelanggan::create([
            'id_user' => $user->id // CAREFUL: accessing id instead of id_user if I didn't map it properly. M_User uses 'users' table which has 'id'. Eloquent returns 'id'.
        ]);

        Auth::login($user);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
