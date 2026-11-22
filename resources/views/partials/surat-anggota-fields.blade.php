@php
    $anggotaForm = old('anggota', $surat?->anggota ?? [['nama' => $pengajuan->mahasiswa->nama_lengkap, 'nim' => $pengajuan->mahasiswa->nim]]);
    $anggotaForm = array_values($anggotaForm ?: [['nama' => $pengajuan->mahasiswa->nama_lengkap, 'nim' => $pengajuan->mahasiswa->nim]]);
@endphp
<div data-anggota-form>
    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
        <label class="form-label mb-0">Nama anggota tim (maksimal 4 orang)</label>
        <button type="button" class="btn btn-sm btn-outline-primary" data-tambah-anggota @disabled(count($anggotaForm) >= 4)>
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah anggota
        </button>
    </div>
    <p class="form-text mt-0">Anggota pertama adalah mahasiswa pengaju. Tambahkan anggota tim lainnya bila ada.</p>
    <div data-anggota-list data-next-index="{{ count($anggotaForm) }}">
        @foreach ($anggotaForm as $i => $anggota)
            <div class="row g-2 align-items-end mb-2" data-anggota-row>
                <div class="col-md-6">
                    <label for="anggota_{{ $i }}_nama" class="form-label" data-label-anggota>Anggota {{ $loop->iteration }} — nama</label>
                    <input id="anggota_{{ $i }}_nama" name="anggota[{{ $i }}][nama]" value="{{ $anggota['nama'] ?? '' }}" class="form-control @error('anggota.'.$i.'.nama') is-invalid @enderror" maxlength="100" required @if (($kunciAnggotaPertama ?? false) && $i === 0) readonly @endif>
                    @error('anggota.'.$i.'.nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label for="anggota_{{ $i }}_nim" class="form-label visually-hidden">NIM anggota {{ $loop->iteration }}</label>
                    <input id="anggota_{{ $i }}_nim" name="anggota[{{ $i }}][nim]" value="{{ $anggota['nim'] ?? '' }}" class="form-control @error('anggota.'.$i.'.nim') is-invalid @enderror" maxlength="20" placeholder="NIM anggota {{ $loop->iteration }}" required @if (($kunciAnggotaPertama ?? false) && $i === 0) readonly @endif>
                    @error('anggota.'.$i.'.nim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-1">
                    @if (! (($kunciAnggotaPertama ?? false) && $i === 0))
                        <button type="button" class="btn btn-outline-danger pk-remove-member" data-hapus-anggota aria-label="Hapus anggota {{ $loop->iteration }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <template data-anggota-template>
        <div class="row g-2 align-items-end mb-2" data-anggota-row>
            <div class="col-md-6">
                <label for="anggota___INDEX___nama" class="form-label" data-label-anggota>Anggota — nama</label>
                <input id="anggota___INDEX___nama" name="anggota[__INDEX__][nama]" class="form-control" maxlength="100" required>
            </div>
            <div class="col-md-5">
                <label for="anggota___INDEX___nim" class="form-label visually-hidden">NIM anggota</label>
                <input id="anggota___INDEX___nim" name="anggota[__INDEX__][nim]" class="form-control" maxlength="20" placeholder="NIM anggota" required>
            </div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger pk-remove-member" data-hapus-anggota aria-label="Hapus anggota"><i class="bi bi-trash" aria-hidden="true"></i></button></div>
        </div>
    </template>
</div>
