{{-- Logbook bergaya buku bergaris (SDD Tabel 3.11). $mode: mahasiswa | pembimbing | admin --}}
@if ($logbooks->isEmpty())
    <div class="pk-logbook"><div class="pk-empty"><i class="bi bi-journal" aria-hidden="true"></i><h3>Belum ada logbook</h3>
        <p>{{ $mode === 'mahasiswa' ? 'Catat kegiatan KPI setiap hari kerja agar pembimbing bisa memantau perkembangan Anda.' : 'Logbook harian mahasiswa akan tampil di sini.' }}</p>
        @if ($mode === 'mahasiswa' && ($bisaTambah ?? false))<a href="{{ route('mahasiswa.logbook.create') }}" class="btn btn-primary">Isi logbook</a>@endif
    </div></div>
@else
    <div class="pk-logbook">
        @foreach ($logbooks as $log)
            <article class="pk-log {{ $log->status === 'revisi' ? 'pk-log--revisi' : '' }}" id="logbook-{{ $log->id }}">
                <div class="pk-log-date">
                    <span class="pk-log-day">{{ $log->tanggal->translatedFormat('l') }}</span>
                    <span class="pk-log-num">{{ $log->tanggal->format('d') }}</span>
                    <span class="pk-log-month">{{ $log->tanggal->translatedFormat('M Y') }}</span>
                </div>
                <div class="pk-log-body">
                    <div class="pk-log-meta">
                        @if (isset($tampilkanNama))<span class="fw-semibold text-body">{{ $log->mahasiswa->nama_lengkap }}</span>@endif
                        <x-status :value="$log->status" />
                    </div>
                    <h3 class="pk-log-title">{{ $log->judul_kegiatan }}</h3>
                    <p class="pk-log-text">{!! nl2br(e($log->deskripsi_kegiatan)) !!}</p>
                    @if ($log->hasil_kegiatan || $log->kendala)
                        <dl class="pk-log-extra">
                            @if ($log->hasil_kegiatan)<div><dt>Hasil</dt><dd>{{ $log->hasil_kegiatan }}</dd></div>@endif
                            @if ($log->kendala)<div><dt>Kendala</dt><dd>{{ $log->kendala }}</dd></div>@endif
                        </dl>
                    @endif
                    @if ($log->catatan_dosen)
                        <div class="pk-feedback mb-3"><span class="fw-semibold d-block mb-1">Catatan dosen pembimbing</span>{!! nl2br(e($log->catatan_dosen)) !!}</div>
                    @endif

                    @if ($mode === 'mahasiswa' && $log->status !== 'disetujui' && ($bisaTambah ?? false))
                        <div class="pk-log-actions">
                            <a href="{{ route('mahasiswa.logbook.edit', $log) }}" class="btn btn-sm {{ $log->status === 'revisi' ? 'btn-primary' : 'btn-outline-primary' }}">{{ $log->status === 'revisi' ? 'Perbaiki logbook' : 'Ubah' }}</a>
                            <form method="POST" action="{{ route('mahasiswa.logbook.destroy', $log) }}" data-confirm="Hapus logbook tanggal {{ $log->tanggal->translatedFormat('d F Y') }}?">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                        </div>
                    @endif

                    @if ($mode === 'pembimbing' && $log->status === 'menunggu')
                        <form method="POST" action="{{ route('pembimbing.logbook.verifikasi', $log) }}" class="pk-verify">@csrf @method('PATCH')
                            <textarea name="catatan_dosen" rows="1" class="form-control form-control-sm" maxlength="1000" placeholder="Catatan untuk mahasiswa (wajib jika minta revisi)" aria-label="Catatan dosen"></textarea>
                            <button class="btn btn-sm btn-primary" type="submit" name="status" value="disetujui">Setujui</button>
                            <button class="btn btn-sm btn-outline-danger" type="submit" name="status" value="revisi" data-perlu-catatan="catatan_dosen">Minta revisi</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif
