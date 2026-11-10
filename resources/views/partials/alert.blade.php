@foreach (['sukses' => 'success', 'gagal' => 'danger', 'info' => 'info'] as $kunci => $warna)
    @if (session($kunci))
        <div class="alert alert-{{ $warna }} alert-dismissible fade show pk-alert" role="alert">
            {{ session($kunci) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger pk-alert" role="alert">
        <div class="fw-semibold mb-1">Ada isian yang perlu diperbaiki:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif
