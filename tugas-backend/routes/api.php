<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

$alat = [
    ['id' => 1, 'nama' => 'Laptop Asus',    'kategori' => 'Laptop',         'status' => 'tersedia'],
    ['id' => 2, 'nama' => 'Kamera Canon',   'kategori' => 'Kamera',         'status' => 'dipinjam'],
    ['id' => 3, 'nama' => 'Arduino Uno',    'kategori' => 'Mikrokontroler', 'status' => 'tersedia'],
    ['id' => 4, 'nama' => 'Raspberry Pi 4', 'kategori' => 'Mikrokontroler', 'status' => 'tersedia'],
    ['id' => 5, 'nama' => 'Tripod Kamera',  'kategori' => 'Aksesoris',      'status' => 'tersedia'],
];

Route::get('/alat', function () use ($alat) {
    return response()->json($alat);
});

Route::post('/peminjaman', function (Request $request) use ($alat) {
    $data = $request->validate([
        'alat_id'       => 'required|integer',
        'nama_peminjam' => 'required|string',
    ]);

    $dipilih = collect($alat)->firstWhere('id', $data['alat_id']);

    if (!$dipilih) {
        return response()->json(['pesan' => 'Alat tidak ditemukan'], 404);
    }
    if ($dipilih['status'] !== 'tersedia') {
        return response()->json(['pesan' => 'Alat sedang dipinjam'], 409);
    }

    $cache  = Cache::store('file');
    $daftar = $cache->get('peminjaman', []);

    $baru = [
        'id'            => count($daftar) + 1,
        'alat_id'       => $data['alat_id'],
        'nama_peminjam' => $data['nama_peminjam'],
        'status'        => 'menunggu persetujuan',
    ];

    $daftar[] = $baru;
    $cache->forever('peminjaman', $daftar);

    return response()->json($baru, 201);
});