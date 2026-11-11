<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') | PrakTI</title>
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
        h1 { font-size: 14pt; text-align: center; margin: 0 0 14px; letter-spacing: .02em; }
        table { width: 100%; border-collapse: collapse; }
        .identitas td { padding: 2px 0; vertical-align: top; }
        .identitas td:first-child { width: 34%; color: #4A5565; }
        .tabel { margin-top: 14px; }
        .tabel th, .tabel td { border: 1px solid var(--line); padding: 6px 8px; vertical-align: top; text-align: left; }
        .tabel th { background: #F1F4F8; font-weight: 600; }
        .tabel .angka { text-align: right; white-space: nowrap; }
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
    <button type="button" onclick="window.print()">Cetak atau simpan sebagai PDF</button>
    <a href="javascript:history.back()">Kembali</a>
</div>
<div class="halaman">
    <div class="kop">
        <div><strong>PrakTI</strong><br><span>{{ config('prakti.nama_prodi') }}</span></div>
        <span>Dicetak {{ now()->translatedFormat('d F Y') }}</span>
    </div>
    @yield('content')
</div>
</body>
</html>
