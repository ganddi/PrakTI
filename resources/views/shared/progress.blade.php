{{-- Linimasa progress KPI. $mode: 'mahasiswa' | 'pembimbing' | 'admin' --}}
@if ($daftarProgress->isEmpty())
    <div class="pk-panel">
        <div class="pk-empty">
            <i class="bi bi-bar-chart-steps" aria-hidden="true"></i>
            <h3>Belum ada pembaruan progress</h3>
            <p>{{ $mode === 'mahasiswa' ? 'Laporkan capaian KPI Anda secara berkala, misalnya setiap minggu atau setiap tahap selesai.' : 'Pembaruan progress dari mahasiswa akan tampil di sini.' }}</p>
        </div>
    </div>
@else
    <div class="pk-panel">
        <ol class="pk-timeline">
            @foreach ($daftarProgress as $pr)
                <li class="pk-timeline-item" id="progress-{{ $pr->id }}">
                    <span class="pk-timeline-percent" aria-label="{{ $pr->persentase }} persen">{{ $pr->persentase }}%</span>
                    <div class="pk-timeline-head">
                        <h3 class="pk-timeline-title">{{ $pr->judul }}</h3>
                        <x-status :value="$pr->status" />
                    </div>
                    <div class="pk-timeline-date mb-2">{{ $pr->created_at->translatedFormat('l, d F Y H:i') }}</div>
                    <p class="mb-2" style="max-width: 76ch">{!! nl2br(e($pr->deskripsi)) !!}</p>
                    @if ($pr->lampiran)
                        <p class="mb-2"><a href="{{ route('lampiran.unduh', ['progress', $pr->id]) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i>Lampiran</a></p>
                    @endif

                    @if ($mode === 'pembimbing' && $pr->status === 'menunggu')
                        <form method="POST" action="{{ route('pembimbing.progress.verifikasi', $pr) }}" class="pk-verify mb-3">
                            @csrf @method('PATCH')
                            <textarea name="catatan" rows="1" class="form-control form-control-sm" maxlength="1000"
                                      placeholder="Catatan untuk mahasiswa (wajib jika minta revisi)" aria-label="Catatan pembimbing"></textarea>
                            <button class="btn btn-sm btn-primary" type="submit" name="status" value="diverifikasi">Verifikasi</button>
                            <button class="btn btn-sm btn-outline-danger" type="submit" name="status" value="revisi" data-perlu-catatan>Minta revisi</button>
                        </form>
                    @endif

                    @include('shared.komentar', ['item' => $pr, 'tipe' => 'progress', 'mode' => $mode])
                </li>
            @endforeach
        </ol>
    </div>
@endif
