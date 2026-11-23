<?php

namespace App\Support;

use App\Models\SuratPermohonanKpi;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;
use ZipArchive;

class SuratPermohonanGenerator
{
    public function unduh(SuratPermohonanKpi $surat)
    {
        abort_unless(class_exists(ZipArchive::class), 500, 'Fitur dokumen Word memerlukan ekstensi PHP zip.');

        $sumber = resource_path('templates/surat-permohonan-kpi.docx');
        abort_unless(is_file($sumber), 500, 'Template surat permohonan belum tersedia.');

        $direktori = storage_path('app/tmp');
        if (! is_dir($direktori) && ! mkdir($direktori, 0775, true) && ! is_dir($direktori)) {
            abort(500, 'Folder sementara untuk surat tidak dapat dibuat.');
        }
        $tujuan = $direktori.'/surat-permohonan-'.Str::uuid().'.docx';
        $asalZip = new ZipArchive;
        $hasilZip = new ZipArchive;
        abort_unless($asalZip->open($sumber) === true, 500, 'Template surat tidak dapat dibuka.');
        abort_unless($hasilZip->open($tujuan, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Surat tidak dapat disiapkan.');

        $xml = $asalZip->getFromName('word/document.xml');
        abort_unless($xml !== false, 500, 'Isi template surat tidak ditemukan.');
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        abort_unless($dom->loadXML($xml, LIBXML_NONET), 500, 'Isi template surat tidak dapat dibaca.');
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        foreach ($xpath->query('//w:body/w:p') as $paragraph) {
            $teks = $paragraph->textContent;

            $this->ganti($xpath, $paragraph, '{Nomor}', (string) $surat->nomor_urut);
            $this->ganti($xpath, $paragraph, 'UN55.F4/KM.22', $surat->kode_nomor);
            $this->ganti($xpath, $paragraph, '{Tahun}', (string) $surat->tahun_nomor);
            $this->ganti($xpath, $paragraph, '{Nama Instansi}', $surat->nama_instansi);
            $this->ganti($xpath, $paragraph, 'Pimpinan', $surat->penerima);
            $this->ganti($xpath, $paragraph, 'Pangkep', $surat->kota_instansi);
            $this->ganti($xpath, $paragraph, 'Wakil Dekan III', $surat->jabatan_penandatangan);

            if (str_starts_with($teks, 'Nama:')) {
                $this->ganti($xpath, $paragraph, '{Nama}', $surat->nama_penandatangan);
            } elseif (str_starts_with($teks, 'NIP:')) {
                $this->ganti($xpath, $paragraph, '{NIP}', $surat->nip_penandatangan);
            } elseif (str_starts_with($teks, 'Pangkat/Golongan:')) {
                $this->ganti($xpath, $paragraph, '{Pangkat/Gol}', $surat->pangkat_golongan);
            } elseif (str_starts_with($teks, 'Jabatan:')) {
                $this->ganti($xpath, $paragraph, '{Jabatan}', $surat->jabatan_penandatangan);
            }

            $this->ganti($xpath, $paragraph, 'Muh. Fahmi Rustan, S.Kom.,M.T.', $surat->nama_penandatangan);
            $this->ganti($xpath, $paragraph, '19911227 201903 1 010', $surat->nip_penandatangan);

            if (str_contains($teks, 's/d')) {
                $this->ganti($xpath, $paragraph, '{Tanggal-Bulan-Tahun}', $surat->tanggal_mulai->locale('id')->translatedFormat('j F Y'));
                $this->ganti($xpath, $paragraph, '{Tanggal-Bulan-Tahun}', $surat->tanggal_selesai->locale('id')->translatedFormat('j F Y'));
            } elseif (str_contains($teks, 'Majene')) {
                $this->ganti($xpath, $paragraph, 'Majene', $surat->kota_surat);
                $this->ganti($xpath, $paragraph, '{Tanggal-Bulan-Tahun}', $surat->tanggal_surat->locale('id')->translatedFormat('j F Y'));
            }
        }

        $this->isiDaftarAnggota($xpath, $surat);
        $xmlBaru = $dom->saveXML();
        abort_unless($xmlBaru !== false, 500, 'Isi surat gagal dibuat.');

        for ($i = 0; $i < $asalZip->numFiles; $i++) {
            $nama = $asalZip->getNameIndex($i);
            $isi = $nama === 'word/document.xml' ? $xmlBaru : $asalZip->getFromIndex($i);
            if ($isi !== false) {
                $hasilZip->addFromString($nama, $isi);
            }
        }
        $asalZip->close();
        $hasilZip->close();

        return response()->download($tujuan, 'Surat_Permohonan_KPI_'.$surat->nomor_urut.'.docx')->deleteFileAfterSend(true);
    }

    private function isiDaftarAnggota(DOMXPath $xpath, SuratPermohonanKpi $surat): void
    {
        $baris = iterator_to_array($xpath->query('//w:body/w:tbl/w:tr'), false);
        $anggota = array_values($surat->anggota ?? []);
        $programStudi = $surat->pengajuan->mahasiswa->program_studi;

        for ($i = 1; $i < count($baris); $i++) {
            $row = $baris[$i];
            if (! isset($anggota[$i - 1])) {
                $row->parentNode->removeChild($row);
                continue;
            }

            $sel = $xpath->query('./w:tc', $row);
            $nilai = [(string) $i, $anggota[$i - 1]['nama'], $anggota[$i - 1]['nim'], $programStudi];
            foreach ($nilai as $kolom => $teks) {
                $this->isiSel($xpath, $sel->item($kolom), $teks);
            }
        }
    }

    private function isiSel(DOMXPath $xpath, ?\DOMNode $sel, string $teks): void
    {
        if (! $sel) {
            return;
        }

        $nodes = $xpath->query('.//w:t', $sel);
        if ($nodes->length === 0) {
            $paragraf = $xpath->query('.//w:p', $sel)->item(0);
            if (! $paragraf) {
                return;
            }
            $run = $paragraf->appendChild($paragraf->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r'));
            $node = $run->appendChild($paragraf->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t'));
            $nodes = $xpath->query('.//w:t', $sel);
        }

        $nodes->item(0)->nodeValue = $teks;
        for ($i = 1; $i < $nodes->length; $i++) {
            $nodes->item($i)->nodeValue = '';
        }
    }

    private function ganti(DOMXPath $xpath, DOMElement $scope, string $cari, string $ganti): void
    {
        $nodes = $xpath->query('.//w:t', $scope);
        if ($nodes->length === 0) {
            return;
        }

        $teks = [];
        foreach ($nodes as $node) {
            $teks[] = $node->textContent;
        }
        $gabung = implode('', $teks);
        $posisi = strpos($gabung, $cari);
        if ($posisi === false) {
            return;
        }

        $awalNode = $akhirNode = null;
        $offset = 0;
        $panjangToken = strlen($cari);
        foreach ($nodes as $index => $node) {
            $akhir = $offset + strlen($teks[$index]);
            if ($awalNode === null && $posisi < $akhir) {
                $awalNode = $index;
            }
            if ($posisi + $panjangToken <= $akhir) {
                $akhirNode = $index;
                break;
            }
            $offset = $akhir;
        }

        if ($awalNode === null || $akhirNode === null) {
            return;
        }

        $awalOffset = array_sum(array_map('strlen', array_slice($teks, 0, $awalNode)));
        $akhirOffset = array_sum(array_map('strlen', array_slice($teks, 0, $akhirNode)));
        $awalLokal = $posisi - $awalOffset;
        $akhirLokal = $posisi + $panjangToken - $akhirOffset;

        if ($awalNode === $akhirNode) {
            $nodes->item($awalNode)->nodeValue = substr($teks[$awalNode], 0, $awalLokal).$ganti.substr($teks[$awalNode], $akhirLokal);
            return;
        }

        $nodes->item($awalNode)->nodeValue = substr($teks[$awalNode], 0, $awalLokal).$ganti;
        for ($i = $awalNode + 1; $i < $akhirNode; $i++) {
            $nodes->item($i)->nodeValue = '';
        }
        $nodes->item($akhirNode)->nodeValue = substr($teks[$akhirNode], $akhirLokal);
    }
}
