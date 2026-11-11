<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Models\DosenPembimbing;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Kelola data pengguna: tabel users + mahasiswa / dosen_pembimbing (SDD Tabel 3.1–3.3). */
class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $peran = $request->query('peran', User::MAHASISWA);
        abort_unless(array_key_exists($peran, User::PERAN), 404);

        $pengguna = User::with(['mahasiswa', 'dosen'])->where('role', $peran)
            ->when($request->query('q'), fn ($q, $c) => $q->where(fn ($w) => $w->where('username', 'like', "%{$c}%")->orWhere('email', 'like', "%{$c}%")
                ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nim', 'like', "%{$c}%"))
                ->orWhereHas('dosen', fn ($d) => $d->where('nama_lengkap', 'like', "%{$c}%")->orWhere('nidn', 'like', "%{$c}%"))))
            ->orderByRaw("status = 'aktif'")->latest('id')
            ->paginate(15)->withQueryString();

        $jumlah = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('admin.pengguna.index', compact('pengguna', 'peran', 'jumlah'));
    }

    public function create(Request $request)
    {
        $peran = array_key_exists($request->query('peran'), User::PERAN) ? $request->query('peran') : User::MAHASISWA;

        return view('admin.pengguna.form', ['pengguna' => new User(['role' => $peran, 'status' => User::AKTIF]), 'profil' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan($request));

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['role'] === User::ADMIN ? $data['username'] : $data['nomor_induk'],
                'email' => $data['email'], 'password' => $data['password'], 'role' => $data['role'], 'status' => $data['status'],
            ]);
            $this->simpanProfil($user, $data);

            return $user;
        });

        AktivitasSistem::catat("Menambah akun {$user->peran_label}: {$user->name}", 'pengguna', $user->id);

        return redirect()->route('admin.pengguna.index', ['peran' => $user->role])->with('sukses', "Akun {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $pengguna)
    {
        return view('admin.pengguna.form', ['pengguna' => $pengguna, 'profil' => $pengguna->profil()]);
    }

    public function update(Request $request, User $pengguna)
    {
        $request->merge(['role' => $pengguna->role]); // peran tidak diubah agar profil tetap konsisten
        $data = $request->validate($this->aturan($request, $pengguna));

        if ($pengguna->id === $request->user()->id) {
            $data['status'] = User::AKTIF;
        }

        DB::transaction(function () use ($pengguna, $data) {
            $pengguna->update(array_filter([
                'username' => $pengguna->isAdmin() ? $data['username'] : $data['nomor_induk'],
                'email' => $data['email'], 'status' => $data['status'], 'password' => $data['password'] ?? null,
            ], fn ($v) => $v !== null));
            $this->simpanProfil($pengguna, $data);
        });

        AktivitasSistem::catat("Memperbarui akun {$pengguna->name}", 'pengguna', $pengguna->id);

        return redirect()->route('admin.pengguna.index', ['peran' => $pengguna->role])->with('sukses', "Data {$pengguna->name} diperbarui.");
    }

    public function destroy(Request $request, User $pengguna)
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('gagal', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $pengguna->name;
        $peran = $pengguna->role;
        AktivitasSistem::catat("Menghapus akun {$nama}", 'pengguna', $pengguna->id);
        $pengguna->delete();

        return redirect()->route('admin.pengguna.index', ['peran' => $peran])->with('sukses', "Akun {$nama} telah dihapus.");
    }

    public function aktivasi(Request $request, User $pengguna)
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('gagal', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $aktif = $pengguna->status !== User::AKTIF;
        $pengguna->update(['status' => $aktif ? User::AKTIF : User::NONAKTIF]);
        AktivitasSistem::catat(($aktif ? 'Mengaktifkan' : 'Menonaktifkan')." akun {$pengguna->name}", 'pengguna', $pengguna->id);

        if ($aktif) {
            Notifikasi::kirim($pengguna, 'Akun PrakTI Anda sudah aktif', 'Akun Anda telah diaktifkan admin program studi. Silakan masuk.', 'akun');
        }

        return back()->with('sukses', "Akun {$pengguna->name} ".($aktif ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    private function simpanProfil(User $user, array $data): void
    {
        $profil = collect($data)->only(['nama_lengkap', 'no_hp', 'program_studi'])->all() + ['email' => $data['email']];

        if ($user->isMahasiswa()) {
            Mahasiswa::updateOrCreate(['user_id' => $user->id], $profil + ['nim' => $data['nomor_induk'], 'fakultas' => $data['fakultas'], 'alamat' => $data['alamat'] ?? null]);
        } elseif ($user->isPembimbing()) {
            DosenPembimbing::updateOrCreate(['user_id' => $user->id], $profil + ['nidn' => $data['nomor_induk'], 'jabatan' => $data['jabatan'] ?? null]);
        }
    }

    private function aturan(Request $request, ?User $pengguna = null): array
    {
        $role = $request->input('role');
        $profilId = $pengguna?->profil()?->id;
        $bukanAdmin = $role !== User::ADMIN;

        return [
            'role' => ['required', Rule::in(array_keys(User::PERAN))],
            'username' => [$bukanAdmin ? 'nullable' : 'required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($pengguna?->id)],
            'nama_lengkap' => [$bukanAdmin ? 'required' : 'nullable', 'string', 'max:100'],
            'nomor_induk' => [$bukanAdmin ? 'required' : 'nullable', 'string', 'max:20', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($pengguna?->id),
                $role === User::MAHASISWA ? Rule::unique('mahasiswa', 'nim')->ignore($profilId) : Rule::unique('dosen_pembimbing', 'nidn')->ignore($profilId)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($pengguna?->id)],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'program_studi' => [$bukanAdmin ? 'required' : 'nullable', 'string', 'max:100'],
            'fakultas' => [$role === User::MAHASISWA ? 'required' : 'nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'jabatan' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in([User::AKTIF, User::NONAKTIF, User::MENUNGGU])],
            'password' => [$pengguna ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }
}
