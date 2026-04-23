<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    public function index()
    {
        $dokter = Auth::user();
        
        // Nama variabel $jadwals agar sinkron dengan index.blade.php
        $jadwals = JadwalPeriksa::where('id_dokter', $dokter->id)
                    ->orderBy('hari')
                    ->get();

        return view('dokter.jadwal-periksa.index', compact('jadwals'));
    }

    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required'
        ]);

        JadwalPeriksa::create([
            'id_dokter' => Auth::id(),
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai
        ]);

        // Ditambahkan prefix 'dokter.'
        return redirect()->route('dokter.jadwal-periksa.index')
            ->with('message', 'Data Berhasil di Simpan')
            ->with('type', 'success');
    }

    public function edit($id)
    {
        // Nama variabel diubah jadi $jadwal agar sinkron dengan edit.blade.php
        $jadwal = JadwalPeriksa::findOrFail($id);
        
        return view('dokter.jadwal-periksa.edit', compact('jadwal'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required'
        ]);

        $jadwalPeriksa = JadwalPeriksa::findOrFail($id);
        $jadwalPeriksa->update([
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai
        ]);

        // Ditambahkan prefix 'dokter.'
        return redirect()->route('dokter.jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Update Data')
            ->with('type', 'success');
    }

    public function destroy(string $id)
    {
        $jadwalPeriksa = JadwalPeriksa::findOrFail($id);
        $jadwalPeriksa->delete();

        // Ditambahkan prefix 'dokter.'
        return redirect()->route('dokter.jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Hapus Data')
            ->with('type', 'success');
    }
}