@extends('layouts.app')
@section('judul', 'Pengaturan')
@section('subjudul', 'Kelola profil dan keamanan akun Anda')

@section('content')
    <div class="row g-4" style="max-width: 1000px">
        <div class="col-lg-7">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Profil</h2>
                <form method="POST" action="{{ route('pengaturan.profil') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="pk-profile-avatar" id="foto-profil-preview" aria-label="Foto profil">
                            @if ($user->foto_profil_url)
                                <img src="{{ $user->foto_profil_url }}" alt="Foto profil {{ $user->name }}">
                            @else
                                <i class="bi bi-person-fill" aria-hidden="true"></i>
                            @endif
                        </div>
                        <div>
                            <label class="form-label" for="foto_profil">Foto profil</label>
                            <input class="form-control" type="file" id="foto_profil" name="foto_profil" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG, atau WebP. Maksimal 2 MB.</div>
                            @if ($user->foto_profil)
                                <button class="btn btn-link px-0 text-danger small" type="button"
                                        onclick="event.preventDefault(); document.getElementById('hapus-foto-form').submit();">
                                    Hapus foto
                                </button>
                            @endif
                        </div>
                    </div>
                    @if ($profil)
                        <x-input name="nama_lengkap" label="Nama lengkap" :value="$profil->nama_lengkap" :required="true" />
                        <div class="mb-3"><label class="form-label">{{ $user->isMahasiswa() ? 'NIM' : 'NIDN' }}</label><input class="form-control" value="{{ $user->nomor_induk }}" disabled></div>
                    @else
                        <x-input name="username" label="Username" :value="$user->username" :required="true" />
                    @endif
                    <div class="row g-3">
                        <div class="col-sm-6"><x-input name="email" label="Email" type="email" :value="$user->email" :required="true" /></div>
                        @if ($profil)<div class="col-sm-6"><x-input name="no_hp" label="Nomor HP" type="tel" :value="$profil->no_hp" /></div>@endif
                    </div>
                    @if ($user->isMahasiswa())
                        <x-input name="alamat" label="Alamat" type="textarea" rows="2" :value="$profil->alamat" />
                        <p class="small text-secondary">Program studi: {{ $profil->program_studi }}, {{ $profil->fakultas }}. Hubungi admin jika data ini perlu diubah.</p>
                    @elseif ($user->isPembimbing())
                        <x-input name="jabatan" label="Jabatan" :value="$profil->jabatan" />
                    @endif
                    <button class="btn btn-primary" type="submit">Simpan profil</button>
                </form>
                @if ($user->foto_profil)
                    <form id="hapus-foto-form" method="POST" action="{{ route('pengaturan.foto.hapus') }}" class="d-none">
                        @csrf @method('DELETE')
                    </form>
                @endif
            </section>
        </div>
        <div class="col-lg-5">
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Ganti kata sandi</h2>
                <form method="POST" action="{{ route('pengaturan.sandi') }}">
                    @csrf @method('PUT')
                    <x-input name="password_lama" label="Kata sandi saat ini" type="password" :required="true" autocomplete="current-password" />
                    <x-input name="password" label="Kata sandi baru" type="password" :required="true" autocomplete="new-password" hint="Minimal 8 karakter." />
                    <x-input name="password_confirmation" label="Ulangi kata sandi baru" type="password" :required="true" autocomplete="new-password" />
                    <button class="btn btn-primary" type="submit">Ganti kata sandi</button>
                </form>
            </section>
            <p class="small text-secondary mt-3">Login terakhir: {{ $user->last_login_at?->translatedFormat('d F Y H:i') ?? '–' }}</p>
        </div>
    </div>
@push('scripts')
<script>
    const fotoInput = document.getElementById('foto_profil');
    const fotoPreview = document.getElementById('foto-profil-preview');

    fotoInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            fotoPreview.innerHTML = `<img src="${event.target.result}" alt="Pratinjau foto profil">`;
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
@endsection
