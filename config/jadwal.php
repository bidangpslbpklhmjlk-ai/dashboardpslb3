<?php

return [
    // Senin yang ditetapkan sebagai awal "Minggu 1". Ganti kalau pola ingin digeser.
    'pekan_acuan' => env('JADWAL_PEKAN_ACUAN', '2026-11-02'),

    // Panjang siklus (2 = minggu 1 dan minggu 2 bergantian)
    'siklus' => 2,
];