<?php

namespace App\Http\Controllers;

use App\Models\AktivitasSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $fotoBaru = $request->file('foto_profil');
        $fotoLama = $user->foto_profil;
        $pathFotoBaru = null;

        if ($fotoBaru) {
            $pathFotoBaru = $fotoBaru->store('profil', 'public');
        }

        try {
            DB::transaction(function () use ($user, $profil, $data, $pathFotoBaru) {
                $dataUser = ['email' => $data['email']];
                if ($profil === null) {
                    $dataUser['username'] = $data['username'];
                }
                if ($pathFotoBaru) {
                    $dataUser['foto_profil'] = $pathFotoBaru;
                }
                $user->update($dataUser);
                if ($user->isMahasiswa()) {
                    $profil->update(collect($data)->only(['nama_lengkap', 'email', 'no_hp', 'alamat'])->all());
                } elseif ($user->isPembimbing()) {
                    $profil->update(collect($data)->only(['nama_lengkap', 'email', 'no_hp', 'jabatan'])->all());
                }
            });
        } catch (\Throwable $exception) {
            if ($pathFotoBaru) {
                Storage::disk('public')->delete($pathFotoBaru);
            }
            throw $exception;
        }

        if ($pathFotoBaru && $fotoLama) {
            Storage::disk('public')->delete($fotoLama);
        }

        AktivitasSistem::catat('Memperbarui profil', 'akun', $user->id);

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function hapusFoto(Request $request)
    {
        $user = $request->user();

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
            $user->update(['foto_profil' => null]);
            AktivitasSistem::catat('Menghapus foto profil', 'akun', $user->id);
        }

        return back()->with('sukses', 'Foto profil berhasil dihapus.');
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
