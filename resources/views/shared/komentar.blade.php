{{-- Utas komentar/konsultasi pada satu logbook atau progress --}}
@if ($item->komentar->isNotEmpty() || $mode !== 'admin')
    <div class="pk-thread">
        @foreach ($item->komentar as $k)
            <div class="pk-comment {{ $k->user_id === auth()->id() ? 'pk-comment--self' : '' }}">
                <div class="pk-comment-head">
                    <strong>{{ $k->user->name }}</strong>
                    <span>{{ $k->user->peran_label }}, {{ $k->created_at->diffForHumans() }}</span>
                </div>
                <div>{!! nl2br(e($k->isi)) !!}</div>
            </div>
        @endforeach

        @if ($mode !== 'admin')
            <form method="POST" action="{{ route('komentar.store', [$tipe, $item->id]) }}" class="pk-comment-form">
                @csrf
                <textarea name="isi" rows="1" class="form-control form-control-sm" required maxlength="1000"
                          aria-label="Tulis komentar"
                          placeholder="{{ $mode === 'pembimbing' ? 'Tulis arahan atau feedback' : 'Balas atau tanyakan ke pembimbing' }}"></textarea>
                <button class="btn btn-sm btn-outline-primary" type="submit">Kirim</button>
            </form>
        @endif
    </div>
@endif
