<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PoliController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\ObatController;
use App\Models\Poli;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Obat;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


// ================= AUTH =================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ================= ADMIN =================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', function () {
        \Carbon\Carbon::setLocale('id');

        return view('admin.dashboard', [
            'today' => \Carbon\Carbon::now()->translatedFormat('l, d F Y'),
            'totalPoli' => Poli::count(),
            'totalDokter' => Dokter::count(),
            'totalPasien' => Pasien::count(),
            'totalObat' => Obat::count(),
            'polis' => Poli::latest('updated_at')->take(5)->get(),
        ]);
    })->name('admin.dashboard');

    // CRUD
    Route::resource('polis', PoliController::class);
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class);
});


// ================= DOKTER =================
Route::middleware(['auth', 'role:dokter'])->prefix('dokters')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokters.dashboard');
    })->name('dokters.dashboard');
});


// ================= PASIEN =================
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', function () {
        return view('pasien.dashboard');
    })->name('pasien.dashboard');
});