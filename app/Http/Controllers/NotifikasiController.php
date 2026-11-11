<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = $request->user()->notifikasi()->paginate(20);

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function buka(Request $request, Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403);
        $notifikasi->update(['is_read' => true, 'read_at' => $notifikasi->read_at ?? now()]);

        return redirect()->to($notifikasi->tautan());
    }

    public function bacaSemua(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('sukses', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
