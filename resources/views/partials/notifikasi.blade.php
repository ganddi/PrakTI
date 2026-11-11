@php
    $belumDibaca = \App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count();
    $terbaru = \App\Models\Notifikasi::where('user_id', auth()->id())->latest('id')->take(5)->get();
@endphp
<div class="dropdown">
    <button class="btn pk-bell" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi{{ $belumDibaca ? ', '.$belumDibaca.' belum dibaca' : '' }}">
        <i class="bi bi-bell" aria-hidden="true"></i>
        @if ($belumDibaca)<span class="pk-bell-count">{{ $belumDibaca > 9 ? '9+' : $belumDibaca }}</span>@endif
    </button>
    <div class="dropdown-menu dropdown-menu-end pk-notif-menu">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <strong>Notifikasi</strong>
            @if ($belumDibaca)
                <form method="POST" action="{{ route('notifikasi.baca-semua') }}">@csrf<button class="btn btn-link btn-sm p-0" type="submit">Tandai semua dibaca</button></form>
            @endif
        </div>
        @forelse ($terbaru as $n)
            <a class="dropdown-item pk-notif-item {{ $n->is_read ? '' : 'is-unread' }}" href="{{ route('notifikasi.buka', $n) }}">
                <span class="d-block fw-semibold text-wrap">{{ $n->judul }}</span>
                <span class="d-block small text-secondary text-truncate">{{ $n->pesan }}</span>
                <span class="d-block small text-secondary">{{ $n->created_at->diffForHumans() }}</span>
            </a>
        @empty
            <p class="px-3 py-3 mb-0 text-secondary small">Belum ada notifikasi.</p>
        @endforelse
        <a class="dropdown-item text-center small fw-semibold border-top py-2" href="{{ route('notifikasi.index') }}">Lihat semua notifikasi</a>
    </div>
</div>
