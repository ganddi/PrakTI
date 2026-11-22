@extends('layouts.cetak')
@section('judul', 'Pratinjau surat permohonan KPI')
@section('aksi-cetak')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.pengajuan.surat.unduh', $pengajuan) }}">Unduh DOCX</a>
    @else
        <a href="{{ route('mahasiswa.pengajuan.surat-permohonan.unduh', $pengajuan) }}">Unduh DOCX</a>
    @endif
@endsection

@section('aksi-kembali')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.pengajuan.show', $pengajuan) }}">Kembali</a>
    @else
        <a href="{{ route('mahasiswa.pengajuan.index') }}">Kembali</a>
    @endif
@endsection

@section('kop-resmi')
    <div class="kop-resmi">
        <img src="{{ asset('images/logo-unsulbar.png') }}" alt="Lambang Universitas Sulawesi Barat">
        <div>
            <div class="kop-resmi-kementerian">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
            <div class="kop-resmi-universitas">UNIVERSITAS SULAWESI BARAT</div>
            <div class="kop-resmi-fakultas">FAKULTAS TEKNIK</div>
            <div class="kop-resmi-kontak">Alamat: Kampus Padhang-Padhang, Banggae Timur, Majene, Sulawesi Barat<br>Website: ft.unsulbar.ac.id · Instagram: ft-universitas sulawesi barat</div>
        </div>
    </div>
@endsection

@section('content')
    <div class="surat-document-info">
        <div>Nomor: {{ $surat->nomor_surat }}</div>
        <div>Lampiran: -</div>
        <div>Hal: Permohonan Kerja Praktek Industri</div>
    </div>

    <div class="surat-recipient">
        <div>Kepada Yth: {{ $surat->penerima }} {{ $surat->nama_instansi }}</div>
        <div>Di-</div>
        <div>{{ $surat->kota_instansi }}</div>
    </div>

    <p>Dengan hormat yang bertanda tangan di bawah ini:</p>
    <table class="identitas">
        <tr><td>Nama</td><td>: {{ $surat->nama_penandatangan }}</td></tr>
        <tr><td>NIP</td><td>: {{ $surat->nip_penandatangan }}</td></tr>
        <tr><td>Pangkat/Golongan</td><td>: {{ $surat->pangkat_golongan }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $surat->jabatan_penandatangan }}</td></tr>
    </table>

    <p class="surat-paragraph">Memohon kepada Bapak/Ibu {{ $surat->penerima }} {{ $surat->nama_instansi }} agar mahasiswa (i) kami dapat diberi izin untuk melaksanakan Praktek Kerja Industri di Instansi/Perusahaan yang Bapak/Ibu Pimpin. Pelaksanaan Kerja Praktek Industri tersebut rencana akan dilaksanakan pada:</p>
    <p class="surat-paragraph">Tanggal {{ $surat->tanggal_mulai->locale('id')->translatedFormat('j F Y') }} s/d {{ $surat->tanggal_selesai->locale('id')->translatedFormat('j F Y') }}. Adapun nama-nama mahasiswa yang akan mengikuti kegiatan kerja praktek industri adalah sebagai berikut:</p>

    <table class="tabel surat-table">
        <thead><tr><th style="width: 8%">No.</th><th>Nama</th><th>NIM</th><th>Prodi</th></tr></thead>
        <tbody>
            @foreach ($surat->anggota as $i => $anggota)
                <tr><td>{{ $i + 1 }}</td><td>{{ $anggota['nama'] }}</td><td>{{ $anggota['nim'] }}</td><td>{{ $pengajuan->mahasiswa->program_studi }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <p class="surat-paragraph">Demikian Surat permohonan izin ini dibuat, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
    <div class="surat-signature">
        <div>{{ $surat->kota_surat }}, {{ $surat->tanggal_surat->locale('id')->translatedFormat('j F Y') }}</div>
        <div>a.n. Dekan</div>
        <div>{{ $surat->jabatan_penandatangan }},</div>
        <div class="surat-signature-space"></div>
        <div class="surat-signature-name">{{ $surat->nama_penandatangan }}</div>
        <div>NIP. {{ $surat->nip_penandatangan }}</div>
    </div>
@endsection
