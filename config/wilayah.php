<?php

/**
 * Data wilayah Kabupaten Majalengka.
 * Dipindahkan dari WasteTripController agar bisa dipakai bersama oleh
 * LokasiAngkutController, RuteAngkutController, dan view.
 *
 * Pemakaian:
 *   config('wilayah.kecamatan')           -> daftar kecamatan
 *   config('wilayah.desa')                -> [kecamatan => [desa, ...]]
 *   config('wilayah.desa.Maja')           -> desa di kecamatan Maja
 *
 * Catatan: nama desa bisa sama di kecamatan berbeda (mis. "Banjaran" ada di
 * Maja dan Sumberjaya), jadi selalu kenali desa lewat pasangan kecamatan + desa.
 */
return [

    'kecamatan' => [
        'Argapura', 'Banjaran', 'Bantarujeg', 'Cigasong', 'Cikijing',
        'Cingambul', 'Dawuan', 'Jatitujuh', 'Jatiwangi', 'Kadipaten',
        'Kasokandel', 'Kertajati', 'Lemahsugih', 'Leuwimunding', 'Ligung',
        'Maja', 'Majalengka', 'Malausma', 'Palasah', 'Panyingkiran',
        'Rajagaluh', 'Sindang', 'Sindangwangi', 'Sukahaji', 'Sumberjaya',
        'Talaga',
    ],

    'desa' => [
        'Argapura' => [
            'Argalingga', 'Argamukti', 'Cibunut', 'Cikaracak', 'Gunungwangi',
            'Haurseah', 'Heubeulisuk', 'Mekarwangi', 'Sadasari', 'Sagara',
            'Sukadana', 'Sukasari Kaler', 'Sukasari Kidul', 'Tejamulya',
        ],
        'Banjaran' => [
            'Banjaran', 'Cimeong', 'Darmalarang', 'Genteng', 'Girimulya',
            'Hegarmanah', 'Kagok', 'Kareo', 'Panyindangan', 'Sangiang',
            'Sindangpala', 'Sunia', 'Suniabaru',
        ],
        'Bantarujeg' => [
            'Babakansari', 'Bantarujeg', 'Cikidang', 'Cimangguhilir', 'Cinambo',
            'Cipeundeuy', 'Gununglarang', 'Haurgeulis', 'Salawangi', 'Silihwangi',
            'Sindanghurip', 'Sukamenak', 'Wadowetan',
        ],
        'Cigasong' => [
            'Baribis', 'Batujaya', 'Karayunan', 'Kawunghilir', 'Kutamanggu',
            'Tajur', 'Tenjolayar', 'Cicenang', 'Cigasong', 'Simpeureum',
        ],
        'Cikijing' => [
            'Bagjasari', 'Banjaransari', 'Cidulang', 'Cikijing', 'Cilancang',
            'Cipulus', 'Cisoka', 'Jagasari', 'Kancana', 'Kasturi',
            'Sindang', 'Sindangpanji', 'Sukamukti', 'Sukasari', 'Sunalari',
        ],
        'Cingambul' => [
            'Cidadap', 'Cikondang', 'Cimanggugirang', 'Cingambul', 'Cintaasih',
            'Ciranjeng', 'Kondangmekar', 'Maniis', 'Muktisari', 'Nagarakembang',
            'Rawa', 'Sedaraja', 'Wangkelang',
        ],
        'Dawuan' => [
            'Balida', 'Baturuyuk', 'Bojongcideres', 'Dawuan', 'Gandu',
            'Genteng', 'Karanganyar', 'Mandapa', 'Pasirmalati', 'Salawana',
            'Sinarjati',
        ],
        'Jatitujuh' => [
            'Babajurang', 'Biyawak', 'Jatiraga', 'Jatitengah', 'Jatitujuh',
            'Pangkalanpari', 'Panongan', 'Panyingkiran', 'Pasindangan', 'Pilangsari',
            'Putridalem', 'Randegan Kulon', 'Randegan Wetan', 'Sumber Kulon',
            'Sumber Wetan',
        ],
        'Jatiwangi' => [
            'Andir', 'Burujul Kulon', 'Burujul Wetan', 'Cibentar', 'Ciborelang',
            'Cicadas', 'Jatisura', 'Jatiwangi', 'Leuweunggede', 'Loji',
            'Mekarsari', 'Pinangraja', 'Sukaraja Kulon', 'Sukaraja Wetan',
            'Surawangi', 'Sutawangi',
        ],
        'Kadipaten' => [
            'Babakan Anyar', 'Cipaku', 'Heuleut', 'Kadipaten', 'Karangsambung',
            'Liangjulang', 'Pagandon',
        ],
        'Kasokandel' => [
            'Gandasari', 'Girimukti', 'Gunungsari', 'Jatimulya', 'Jatisawit',
            'Kasokandel', 'Leuwikidang', 'Ranji Kulon', 'Ranji Wetan', 'Wanajaya',
        ],
        'Kertajati' => [
            'Babakan', 'Bantarjati', 'Kertajati', 'Kertasari', 'Kertawinangun',
            'Mekarjaya', 'Mekarmulya', 'Pakubeureum', 'Palasah', 'Pasiripis',
            'Sahbandar', 'Sukakerta', 'Sukamulya', 'Sukawana',
        ],
        'Lemahsugih' => [
            'Bangbayang', 'Borogojol', 'Cibulan', 'Cigaleuh', 'Cipasung',
            'Kalapadua', 'Kepuh', 'Lemah Putih', 'Margajaya', 'Mekar Wangi',
            'Mekarmulya', 'Padarek', 'Sadawangi', 'Sinargalih', 'Sukajadi',
            'Sukamaju', 'Dayeuhwangi', 'Lemahsugih', 'Cisalak',
        ],
        'Leuwimunding' => [
            'Ciparay', 'Heuleut', 'Karangasem', 'Lame', 'Leuwikujang',
            'Leuwimunding', 'Mindi', 'Mirat', 'Nanggerang', 'Parakan',
            'Parungjaya', 'Patuanan', 'Rajawangi', 'Tanjungsari',
        ],
        'Ligung' => [
            'Ampel', 'Bantarwaru', 'Beber', 'Beusi', 'Buntu', 'Cibogor',
            'Gandawesi', 'Kedungkancana', 'Kedungsari', 'Kertasari', 'Kodasari',
            'Leuweunghapit', 'Leuwiliang Baru', 'Ligung', 'Ligung Lor',
            'Majasari', 'Sukawera', 'Tegalaren', 'Wanasalam',
        ],
        'Maja' => [
            'Anggrawati', 'Banjaran', 'Cengal', 'Cicalung', 'Cieurih', 'Cihaur',
            'Cipicung', 'Maja Selatan', 'Maja Utara', 'Nunuk Baru',
            'Kartabasuki', 'Malongpong', 'Paniis', 'Pasanggrahan', 'Pageraji',
            'Sindangkerta', 'Tegalsari', 'Wanahayu',
        ],
        'Majalengka' => [
            'Cibodas', 'Kawunggirang', 'Kulur', 'Sidamukti',
            'Babakan Jawa', 'Cicurug', 'Cijati', 'Cikasarung',
            'Majalengka Kulon', 'Majalengka Wetan', 'Munjul', 'Sindangkasih',
            'Tarikolot', 'Tonjong',
        ],
        'Malausma' => [
            'Banyusari', 'Buninagara', 'Cimuncang', 'Ciranca', 'Girimukti',
            'Jagamulya', 'Kramat Jaya', 'Lebakwangi', 'Malausma', 'Sukadana',
            'Werasari',
        ],
        'Palasah' => [
            'Buniwangi', 'Cisambeng', 'Enggalwangi', 'Majasuka', 'Palasah',
            'Pasir', 'Sindanghaji', 'Sindangwasa', 'Tarikolot', 'Trajaya',
            'Karamat', 'Waringin', 'Weragati',
        ],
        'Panyingkiran' => [
            'Bantrangsana', 'Bonang', 'Cijurey', 'Jatipamor', 'Jatiserang',
            'Karyamukti', 'Leuwiseeng', 'Pasirmuncang', 'Panyingkiran',
        ],
        'Rajagaluh' => [
            'Babakankareo', 'Cipinang', 'Cisetu', 'Kumbung', 'Pajajar',
            'Payung', 'Rajagaluh Kidul', 'Rajagaluh Lor', 'Rajagaluh',
            'Sadomas', 'Sindangpano', 'Singawada', 'Teja',
        ],
        'Sindang' => [
            'Bayureja', 'Garawastu', 'Gunungkuning', 'Indrakila', 'Pasirayu',
            'Sangkanhurip', 'Sindang',
        ],
        'Sindangwangi' => [
            'Balagedog', 'Bantar Agung', 'Buahkapas', 'Jerukleueut',
            'Lengkongkulon', 'Lengkongwetan', 'Leuwilaja', 'Padaherang',
            'Sindangwangi', 'Ujungberung',
        ],
        'Sukahaji' => [
            'Babakan Manjeti', 'Candrajaya', 'Cikalong', 'Cikeusik', 'Cikoneng',
            'Ciomas', 'Jayi', 'Nanggewer', 'Padahanten', 'Palabuan',
            'Salagedang', 'Sukahaji', 'Tanjungsari',
        ],
        'Sumberjaya' => [
            'Banjaran', 'Bongas Kulon', 'Bongas Wetan', 'Rancaputat', 'Cidenok',
            'Garawangi', 'Gelok Mulya', 'Lojikobong', 'Pancaksuji',
            'Panjalin Kidul', 'Panjalin Lor', 'Paningkiran', 'Parapatan',
            'Sepat', 'Sumberjaya',
        ],
        'Talaga' => [
            'Argasari', 'Campaga', 'Cibeureum', 'Cicanir', 'Cikeusal',
            'Ganeas', 'Gunungmanik', 'Jatipamor', 'Kertahayu', 'Lampuyang',
            'Margamukti', 'Mekarhurip', 'Mekarraharja', 'Salado', 'Sukaperna',
            'Talaga Kulon', 'Talaga Wetan',
        ],
    ],

];