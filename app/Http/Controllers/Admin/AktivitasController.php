<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasSistem;
use Illuminate\Http\Request;

/** Riwayat aktivitas sistem (SDD Tabel 3.16, Bab V: Activity Logging). */
class AktivitasController extends Controller
{
    public function __invoke(Request $request)
    {
        $aktivitas = AktivitasSistem::with(['user.mahasiswa', 'user.dosen'])
            ->when($request->query('modul'), fn ($q, $m) => $q->where('modul', $m))
            ->when($request->query('q'), fn ($q, $c) => $q->where(fn ($w) => $w->where('aktivitas', 'like', "%{$c}%")->orWhere('deskripsi', 'like', "%{$c}%")
                ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$c}%"))))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.aktivitas.index', [
            'aktivitas' => $aktivitas,
            'daftarModul' => AktivitasSistem::distinct()->orderBy('modul')->pluck('modul'),
        ]);
    }
}
