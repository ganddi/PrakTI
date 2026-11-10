<?php

/*
| Pesan validasi Bahasa Indonesia untuk aturan yang dipakai di PrakTI.
| Aturan lain yang tidak tercantum akan memakai pesan bawaan (bahasa Inggris).
*/
return [
    'after' => ':Attribute harus setelah :date.',
    'after_or_equal' => ':Attribute paling cepat :date.',
    'before_or_equal' => ':Attribute paling lambat :date.',
    'between' => [
        'numeric' => ':Attribute harus di antara :min dan :max.',
    ],
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => ':Attribute harus berformat :format.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak tersedia.',
    'file' => ':Attribute harus berupa berkas.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'max' => [
        'file' => 'Ukuran :attribute maksimal :max KB.',
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'mimes' => ':Attribute harus berformat :values.',
    'min' => [
        'file' => 'Ukuran :attribute minimal :min KB.',
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'required' => ':Attribute wajib diisi.',
    'required_if' => ':Attribute wajib diisi.',
    'required_unless' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah terdaftar.',
    'uploaded' => ':Attribute gagal diunggah. Pastikan ukuran berkas tidak melebihi batas upload server.',

    'password' => [
        'letters' => ':Attribute harus mengandung minimal satu huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus mengandung minimal satu angka.',
        'symbols' => ':Attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Gunakan kata sandi lain.',
    ],

    'custom' => [
        'tanggal' => [
            'unique' => 'Logbook untuk tanggal ini sudah ada. Ubah logbook tersebut dari daftar logbook.',
            'before_or_equal' => 'Logbook tidak bisa diisi untuk tanggal yang belum terjadi atau melewati periode KPI.',
            'after_or_equal' => 'Tanggal logbook harus berada dalam periode KPI.',
        ],
        'jam_selesai' => [
            'after' => 'Jam selesai harus setelah jam mulai.',
        ],
        'pembimbing_id' => [
            'required_if' => 'Pilih dosen pembimbing sebelum menyetujui pengajuan.',
        ],
        'catatan_admin' => [
            'required_if' => 'Tuliskan alasan penolakan agar mahasiswa tahu apa yang perlu diperbaiki.',
        ],
        'catatan' => [
            'required_if' => 'Tuliskan catatan revisi agar mahasiswa tahu bagian yang perlu diperbaiki.',
        ],
        'nomor_induk' => [
            'required_unless' => 'NIM/NIDN wajib diisi untuk mahasiswa dan dosen.',
            'unique' => 'NIM/NIDN ini sudah terdaftar.',
        ],
        'email' => [
            'unique' => 'Email ini sudah terdaftar. Silakan masuk atau gunakan email lain.',
        ],
    ],

    'attributes' => [
        'name' => 'nama lengkap',
        'email' => 'email',
        'password' => 'kata sandi',
        'role' => 'peran',
        'nomor_induk' => 'NIM/NIDN',
        'no_hp' => 'nomor HP',
        'nama_instansi' => 'nama instansi',
        'alamat_instansi' => 'alamat instansi',
        'bidang_kerja' => 'bidang kerja',
        'pembimbing_lapangan' => 'pembimbing lapangan',
        'kontak_instansi' => 'kontak instansi',
        'tanggal_mulai' => 'tanggal mulai',
        'tanggal_selesai' => 'tanggal selesai',
        'deskripsi' => 'deskripsi',
        'surat_permohonan' => 'surat permohonan',
        'proposal' => 'proposal',
        'jenis' => 'jenis dokumen',
        'berkas' => 'berkas',
        'tanggal' => 'tanggal',
        'jam_mulai' => 'jam mulai',
        'jam_selesai' => 'jam selesai',
        'kegiatan' => 'uraian kegiatan',
        'lampiran' => 'lampiran',
        'judul' => 'judul progress',
        'persentase' => 'persentase',
        'isi' => 'komentar',
        'catatan' => 'catatan',
        'catatan_admin' => 'catatan',
        'keputusan' => 'keputusan',
        'pembimbing_id' => 'dosen pembimbing',
    ],
];
