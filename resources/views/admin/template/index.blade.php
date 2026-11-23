@extends('layouts.app')
@section('judul', 'Template dokumen')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            @foreach (\App\Support\Template::JENIS as $jenis => $label)
                <section class="pk-panel">
                    <h2 class="pk-panel-title mb-1">{{ $label }}</h2>
                    <p class="small text-secondary mb-3">{{ ['logbook' => 'Diunduh mahasiswa di halaman logbook.', 'laporan' => 'Diunduh mahasiswa di halaman laporan.', 'penilaian' => 'Diserahkan mahasiswa ke dosen penguji dan pembimbing lapangan.'][$jenis] }}</p>
                    @forelse ($template[$jenis] as $t)
                        <x-berkas :href="route('template.unduh', $t->kode)" :nama="$t->nama" :ukuran="$t->ukuran" class="mb-2">
                            <form method="POST" action="{{ route('admin.template.destroy', $t->kode) }}" class="d-inline" data-confirm="Hapus template {{ $t->nama }}?">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Hapus {{ $t->nama }}"><i class="bi bi-trash"></i></button></form>
                        </x-berkas>
                    @empty<p class="text-secondary small mb-0">Belum ada template.</p>@endforelse
                </section>
            @endforeach
        </div>
        <div class="col-lg-4">
            <section class="pk-panel mb-3">
                <h2 class="pk-panel-title mb-1">Pengaturan surat permohonan KPI</h2>
                <p class="small text-secondary mb-3">Nilai ini menjadi isian awal surat baru. Nomor akan bertambah otomatis dan kembali ke awal setiap tahun.</p>
                <form method="POST" action="{{ route('admin.template.pengaturan-surat') }}">@csrf @method('PUT')
                    <x-input name="kode_nomor" label="Kode setelah nomor urut" :value="$pengaturanSurat->kode_nomor" :required="true" hint="Contoh: UN55.F4/KM.22" />
                    <x-input name="nomor_terakhir" label="Nomor terakhir yang digunakan tahun {{ $pengaturanSurat->tahun_nomor }}" type="number" :value="$pengaturanSurat->nomor_terakhir" :required="true" min="0" />
                    <x-input name="nama_penandatangan" label="Nama wakil dekan" :value="$pengaturanSurat->nama_penandatangan" :required="true" />
                    <x-input name="nip_penandatangan" label="NIP" :value="$pengaturanSurat->nip_penandatangan" :required="true" />
                    <x-input name="pangkat_golongan" label="Pangkat/golongan" :value="$pengaturanSurat->pangkat_golongan" :required="true" />
                    <x-input name="jabatan_penandatangan" label="Jabatan penandatangan" :value="$pengaturanSurat->jabatan_penandatangan" :required="true" />
                    <x-input name="kota_surat" label="Kota pada tanggal surat" :value="$pengaturanSurat->kota_surat" :required="true" />
                    <button class="btn btn-primary w-100" type="submit">Simpan pengaturan surat</button>
                </form>
            </section>
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Unggah template</h2>
                <form method="POST" action="{{ route('admin.template.store') }}" enctype="multipart/form-data">@csrf
                    <div class="mb-3"><label for="jenis" class="form-label">Jenis template</label><select id="jenis" name="jenis" class="form-select" required>
                        @foreach (\App\Support\Template::JENIS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                    <x-input name="berkas" label="Berkas" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx" :required="true" hint="PDF, Word, atau Excel, maksimal 10 MB. Nama berkas dipakai sebagai judul template." />
                    <button class="btn btn-primary w-100" type="submit">Unggah template</button>
                </form>
            </section>
        </div>
    </div>
@endsection
