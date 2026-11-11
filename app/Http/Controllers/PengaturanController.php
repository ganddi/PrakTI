<?php

namespace App\Http\Controllers;

use App\Models\AktivitasSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Menu "Pengaturan" pada mockup SDD: profil dan kata sandi. */
class PengaturanController extends Controller
{
    public function edit(Request $request)
    {
        return view('pengaturan.index', ['user' => $request->user(), 'profil' => $request->user()->profil()]);
    }

    public function profil(Request $request)
    {
        $user = $request->user();
        $profil = $user->profil();

        $data = $request->validate([
            'nama_lengkap' => [$profil ? 'required' : 'nullable', 'string', 'max:100'],
            'username' => [$profil ? 'nullable' : 'required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'jabatan' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($user, $profil, $data) {
            $user->update(['email' => $data['email']] + ($profil ? [] : ['username' => $data['username']]));
            if ($user->isMahasiswa()) {
                $profil->update(collect($data)->only(['nama_lengkap', 'email', 'no_hp', 'alamat'])->all());
            } elseif ($user->isPembimbing()) {
                $profil->update(collect($data)->only(['nama_lengkap', 'email', 'no_hp', 'jabatan'])->all());
            }
        });

        AktivitasSistem::catat('Memperbarui profil', 'akun', $user->id);

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function kataSandi(Request $request)
    {
        $data = $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $data['password']]);
        AktivitasSistem::catat('Mengganti kata sandi', 'akun', $request->user()->id);

        return back()->with('sukses', 'Kata sandi berhasil diganti.');
    }
}
