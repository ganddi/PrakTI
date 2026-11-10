{{-- Daftar dokumen KPI --}}
@if ($dokumen->isEmpty())
    <div class="pk-panel">
        <div class="pk-empty">
            <i class="bi bi-folder2-open" aria-hidden="true"></i>
            <h3>Belum ada dokumen</h3>
            <p>Dokumen yang diunggah mahasiswa akan tampil di sini.</p>
        </div>
    </div>
@else
    <div class="pk-panel">
        <div class="table-responsive">
            <table class="table pk-table">
                <thead><tr><th>Jenis</th><th>Nama berkas</th><th>Ukuran</th><th>Diunggah</th><th></th></tr></thead>
                <tbody>
                @foreach ($dokumen as $d)
                    <tr>
                        <td class="fw-semibold">{{ $d->jenis_label }}</td>
                        <td class="text-break">{{ $d->nama_asli }}</td>
                        <td class="text-nowrap">{{ $d->ukuran_format }}</td>
                        <td class="text-nowrap">{{ $d->created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('dokumen.unduh', $d) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1" aria-hidden="true"></i>Unduh
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
