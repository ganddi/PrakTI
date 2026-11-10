@extends('layouts.app')
@section('judul', 'Detail pengajuan KPI')

@section('content')
    <a href="{{ route('admin.pengajuan.index', ['status' => $pengajuan->status]) }}" class="pk-back"><i class="bi bi-arrow-left"></i>Kembali ke daftar pengajuan</a>

    @if ($pengajuan->status === 'diajukan')
        <section class="pk-panel mb-4">
            <h2 class="pk-panel-title mb-1">Verifikasi pengajuan</h2>
            <p class="pk-lead mb-3">Periksa data instansi dan dokumen di bawah, lalu tentukan keputusan. Mahasiswa akan menerima email berisi hasilnya.</p>

            <form method="POST" action="{{ route('admin.pengajuan.verifikasi', $pengajuan) }}" data-verifikasi>
                @csrf @method('PATCH')
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="pk-choice">
                            <input class="form-check-input" type="radio" name="keputusan" value="setujui" @checked(old('keputusan') === 'setujui')>
                            <span class="fw-semibold">Setujui pengajuan<small>KPI mulai berjalan dan dosen pembimbing ditetapkan.</small></span>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="pk-choice">
                            <input class="form-check-input" type="radio" name="keputusan" value="tolak" @checked(old('keputusan') === 'tolak')>
                            <span class="fw-semibold">Tolak pengajuan<small>Mahasiswa dapat memperbaiki dan mengajukan ulang.</small></span>
                        </label>
                    </div>
                </div>

                <div class="mb-3" data-blok="pembimbing" hidden>
                    <label for="pembimbing_id" class="form-label">Dosen pembimbing</label>
                    <select id="pembimbing_id" name="pembimbing_id" class="form-select @error('pembimbing_id') is-invalid @enderror">
                        <option value="">Pilih dosen pembimbing</option>
                        @foreach ($daftarPembimbing as $d)
                            <option value="{{ $d->id }}" @selected((string) old('pembimbing_id') === (string) $d->id)>
                                {{ $d->name }} ({{ $d->jumlah_bimbingan }} mahasiswa sedang dibimbing)
                            </option>
                        @endforeach
                    </select>
                    @if ($daftarPembimbing->isEmpty())
                        <div class="form-text text-danger">Belum ada dosen pembimbing aktif. Aktifkan akun dosen di menu Kelola pengguna.</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="catatan_admin" class="form-label" data-label-catatan>Catatan untuk mahasiswa (opsional)</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="3" class="form-control @error('catatan_admin') is-invalid @enderror">{{ old('catatan_admin') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" disabled>Pilih keputusan</button>
            </form>
        </section>
    @endif

    @include('shared.detail-kpi', ['pengajuan' => $pengajuan, 'mode' => 'admin'])

    @if ($pengajuan->status === 'berjalan')
        <section class="pk-panel mt-4">
            <div class="row g-4">
                <div class="col-lg-7">
                    <h2 class="pk-panel-title mb-1">Ganti dosen pembimbing</h2>
                    <p class="text-secondary small">Mahasiswa dan dosen baru akan menerima email pemberitahuan.</p>
                    <form method="POST" action="{{ route('admin.pengajuan.pembimbing', $pengajuan) }}" class="d-flex flex-wrap gap-2">
                        @csrf @method('PATCH')
                        <select name="pembimbing_id" class="form-select" style="max-width: 360px" aria-label="Dosen pembimbing" required>
                            @foreach ($daftarPembimbing as $d)
                                <option value="{{ $d->id }}" @selected($d->id === $pengajuan->pembimbing_id)>{{ $d->name }} ({{ $d->jumlah_bimbingan }})</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-primary" type="submit">Simpan pembimbing</button>
                    </form>
                </div>
                <div class="col-lg-5">
                    <h2 class="pk-panel-title mb-1">Selesaikan KPI</h2>
                    <p class="text-secondary small">Tandai selesai setelah mahasiswa menyelesaikan kegiatan dan laporan akhir.</p>
                    <form method="POST" action="{{ route('admin.pengajuan.selesai', $pengajuan) }}"
                          data-confirm="Tandai KPI {{ $pengajuan->mahasiswa->name }} sebagai selesai? Mahasiswa tidak dapat menambah logbook setelahnya.">
                        @csrf @method('PATCH')
                        <button class="btn btn-outline-success" type="submit"><i class="bi bi-check2-circle me-1"></i>Tandai selesai</button>
                    </form>
                </div>
            </div>
        </section>
    @endif
@endsection
