<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DosenPembimbing;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $kredensial = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::attempt($kredensial, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        $user = Auth::user();
        if ($user->status !== User::AKTIF) {
            Auth::logout();

            return back()->withErrors(['email' => $user->status === User::MENUNGGU
                ? 'Akun Anda menunggu aktivasi admin program studi.'
                : 'Akun Anda dinonaktifkan. Hubungi admin program studi.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        AktivitasSistem::catat('Login ke sistem', 'auth', $user->id);

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister() { return view('auth.register'); }

    /** Mahasiswa langsung aktif; dosen menunggu aktivasi admin. Akun admin dibuat oleh admin. */
    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::MAHASISWA, User::PEMBIMBING])],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'nomor_induk' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:users,username', 'unique:mahasiswa,nim', 'unique:dosen_pembimbing,nidn'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'program_studi' => ['required', 'string', 'max:100'],
            'fakultas' => ['required_if:role,mahasiswa', 'nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['nomor_induk'], 'email' => $data['email'], 'password' => $data['password'],
                'role' => $data['role'], 'status' => $data['role'] === User::MAHASISWA ? User::AKTIF : User::MENUNGGU,
            ]);
            $profil = ['user_id' => $user->id, 'nama_lengkap' => $data['nama_lengkap'], 'email' => $data['email'], 'no_hp' => $data['no_hp'] ?? null, 'program_studi' => $data['program_studi']];

            $data['role'] === User::MAHASISWA
                ? Mahasiswa::create($profil + ['nim' => $data['nomor_induk'], 'fakultas' => $data['fakultas']])
                : DosenPembimbing::create($profil + ['nidn' => $data['nomor_induk'], 'jabatan' => $data['jabatan'] ?? null]);

            return $user;
        });

        AktivitasSistem::catat('Registrasi akun '.User::PERAN[$user->role], 'auth', $user->id, null, $user);

        if ($user->status === User::AKTIF) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('sukses', 'Akun berhasil dibuat. Selamat datang di PrakTI.');
        }

        Notifikasi::kirim(User::where('role', User::ADMIN)->get(), 'Akun dosen pembimbing menunggu aktivasi',
            "{$data['nama_lengkap']} ({$data['nomor_induk']}) mendaftar sebagai dosen pembimbing.", 'akun', $user->id);

        return redirect()->route('login')->with('sukses', 'Akun dosen pembimbing berhasil dibuat. Anda bisa masuk setelah admin program studi mengaktifkannya.');
    }

    public function logout(Request $request)
    {
        AktivitasSistem::catat('Logout dari sistem', 'auth', auth()->id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
