<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Poli;
use App\Models\JadwalPeriksa;
use App\Models\DaftarPoli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DaftarPoliController extends Controller
{
    public function create()
{
    $pasien = User::findOrFail(Auth::id());

    $poli = Poli::orderBy('nama_poli')->get();

    $jadwals = JadwalPeriksa::with(['dokter', 'dokter.poli'])
        ->get()
        ->map(function ($jadwal) {
            $jadwal->id_poli = $jadwal->dokter->id_poli ?? null;
            $jadwal->nama_dokter = $jadwal->dokter->nama ?? '-';
            $jadwal->nama_poli = $jadwal->dokter->poli->nama_poli ?? '-';
            $jadwal->kode_poli = $jadwal->dokter->poli->kode_poli ?? 'A';
            return $jadwal;
        });

    return view('pasien.daftar-poli', compact('pasien', 'poli', 'jadwals'));
}

    public function store(Request $request)
    {
        $request->validate([
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan'   => 'required|string',
        ]);

        $pasien = User::findOrFail(Auth::id());

        // cek apakah pasien masih punya antrian aktif
        $antrianAktif = DaftarPoli::where('id_pasien', $pasien->id)
            ->whereDoesntHave('periksa')
            ->exists();

        if ($antrianAktif) {
            return back()->with(
                'error',
                'Anda masih memiliki antrian aktif dan belum dapat mendaftar lagi.'
            );
        }

        DB::beginTransaction();

        try {
            // ambil jadwal + dokter + poli
            $jadwal = JadwalPeriksa::with('dokter.poli')
                ->findOrFail($request->id_jadwal);

            // hitung jumlah antrian hari ini
            $jumlah = DaftarPoli::where('id_jadwal', $request->id_jadwal)
                ->whereDate('created_at', now('Asia/Jakarta')->toDateString())
                ->lockForUpdate()
                ->count();

            // nomor berikutnya
            $nomor = $jumlah + 1;

            DaftarPoli::create([
                'id_pasien'  => $pasien->id,
                'id_jadwal'  => $request->id_jadwal,
                'keluhan'    => $request->keluhan,
                'no_antrian' => $nomor,
            ]);

            DB::commit();

            return redirect()
                ->route('pasien.dashboard')
                ->with('success', 'Berhasil mendaftar ke poli!');

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}