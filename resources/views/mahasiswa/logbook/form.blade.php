@extends('layouts.app')
@section('judul', $logbook->exists ? 'Ubah logbook' : 'Isi logbook')

@section('content')
    <a href="{{ route('mahasiswa.logbook.index') }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke logbook</a>

    @if ($logbook->exists && $logbook->komentar->isNotEmpty())
        <div class="alert alert-warning" style="max-width: 820px">
            <div class="fw-semibold mb-1">Catatan pembimbing</div>
            @foreach ($logbook->komentar as $k)
                <p class="mb-1">{{ $k->isi }} <small class="text-secondary">– {{ $k->user->name }}, {{ $k->created_at->diffForHumans() }}</small></p>
            @endforeach
        </div>
    @endif

    <section class="pk-panel" style="max-width: 820px">
        <form method="POST" enctype="multipart/form-data" novalidate
              action="{{ $logbook->exists ? route('mahasiswa.logbook.update', $logbook) : route('mahasiswa.logbook.store') }}">
            @csrf
            @if ($logbook->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <x-input name="tanggal" label="Tanggal" type="date" :required="true"
                             :value="$logbook->tanggal?->format('Y-m-d')"
                             min="{{ $pengajuan->tanggal_mulai->format('Y-m-d') }}"
                             max="{{ today()->min($pengajuan->tanggal_selesai)->format('Y-m-d') }}" />
                </div>
                <div class="col-6 col-md-4">
                    <x-input name="jam_mulai" label="Jam mulai" type="time" :required="true" :value="substr((string) $logbook->jam_mulai, 0, 5)" />
                </div>
                <div class="col-6 col-md-4">
                    <x-input name="jam_selesai" label="Jam selesai" type="time" :required="true" :value="substr((string) $logbook->jam_selesai, 0, 5)" />
                </div>
            </div>

            <x-input name="kegiatan" label="Uraian kegiatan" type="textarea" rows="7" :value="$logbook->kegiatan" :required="true"
                     hint="Tulis apa yang dikerjakan, hasilnya, dan kendala yang ditemui. Minimal 20 karakter." />

            <x-input name="lampiran" label="Lampiran foto atau dokumen" type="file" accept=".pdf,.jpg,.jpeg,.png"
                     :hint="$logbook->lampiran ? 'Sudah ada lampiran. Unggah berkas baru untuk menggantinya. PDF, JPG, atau PNG maksimal 2 MB.' : 'PDF, JPG, atau PNG maksimal 2 MB.'" />

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ $logbook->exists ? 'Simpan dan kirim ulang' : 'Simpan logbook' }}</button>
                <a href="{{ route('mahasiswa.logbook.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
