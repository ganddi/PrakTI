<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') | PrakTI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #1F3A5F; --line: #C9D3E0; }
        * { box-sizing: border-box; }
        body { font-family: "Switzer", "Segoe UI", Arial, sans-serif; color: #1E2632; margin: 0; background: #EEF1F5; font-size: 11pt; }
        .toolbar { position: sticky; top: 0; display: flex; gap: .5rem; justify-content: center; padding: .75rem; background: #fff; border-bottom: 1px solid var(--line); }
        .toolbar button, .toolbar a { font: inherit; font-weight: 600; padding: .5rem 1rem; border-radius: 8px; border: 1px solid var(--ink); background: var(--ink); color: #fff; cursor: pointer; text-decoration: none; }
        .toolbar a { background: #fff; color: var(--ink); }
        .halaman { background: #fff; width: 210mm; min-height: 297mm; margin: 1.5rem auto; padding: 18mm 18mm 20mm; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .kop { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid var(--ink); padding-bottom: 8px; margin-bottom: 16px; }
        .kop strong { font-size: 13pt; color: var(--ink); }
        .kop-resmi { display: grid; grid-template-columns: 68px 1fr; align-items: center; gap: 12px; border-bottom: 4px double #111; padding-bottom: 8px; margin-bottom: 16px; color: #111; text-align: center; line-height: 1.25; }
        .kop-resmi img { width: 68px; height: 68px; object-fit: contain; }
        .kop-resmi-kementerian { font-size: 8.5pt; }
        .kop-resmi-universitas { font-size: 13pt; font-weight: 700; }
        .kop-resmi-fakultas { font-size: 11pt; font-weight: 700; }
        .kop-resmi-kontak { margin-top: 3px; font-size: 7.5pt; }
        h1 { font-size: 14pt; text-align: center; margin: 0 0 14px; letter-spacing: .02em; }
        table { width: 100%; border-collapse: collapse; }
        .identitas td { padding: 2px 0; vertical-align: top; }
        .identitas td:first-child { width: 34%; color: #4A5565; }
        .tabel { margin-top: 14px; }
        .tabel th, .tabel td { border: 1px solid var(--line); padding: 6px 8px; vertical-align: top; text-align: left; }
        .tabel th { background: #F1F4F8; font-weight: 600; }
        .tabel .angka { text-align: right; white-space: nowrap; }
        .surat-document-info { margin-bottom: 1.25rem; }
        .surat-recipient { margin-bottom: 1rem; }
        .surat-paragraph { margin: 0 0 .8rem; text-align: justify; }
        .surat-table { margin: .75rem 0 1rem; }
        .surat-signature { width: 52%; margin: 1.5rem 0 0 auto; text-align: center; }
        .surat-signature-space { height: 64px; }
        .surat-signature-name { font-weight: 600; text-decoration: underline; }
        .ttd { display: grid; grid-template-columns: repeat(var(--kolom, 2), 1fr); gap: 16px; margin-top: 36px; text-align: center; }
        .ttd .ruang { height: 64px; }
        .ttd .nama { font-weight: 600; text-decoration: underline; }
        .catatan { margin-top: 18px; font-size: 9.5pt; color: #4A5565; }
        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .halaman { margin: 0; box-shadow: none; width: auto; min-height: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button type="button" onclick="window.print()">Cetak</button>
    @yield('aksi-cetak')
    @hasSection('aksi-kembali')
        @yield('aksi-kembali')
    @else
        <a href="javascript:history.back()">Kembali</a>
    @endif
</div>
<div class="halaman">
    @hasSection('kop-resmi')
        @yield('kop-resmi')
    @else
        <div class="kop">
            <div><strong>PrakTI</strong><br><span>{{ config('prakti.nama_prodi') }}</span></div>
            <span>Dicetak {{ now()->translatedFormat('d F Y') }}</span>
        </div>
    @endif
    @yield('content')
</div>
</body>
</html>
