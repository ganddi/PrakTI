<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($kredensial, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun Anda belum diaktifkan oleh admin program studi. Silakan hubungi admin.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Mahasiswa langsung aktif. Akun dosen pembimbing menunggu aktivasi admin
     * agar tidak sembarang orang mendapat akses ke data mahasiswa.
     * Akun admin hanya dibuat oleh admin (menu Kelola pengguna) atau seeder.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', Rule::in([User::MAHASISWA, User::PEMBIMBING])],
            'nomor_induk' => ['required', 'string', 'max:30', 'unique:users,nomor_induk'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $data['is_active'] = $data['role'] === User::MAHASISWA;
        $user = User::create($data);

        if ($user->is_active) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('sukses', 'Akun berhasil dibuat. Selamat datang di PrakTI.');
        }

        Notifikasi::kirim(
            User::where('role', User::ADMIN)->get(),
            'Akun dosen pembimbing menunggu aktivasi',
            ["{$user->name} ({$user->nomor_induk}) mendaftar sebagai dosen pembimbing dan menunggu aktivasi."],
            route('admin.pengguna.index', ['peran' => User::PEMBIMBING]),
            'Periksa akun'
        );

        return redirect()->route('login')->with(
            'sukses',
            'Akun dosen pembimbing berhasil dibuat. Anda bisa masuk setelah admin program studi mengaktifkan akun ini.'
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
