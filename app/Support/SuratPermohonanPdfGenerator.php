<?php

namespace App\Support;

use App\Models\SuratPermohonanKpi;

class SuratPermohonanPdfGenerator
{
    private const LEBAR = 595.28;
    private const TINGGI = 841.89;
    private array $perintah = [];

    public function unduh(SuratPermohonanKpi $surat)
    {
        $surat->loadMissing('pengajuan.mahasiswa');
        $this->perintah = [];
        $kiri = 52;
        $kanan = 543;

        $logoPath = public_path('images/logo-unsulbar.jpg');
        $logo = file_get_contents($logoPath);
        $logoInfo = getimagesize($logoPath);
        $this->perintah[] = 'q 62 0 0 61 '.$kiri.' 752 cm /Logo Do Q';

        $this->teks(134, 798, 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI', 8);
        $this->teks(134, 778, 'UNIVERSITAS SULAWESI BARAT', 13, true);
        $this->teks(134, 761, 'FAKULTAS TEKNIK', 11, true);
        $this->teks(134, 746, 'Alamat: Kampus Padhang-Padhang, Banggae Timur, Majene, Sulawesi Barat', 7);
        $this->teks(134, 735, 'Website: ft.unsulbar.ac.id | Instagram: ft-universitas sulawesi barat', 7);
        $this->perintah[] = "0.1 w {$kiri} 726 m {$kanan} 726 l S";
        $this->perintah[] = "0.7 w {$kiri} 723 m {$kanan} 723 l S";

        $y = 696;
        $this->teks($kiri, $y, 'Nomor: '.$surat->nomor_surat, 10);
        $y -= 15;
        $this->teks($kiri, $y, 'Lampiran: -', 10);
        $y -= 15;
        $this->teks($kiri, $y, 'Hal: Permohonan Kerja Praktek Industri', 10);

        $y -= 38;
        $this->teks($kiri, $y, 'Kepada Yth: '.$surat->penerima.' '.$surat->nama_instansi, 10);
        $y -= 14;
        $this->teks($kiri, $y, 'Di-', 10);
        $y -= 14;
        $this->teks($kiri + 24, $y, $surat->kota_instansi, 10);

        $y -= 32;
        $this->teks($kiri, $y, 'Dengan hormat yang bertanda tangan di bawah ini:', 10);
        $y -= 16;
        foreach ([
            'Nama' => $surat->nama_penandatangan,
            'NIP' => $surat->nip_penandatangan,
            'Pangkat/Golongan' => $surat->pangkat_golongan,
            'Jabatan' => $surat->jabatan_penandatangan,
        ] as $label => $value) {
            $this->teks($kiri + 18, $y, $label.': '.$value, 10);
            $y -= 14;
        }

        $y -= 12;
        $y = $this->paragraf('Memohon kepada Bapak/Ibu '.$surat->penerima.' '.$surat->nama_instansi.' agar mahasiswa (i) kami dapat diberi izin untuk melaksanakan Praktek Kerja Industri di Instansi/Perusahaan yang Bapak/Ibu Pimpin. Pelaksanaan Kerja Praktek Industri tersebut rencana akan dilaksanakan pada:', $kiri, $y, 10, 14);
        $y -= 4;
        $periode = 'Tanggal '.$surat->tanggal_mulai->locale('id')->translatedFormat('j F Y').' s/d '.$surat->tanggal_selesai->locale('id')->translatedFormat('j F Y').'. Adapun nama-nama mahasiswa yang akan mengikuti kegiatan kerja praktek industri adalah sebagai berikut:';
        $y = $this->paragraf($periode, $kiri, $y, 10, 14);
        $y -= 12;

        $anggota = array_values($surat->anggota ?? []);
        $prodi = $surat->pengajuan->mahasiswa->program_studi;
        $baris = [['NO', 'NAMA', 'NIM', 'PRODI']];
        foreach ($anggota as $i => $orang) {
            $baris[] = [(string) ($i + 1), $orang['nama'], $orang['nim'], $prodi];
        }
        $lebarKolom = [34, 185, 105, 165];
        foreach ($baris as $nomorBaris => $kolom) {
            $tinggiBaris = $nomorBaris === 0 ? 22 : 24;
            $this->perintah[] = sprintf('0.6 w %.2f %.2f %.2f %.2f re S', $kiri, $y - $tinggiBaris + 5, array_sum($lebarKolom), $tinggiBaris);
            $x = $kiri;
            foreach ($kolom as $i => $isi) {
                if ($i > 0) {
                    $this->perintah[] = sprintf('0.4 w %.2f %.2f m %.2f %.2f l S', $x, $y + 5, $x, $y - $tinggiBaris + 5);
                }
                $this->teks($x + 5, $y - 10, $isi, 8, $nomorBaris === 0);
                $x += $lebarKolom[$i];
            }
            $y -= $tinggiBaris;
        }

        $y -= 22;
        $y = $this->paragraf('Demikian Surat permohonan izin ini dibuat, atas perhatian dan kerjasamanya kami ucapkan terima kasih.', $kiri, $y, 10, 14);
        $y -= 24;
        $ttdX = 350;
        $this->teks($ttdX, $y, $surat->kota_surat.', '.$surat->tanggal_surat->locale('id')->translatedFormat('j F Y'), 10);
        $y -= 15;
        $this->teks($ttdX, $y, 'a.n. Dekan', 10);
        $y -= 15;
        $this->teks($ttdX, $y, $surat->jabatan_penandatangan.',', 10);
        $y -= 70;
        $this->teks($ttdX, $y, $surat->nama_penandatangan, 10, true);
        $this->perintah[] = sprintf('0.5 w %.2f %.2f m %.2f %.2f l S', $ttdX, $y - 2, $ttdX + min(190, strlen($surat->nama_penandatangan) * 5.1), $y - 2);
        $y -= 14;
        $this->teks($ttdX, $y, 'NIP. '.$surat->nip_penandatangan, 9);

        $konten = implode("\n", $this->perintah);
        $pdf = $this->susunPdf($konten, $logo, $logoInfo[0], $logoInfo[1]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Surat_Permohonan_KPI_'.$surat->nomor_urut.'.pdf"',
        ]);
    }

    private function paragraf(string $teks, float $x, float $y, int $ukuran, int $jarak): float
    {
        $baris = $this->bungkus($teks, 96);
        foreach ($baris as $isi) {
            $this->teks($x, $y, $isi, $ukuran);
            $y -= $jarak;
        }

        return $y;
    }

    private function bungkus(string $teks, int $batas): array
    {
        $teks = $this->enkode($teks);
        $kata = preg_split('/\s+/', $teks) ?: [];
        $baris = [];
        $saatIni = '';
        foreach ($kata as $item) {
            $uji = $saatIni === '' ? $item : $saatIni.' '.$item;
            if (strlen($uji) > $batas && $saatIni !== '') {
                $baris[] = $saatIni;
                $saatIni = $item;
            } else {
                $saatIni = $uji;
            }
        }
        if ($saatIni !== '') {
            $baris[] = $saatIni;
        }

        return $baris;
    }

    private function teks(float $x, float $y, string $teks, int $ukuran, bool $tebal = false): void
    {
        $font = $tebal ? 'F2' : 'F1';
        $this->perintah[] = sprintf('BT /%s %d Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET', $font, $ukuran, $x, $y, $this->literal($this->enkode($teks)));
    }

    private function enkode(string $teks): string
    {
        return iconv('UTF-8', 'Windows-1252//TRANSLIT', $teks) ?: $teks;
    }

    private function literal(string $teks): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $teks);
    }

    private function susunPdf(string $konten, string $logo, int $lebarLogo, int $tinggiLogo): string
    {
        $objek = [];
        $objek[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objek[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objek[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::LEBAR.' '.self::TINGGI.'] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> /XObject << /Logo 6 0 R >> >> /Contents 7 0 R >>';
        $objek[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objek[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objek[6] = '<< /Type /XObject /Subtype /Image /Width '.$lebarLogo.' /Height '.$tinggiLogo.' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '.strlen($logo).">>\nstream\n".$logo."\nendstream";
        $objek[7] = '<< /Length '.strlen($konten).">>\nstream\n".$konten."\nendstream";

        $hasil = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offset = [0];
        foreach ($objek as $nomor => $isi) {
            $offset[$nomor] = strlen($hasil);
            $hasil .= $nomor." 0 obj\n".$isi."\nendobj\n";
        }
        $xref = strlen($hasil);
        $hasil .= "xref\n0 8\n0000000000 65535 f \n";
        for ($i = 1; $i <= 7; $i++) {
            $hasil .= sprintf('%010d 00000 n ', $offset[$i])."\n";
        }
        $hasil .= "trailer\n<< /Size 8 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $hasil;
    }
}
