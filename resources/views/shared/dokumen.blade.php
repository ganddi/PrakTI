{{-- Daftar dokumen KPI (SDD Tabel 3.10) --}}
@php($admin = auth()->user()?->isAdmin() ?? false)
@if ($admin && isset($pengajuan))
    <section class="pk-panel mb-3">
        <h3 class="pk-subtitle">Tambah dokumen</h3>
        <form method="POST" action="{{ route('admin.pengajuan.dokumen.store', $pengajuan) }}" enctype="multipart/form-data" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label for="jenis_dokumen_baru" class="form-label">Jenis dokumen</label>
                <select id="jenis_dokumen_baru" name="jenis_dokumen" class="form-select" required>
                    @foreach (\App\Models\DokumenKpi::JENIS as $jenis => $label)<option value="{{ $jenis }}">{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="berkas_baru" class="form-label">Berkas</label>
                <input id="berkas_baru" name="berkas" type="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload me-1" aria-hidden="true"></i>Tambah</button></div>
        </form>
        <p class="form-text mb-0 mt-2">PDF, Word, JPG, atau PNG; maksimal 5 MB.</p>
    </section>
@endif
@if ($dokumen->isEmpty())
    <div class="pk-panel"><div class="pk-empty"><i class="bi bi-folder2-open" aria-hidden="true"></i><h3>Belum ada dokumen</h3><p>Dokumen yang diunggah mahasiswa akan tampil di sini.</p></div></div>
@else
    <div class="pk-panel"><div class="table-responsive"><table class="table pk-table">
        <thead><tr><th>Jenis</th><th>Berkas</th><th>Diunggah</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @foreach ($dokumen as $d)
            <tr>
                <td class="fw-semibold">{{ $d->jenis_label }}</td>
                <td class="text-break">{{ $d->nama_file }}<span class="pk-sub">{{ strtoupper($d->format_file) }}, {{ $d->ukuran_format }}</span></td>
                <td class="text-nowrap">{{ $d->uploaded_at->translatedFormat('d M Y H:i') }}</td>
                <td><x-status :value="$d->status_verifikasi" />@if ($d->catatan)<span class="pk-sub mt-1">{{ $d->catatan }}</span>@endif</td>
                <td class="text-end">
                    <div class="d-flex flex-wrap justify-content-end gap-1">
                        <a href="{{ route('dokumen.unduh', ['dokumen' => $d, 'lihat' => 1]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1" aria-hidden="true"></i>Lihat</a>
                        <a href="{{ route('dokumen.unduh', $d) }}" class="btn btn-sm btn-outline-primary" aria-label="Unduh {{ $d->nama_file }}" title="Unduh"><i class="bi bi-download" aria-hidden="true"></i></a>
                        @if ($admin)
                            <details class="pk-document-edit">
                                <summary class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</summary>
                                <form method="POST" action="{{ route('admin.dokumen.update', $d) }}" enctype="multipart/form-data" class="pk-document-edit-form text-start">
                                    @csrf @method('PUT')
                                    <label for="jenis_dokumen_{{ $d->id }}" class="form-label">Jenis dokumen</label>
                                    <select id="jenis_dokumen_{{ $d->id }}" name="jenis_dokumen" class="form-select form-select-sm mb-2" required>
                                        @foreach (\App\Models\DokumenKpi::JENIS as $jenis => $label)<option value="{{ $jenis }}" @selected($d->jenis_dokumen === $jenis)>{{ $label }}</option>@endforeach
                                    </select>
                                    <label for="berkas_{{ $d->id }}" class="form-label">Ganti berkas (opsional)</label>
                                    <input id="berkas_{{ $d->id }}" name="berkas" type="file" class="form-control form-control-sm mb-2" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Simpan perubahan</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.dokumen.destroy', $d) }}" data-confirm="Hapus dokumen {{ $d->nama_file }}? Tindakan ini tidak dapat dibatalkan.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Hapus</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table></div></div>
@endif
