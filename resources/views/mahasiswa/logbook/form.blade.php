@extends('layouts.app')
@section('judul', $logbook->exists ? 'Ubah logbook' : 'Isi logbook')

@section('content')
    <a href="{{ route('mahasiswa.logbook.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke logbook</a>
    @if ($logbook->catatan_dosen)<div class="alert alert-warning" style="max-width: 820px"><div class="fw-semibold mb-1">Catatan dosen pembimbing</div>{{ $logbook->catatan_dosen }}</div>@endif
    <section class="pk-panel" style="max-width: 820px">
        <form method="POST" novalidate action="{{ $logbook->exists ? route('mahasiswa.logbook.update', $logbook) : route('mahasiswa.logbook.store') }}">
            @csrf @if ($logbook->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4"><x-input name="tanggal" label="Tanggal" type="date" :required="true" :value="$logbook->tanggal?->format('Y-m-d')"
                    min="{{ $pengajuan->tanggal_mulai->format('Y-m-d') }}" max="{{ today()->min($pengajuan->tanggal_selesai)->format('Y-m-d') }}" /></div>
                <div class="col-md-8"><x-input name="judul_kegiatan" label="Judul kegiatan" :value="$logbook->judul_kegiatan" :required="true" maxlength="100" placeholder="Contoh: Implementasi halaman login" /></div>
            </div>
            <x-input name="deskripsi_kegiatan" label="Deskripsi kegiatan" type="textarea" rows="5" :value="$logbook->deskripsi_kegiatan" :required="true" hint="Tulis apa yang dikerjakan. Minimal 20 karakter." />
            <div class="row g-3">
                <div class="col-md-6"><x-input name="hasil_kegiatan" label="Hasil kegiatan" type="textarea" rows="3" :value="$logbook->hasil_kegiatan" /></div>
                <div class="col-md-6"><x-input name="kendala" label="Kendala" type="textarea" rows="3" :value="$logbook->kendala" /></div>
            </div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">{{ $logbook->exists ? 'Simpan dan kirim ulang' : 'Simpan logbook' }}</button>
                <a href="{{ route('mahasiswa.logbook.index') }}" class="btn btn-outline-secondary">Batal</a></div>
        </form>
    </section>
@endsection
