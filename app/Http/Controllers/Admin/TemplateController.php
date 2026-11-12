<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use App\Support\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Kelola template surat, logbook, laporan, dan penilaian (FR-06, FR-14, FR-17). */
class TemplateController extends Controller
{
    public function index()
    {
        return view('admin.template.index', [
            'template' => collect(Template::JENIS)->mapWithKeys(fn ($l, $j) => [$j => Template::daftar($j)]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(Template::JENIS))],
            'berkas' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
        ]);
        Template::simpan($data['jenis'], $request->file('berkas'));
        AktivitasSistem::catat('Mengunggah '.strtolower(Template::JENIS[$data['jenis']]), 'template');

        return back()->with('sukses', 'Template diunggah dan langsung tersedia untuk diunduh.');
    }

    public function destroy(string $kode)
    {
        $path = Template::pathDariKode($kode);
        abort_unless($path, 404);
        Storage::disk()->delete($path);

        return back()->with('sukses', 'Template dihapus.');
    }
}
