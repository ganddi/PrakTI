<div class="row g-2">
    <div class="col-6"><x-input name="kode_periode" label="Kode periode" :value="$pr->kode_periode" :required="true" placeholder="KPI-2026-1" /></div>
    <div class="col-6"><x-input name="tahun" label="Tahun" type="number" :value="$pr->tahun ?? now()->year" :required="true" /></div>
</div>
<x-input name="nama_periode" label="Nama periode" :value="$pr->nama_periode" :required="true" placeholder="KPI Semester Ganjil 2026/2027" />
<div class="row g-2">
    <div class="col-6"><x-input name="tanggal_mulai" label="Mulai" type="date" :value="$pr->tanggal_mulai?->format('Y-m-d')" :required="true" /></div>
    <div class="col-6"><x-input name="tanggal_selesai" label="Selesai" type="date" :value="$pr->tanggal_selesai?->format('Y-m-d')" :required="true" /></div>
</div>
<x-input name="keterangan" label="Keterangan" type="textarea" rows="2" :value="$pr->keterangan" />
<div class="mb-3"><label for="status-periode" class="form-label">Status</label>
    <select id="status-periode" name="status" class="form-select">@foreach (['aktif' => 'Aktif (dapat dipilih mahasiswa)', 'selesai' => 'Selesai'] as $k => $l)<option value="{{ $k }}" @selected(old('status', $pr->status ?? 'aktif') === $k)>{{ $l }}</option>@endforeach</select></div>
