@extends('layouts.app')
@section('judul', 'Notifikasi')

@section('content')
    <section class="pk-panel" style="max-width: 860px">
        <div class="pk-panel-head">
            <h2 class="pk-panel-title">Semua notifikasi</h2>
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">Tandai semua dibaca</button></form>
        </div>
        @forelse ($notifikasi as $n)
            <a href="{{ route('notifikasi.buka', $n) }}" class="pk-notif-row {{ $n->is_read ? '' : 'is-unread' }}">
                <span class="fw-semibold">{{ $n->judul }}</span>
                <span class="text-secondary">{{ $n->pesan }}</span>
                <span class="small text-secondary">{{ $n->created_at->translatedFormat('d M Y H:i') }}, {{ $n->created_at->diffForHumans() }}</span>
            </a>
        @empty
            <div class="pk-empty"><i class="bi bi-bell" aria-hidden="true"></i><h3>Belum ada notifikasi</h3><p>Perubahan status, hasil verifikasi, dan catatan pembimbing akan muncul di sini.</p></div>
        @endforelse
        <div class="mt-3">{{ $notifikasi->links() }}</div>
    </section>
@endsection
