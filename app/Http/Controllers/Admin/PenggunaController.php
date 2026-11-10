<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Fitur 4: Kelola data pengguna (mahasiswa, dosen pembimbing, admin). */
class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $peran = $request->query('peran', User::MAHASISWA);
        abort_unless(array_key_exists($peran, User::PERAN), 404);

        $pengguna = User::where('role', $peran)
            ->when($request->query('q'), function ($query, $cari) {
                $query->where(fn ($w) => $w->where('name', 'like', "%{$cari}%")
                    ->orWhere('email', 'like', "%{$cari}%")
                    ->orWhere('nomor_induk', 'like', "%{$cari}%"));
            })
            ->orderBy('is_active')   // akun nonaktif / menunggu aktivasi tampil paling atas
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $jumlahPerPeran = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('admin.pengguna.index', compact('pengguna', 'peran', 'jumlahPerPeran'));
    }

    public function create(Request $request)
    {
        $peran = $request->query('peran', User::MAHASISWA);
        $pengguna = new User(['role' => array_key_exists($peran, User::PERAN) ? $peran : User::MAHASISWA, 'is_active' => true]);

        return view('admin.pengguna.form', compact('pengguna'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan());
        $data['is_active'] = $request->boolean('is_active');

        $pengguna = User::create($data);

        return redirect()->route('admin.pengguna.index', ['peran' => $pengguna->role])
            ->with('sukses', "Akun {$pengguna->name} berhasil ditambahkan.");
    }

    public function edit(User $pengguna)
    {
        return view('admin.pengguna.form', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $request->validate($this->aturan($pengguna));
        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Admin tidak bisa menurunkan peran atau menonaktifkan akunnya sendiri
        if ($pengguna->id === $request->user()->id) {
            $data['role'] = User::ADMIN;
            $data['is_active'] = true;
        }

        $pengguna->update($data);

        return redirect()->route('admin.pengguna.index', ['peran' => $pengguna->role])
            ->with('sukses', "Data {$pengguna->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $pengguna)
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('gagal', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $pengguna->name;
        $peran = $pengguna->role;
        $pengguna->delete();

        return redirect()->route('admin.pengguna.index', ['peran' => $peran])
            ->with('sukses', "Akun {$nama} telah dihapus.");
    }

    public function aktivasi(Request $request, User $pengguna)
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('gagal', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $pengguna->update(['is_active' => ! $pengguna->is_active]);

        if ($pengguna->is_active) {
            Notifikasi::kirim($pengguna, 'Akun PrakTI Anda sudah aktif', [
                'Akun Anda telah diaktifkan oleh admin program studi.',
                'Sekarang Anda dapat masuk menggunakan email dan kata sandi yang didaftarkan.',
            ], route('login'), 'Masuk ke PrakTI');
        }

        return back()->with('sukses', $pengguna->is_active
            ? "Akun {$pengguna->name} telah diaktifkan."
            : "Akun {$pengguna->name} telah dinonaktifkan.");
    }

    private function aturan(?User $pengguna = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', Rule::in(array_keys(User::PERAN))],
            'nomor_induk' => ['nullable', 'required_unless:role,admin', 'string', 'max:30',
                Rule::unique('users', 'nomor_induk')->ignore($pengguna?->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($pengguna?->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => [$pengguna ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }
}
