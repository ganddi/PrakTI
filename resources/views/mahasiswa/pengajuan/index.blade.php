@extends('layouts.app')
@section('judul', 'Pengajuan KPI')

@section('content')
    @if (! $utama)
        <section class="pk-panel">
            <div class="pk-empty">
                <i class="bi bi-send" aria-hidden="true"></i>
                <h3>Belum ada pengajuan KPI</h3>
                <p>Siapkan data instansi tempat KPI dan surat permohonan dalam format PDF.</p>
                <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary">Ajukan KPI</a>
            </div>
        </section>
    @else
        <section class="pk-panel mb-4">
            <div class="pk-panel-head">
                <div>
                    <h2 class="pk-panel-title fs-5">{{ $utama->nama_instansi }}</h2>
                    <span class="text-secondary">Diajukan {{ $utama->created_at->translatedFormat('d F Y') }}</span>
                </div>
                <x-status :value="$utama->status" />
            </div>

            @if ($utama->status === 'diajukan')
                <div class="alert alert-info">Pengajuan sedang diperiksa admin program studi. Anda akan menerima email setelah diverifikasi.</div>
            @elseif ($utama->status === 'ditolak')
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Pengajuan belum disetujui</div>
                    <p class="mb-2">{{ $utama->catatan_admin }}</p>
                    <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-sm btn-danger">Ajukan ulang dengan perbaikan</a>
                </div>
            @endif

            <dl class="pk-info">
                <div><dt>Bidang kerja</dt><dd>{{ $utama->bidang_kerja }}</dd></div>
                <div><dt>Periode</dt><dd>{{ $utama->tanggal_mulai->translatedFormat('d M Y') }} – {{ $utama->tanggal_selesai->translatedFormat('d M Y') }}</dd></div>
                <div><dt>Dosen pembimbing</dt><dd>{{ $utama->pembimbing->name ?? 'Ditetapkan setelah verifikasi' }}</dd></div>
                <div><dt>Pembimbing lapangan</dt><dd>{{ $utama->pembimbing_lapangan ?: '–' }}</dd></div>
                <div><dt>Alamat instansi</dt><dd class="fw-normal">{{ $utama->alamat_instansi }}</dd></div>
                <div><dt>Kontak instansi</dt><dd>{{ $utama->kontak_instansi ?: '–' }}</dd></div>
            </dl>
        </section>

        <section class="mb-4">
            <div class="pk-panel-head">
                <h2 class="pk-panel-title">Dokumen</h2>
            </div>
            @include('shared.dokumen', ['dokumen' => $utama->dokumen])

            @if ($utama->status !== 'ditolak')
                <div class="pk-panel mt-3">
                    <h3 class="pk-panel-title mb-1">Unggah dokumen</h3>
                    <p class="text-secondary small">Contoh: surat balasan dari instansi atau laporan akhir KPI. Format PDF, Word, JPG, atau PNG, maksimal 5 MB.</p>
                    <form method="POST" action="{{ route('mahasiswa.pengajuan.dokumen', $utama) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label for="jenis" class="form-label">Jenis dokumen</label>
                            <select id="jenis" name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                @foreach (\App\Models\Dokumen::JENIS as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected(old('jenis', 'surat_balasan') === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="berkas" class="form-label">Berkas</label>
                            <input id="berkas" name="berkas" type="file" class="form-control @error('berkas') is-invalid @enderror"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Unggah dokumen</button></div>
                    </form>
                </div>
            @endif
        </section>

        @if ($riwayat->isNotEmpty())
            <section class="pk-panel">
                <h2 class="pk-panel-title mb-3">Riwayat pengajuan sebelumnya</h2>
                <div class="table-responsive">
                    <table class="table pk-table">
                        <thead><tr><th>Instansi</th><th>Diajukan</th><th>Status</th><th>Catatan admin</th></tr></thead>
                        <tbody>
                        @foreach ($riwayat as $r)
                            <tr>
                                <td class="fw-semibold">{{ $r->nama_instansi }}</td>
                                <td class="text-nowrap">{{ $r->created_at->translatedFormat('d M Y') }}</td>
                                <td><x-status :value="$r->status" /></td>
                                <td>{{ $r->catatan_admin ?: '–' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
@endsection
