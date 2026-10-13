<?php

/*
 | Daftar menu yang bisa di-assign ke pengguna. Urutan di sini = urutan di sidebar.
 | key   = dipakai di middleware: menu:key
 | route = halaman tujuan (index) menu tersebut
 | group = judul grup di sidebar dan di form pengguna
 | label = nama lengkap (form pengguna)
 | nav   = nama pendek di sidebar (opsional, kalau kosong memakai label)
 | Menu "Pengguna" sengaja tidak ada di sini: hanya superadmin yang bisa membukanya.
 */
return [
    // Utama
     // 'halaman'       => ['label' => 'Kelola Halaman',        'route' => 'admin.pages.index',         'group' => 'Utama'],
    // 'berita'        => ['label' => 'Berita Carousel',       'route' => 'admin.berita.index',        'group' => 'Utama'],
    // 'akuntabilitas' => ['label' => 'Dokumen Akuntabilitas', 'route' => 'admin.akuntabilitas.index', 'group' => 'Utama'],
    'armada'        => ['label' => 'Data Armada',           'route' => 'admin.armada.index',        'group' => 'Utama'],
    'rute-angkut'   => ['label' => 'Rute Pengangkutan',     'route' => 'admin.rute-angkut.index',   'group' => 'Utama'],
    'gatepass'      => ['label' => 'TPA (Gatepass)',        'route' => 'admin.gatepass.index',      'group' => 'Utama', 'nav' => 'TPA'],
    'tarif'         => ['label' => 'Tarif',                 'route' => 'admin.tarif.index',         'group' => 'Utama'],
    'lokasi'        => ['label' => 'Lokasi Angkut',         'route' => 'admin.lokasi.index',        'group' => 'Utama'],
    'invoice'       => ['label' => 'Retribusi (Invoice)',   'route' => 'admin.invoice.index',       'group' => 'Utama', 'nav' => 'Retribusi'],

    // Penjadwalan Armada
    'penugasan'     => ['label' => 'Jadwal Harian',         'route' => 'admin.penugasan-harian.index', 'group' => 'Penjadwalan Armada'],
    'kerusakan'     => ['label' => 'Armada Rusak',          'route' => 'admin.kerusakan.index',        'group' => 'Penjadwalan Armada'],
    'checkin'       => ['label' => 'Check-in Lokasi',       'route' => 'admin.checkin.index',          'group' => 'Penjadwalan Armada'],

    // Wajib Retribusi
    'object-retribusi'          => ['label' => 'Data Object Retribusi',   'route' => 'admin.object-retribusi.index',          'group' => 'Wajib Retribusi'],
    'kategori-object-retribusi' => ['label' => 'Kategori Object Retribusi', 'route' => 'admin.kategori-object-retribusi.index', 'group' => 'Wajib Retribusi'],
    'pungutan'                  => ['label' => 'Pungutan',                'route' => 'admin.pungutan.index',                  'group' => 'Wajib Retribusi'],

    // Laporan Industri
   'industri'      => ['label' => 'Data Industri',         'route' => 'admin.industri.index',      'group' => 'Laporan Industri', 'nav' => 'Data Industri'],
'neraca-sampah' => ['label' => 'Neraca Sampah Industri', 'route' => 'admin.neraca-sampah.index', 'group' => 'Laporan Industri', 'nav' => 'Neraca Sampah'],

    // Kepegawaian
    'pegawai'       => ['label' => 'Data Pegawai',          'route' => 'admin.pegawai.index',       'group' => 'Kepegawaian'],
    'jabatan'       => ['label' => 'Jabatan',               'route' => 'admin.jabatan.index',       'group' => 'Kepegawaian'],
];