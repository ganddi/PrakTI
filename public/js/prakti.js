// PrakTI — interaksi kecil di sisi browser (tanpa build step)
document.addEventListener('DOMContentLoaded', () => {
  // Konfirmasi sebelum aksi penting: <form data-confirm="Yakin?">
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
  });

  // Buka tab sesuai hash URL: #tab-progress, #progress-3, #logbook-12, #tab-dokumen
  const hash = window.location.hash.slice(1);
  if (hash && window.bootstrap) {
    const namaTab = hash.startsWith('tab-') ? hash.slice(4) : hash.split('-')[0];
    const tombolTab = document.querySelector(`[data-bs-target="#tab-${namaTab}"]`);
    if (tombolTab) bootstrap.Tab.getOrCreateInstance(tombolTab).show();

    const sasaran = !hash.startsWith('tab-') && document.getElementById(hash);
    if (sasaran) setTimeout(() => sasaran.scrollIntoView({ block: 'center' }), 150);
  }

  // Tombol "Minta revisi" wajib disertai catatan
  document.querySelectorAll('[data-perlu-catatan]').forEach((tombol) => {
    tombol.addEventListener('click', (event) => {
      const catatan = tombol.form.querySelector('textarea[name="catatan"]');
      if (catatan && !catatan.value.trim()) {
        event.preventDefault();
        catatan.classList.add('is-invalid');
        catatan.placeholder = 'Tuliskan bagian yang perlu direvisi sebelum mengirim.';
        catatan.focus();
      }
    });
  });

  // Form verifikasi pengajuan (admin): tampilkan pilihan pembimbing hanya saat menyetujui
  const formVerifikasi = document.querySelector('[data-verifikasi]');
  if (formVerifikasi) {
    const blokPembimbing = formVerifikasi.querySelector('[data-blok="pembimbing"]');
    const pilihPembimbing = blokPembimbing.querySelector('select');
    const catatan = formVerifikasi.querySelector('[name="catatan_admin"]');
    const labelCatatan = formVerifikasi.querySelector('[data-label-catatan]');
    const tombol = formVerifikasi.querySelector('[type="submit"]');

    const perbarui = () => {
      const pilihan = formVerifikasi.querySelector('[name="keputusan"]:checked')?.value;
      blokPembimbing.hidden = pilihan !== 'setujui';
      pilihPembimbing.required = pilihan === 'setujui';
      catatan.required = pilihan === 'tolak';
      labelCatatan.textContent = pilihan === 'tolak' ? 'Alasan penolakan' : 'Catatan untuk mahasiswa (opsional)';
      tombol.textContent = pilihan === 'tolak' ? 'Tolak pengajuan' : 'Setujui pengajuan';
      tombol.className = pilihan === 'tolak' ? 'btn btn-danger' : 'btn btn-primary';
      tombol.disabled = !pilihan;
    };

    formVerifikasi.querySelectorAll('[name="keputusan"]').forEach((radio) => radio.addEventListener('change', perbarui));
    perbarui();
  }

  // Label NIM / NIDN menyesuaikan peran yang dipilih
  document.querySelectorAll('[data-peran-select]').forEach((select) => {
    const label = document.querySelector('[data-label-nomor]');
    if (!label) return;
    const ubah = () => {
      label.textContent = select.value === 'pembimbing' ? 'NIDN / NIP'
        : select.value === 'admin' ? 'Nomor induk (opsional)' : 'NIM';
    };
    select.addEventListener('change', ubah);
    ubah();
  });

  // Slider persentase progress
  document.querySelectorAll('[data-range-output]').forEach((range) => {
    const keluaran = document.getElementById(range.dataset.rangeOutput);
    const sinkron = () => { keluaran.textContent = `${range.value}%`; };
    range.addEventListener('input', sinkron);
    sinkron();
  });
});
