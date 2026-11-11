// PrakTI — interaksi di sisi browser (tanpa build step)
document.addEventListener('DOMContentLoaded', () => {
  /* ---------------------------------------------------------------
   * Modal konfirmasi untuk tindakan penting (Look & Feel #18)
   * <form data-confirm="..."> atau <button data-konfirmasi="...">
   * --------------------------------------------------------------- */
  const modalEl = document.createElement('div');
  modalEl.className = 'modal fade';
  modalEl.tabIndex = -1;
  modalEl.setAttribute('aria-hidden', 'true');
  modalEl.innerHTML = `
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header"><h2 class="modal-title fs-5">Konfirmasi</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><p class="mb-0" data-pesan></p></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" data-lanjut>Ya, lanjutkan</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(modalEl);
  const modal = window.bootstrap ? new bootstrap.Modal(modalEl) : null;
  let aksiTertunda = null;
  modalEl.querySelector('[data-lanjut]').addEventListener('click', () => {
    modal.hide();
    if (aksiTertunda) aksiTertunda();
    aksiTertunda = null;
  });

  const tanya = (pesan, lanjut) => {
    if (!modal) { if (window.confirm(pesan)) lanjut(); return; }
    modalEl.querySelector('[data-pesan]').textContent = pesan;
    aksiTertunda = lanjut;
    modal.show();
  };

  /* ---------------------------------------------------------------
   * Satu penangan submit: catatan wajib, konfirmasi, lalu indikator proses
   * --------------------------------------------------------------- */
  document.addEventListener('submit', (event) => {
    const form = event.target;
    const tombol = event.submitter;

    // Tombol "minta perbaikan/revisi" wajib disertai catatan
    if (tombol && tombol.hasAttribute('data-perlu-catatan')) {
      const nama = tombol.dataset.perluCatatan || 'catatan';
      const kolom = form.querySelector(`[name="${nama}"]`);
      if (kolom && !kolom.value.trim()) {
        event.preventDefault();
        kolom.classList.add('is-invalid');
        kolom.placeholder = 'Tuliskan bagian yang perlu diperbaiki sebelum mengirim.';
        kolom.focus();
        return;
      }
    }

    const pesan = (tombol && tombol.dataset.konfirmasi) || form.dataset.confirm;
    if (pesan && form.dataset.terkonfirmasi !== '1') {
      event.preventDefault();
      tanya(pesan, () => {
        form.dataset.terkonfirmasi = '1';
        if (tombol) form.requestSubmit(tombol); else form.requestSubmit();
      });
      return;
    }
    delete form.dataset.terkonfirmasi;

    // Indikator proses (NFR-02, Look & Feel #25). Nilai tombol submit disalin ke input tersembunyi
    // karena tombol yang dinonaktifkan tidak ikut terkirim.
    if (form.method.toLowerCase() === 'get') return;
    if (tombol && tombol.name) {
      const salinan = document.createElement('input');
      salinan.type = 'hidden';
      salinan.name = tombol.name;
      salinan.value = tombol.value;
      form.appendChild(salinan);
    }
    form.querySelectorAll('button[type="submit"], button:not([type])').forEach((b) => {
      b.disabled = true;
      if (b === tombol || (!tombol && b.type === 'submit')) {
        b.dataset.teksAsli = b.innerHTML;
        b.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Memproses…';
      }
    });
  });

  // Saat kembali dengan tombol Back, aktifkan lagi tombol yang sempat dinonaktifkan
  window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[data-teks-asli]').forEach((b) => {
      b.disabled = false;
      b.innerHTML = b.dataset.teksAsli;
    });
  });

  /* ---------------------------------------------------------------
   * Buka tab sesuai hash URL: #tab-progress, #progress-3, #logbook-12, #laporan-4
   * --------------------------------------------------------------- */
  const hash = window.location.hash.slice(1);
  if (hash && window.bootstrap) {
    const namaTab = hash.startsWith('tab-') ? hash.slice(4) : hash.split('-')[0];
    const tombolTab = document.querySelector(`[data-bs-target="#tab-${namaTab}"]`);
    if (tombolTab) bootstrap.Tab.getOrCreateInstance(tombolTab).show();
    const sasaran = !hash.startsWith('tab-') && document.getElementById(hash);
    if (sasaran) setTimeout(() => sasaran.scrollIntoView({ block: 'center' }), 150);
  }

  /* ---------------------------------------------------------------
   * Informasi berkas yang dipilih: nama, jenis, ukuran (Look & Feel #19)
   * --------------------------------------------------------------- */
  const ukuran = (b) => (b >= 1048576 ? `${(b / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(b / 1024))} KB`);
  document.querySelectorAll('input[type="file"]').forEach((input) => {
    const info = document.createElement('div');
    info.className = 'form-text pk-file-chosen';
    info.setAttribute('aria-live', 'polite');
    input.insertAdjacentElement('afterend', info);
    input.addEventListener('change', () => {
      const f = input.files[0];
      if (!f) { info.textContent = ''; return; }
      const ext = (f.name.split('.').pop() || '').toUpperCase();
      info.textContent = `Dipilih: ${f.name} (${ext}, ${ukuran(f.size)})`;
    });
  });

  /* ---------------------------------------------------------------
   * Form verifikasi pengajuan (admin)
   * --------------------------------------------------------------- */
  const formVerifikasi = document.querySelector('[data-verifikasi]');
  if (formVerifikasi) {
    const catatan = formVerifikasi.querySelector('[name="catatan_admin"]');
    const label = formVerifikasi.querySelector('[data-label-catatan]');
    const kirim = formVerifikasi.querySelector('[type="submit"]');
    const perbarui = () => {
      const pilihan = formVerifikasi.querySelector('[name="keputusan"]:checked')?.value;
      const perbaikan = pilihan === 'revisi';
      catatan.required = perbaikan;
      label.textContent = perbaikan ? 'Catatan perbaikan (wajib)' : 'Catatan untuk mahasiswa (opsional)';
      kirim.textContent = perbaikan ? 'Kirim catatan perbaikan' : 'Setujui pengajuan';
      kirim.className = perbaikan ? 'btn btn-danger' : 'btn btn-primary';
      kirim.disabled = !pilihan;
    };
    formVerifikasi.querySelectorAll('[name="keputusan"]').forEach((r) => r.addEventListener('change', perbarui));
    perbarui();
  }

  /* Form registrasi & pengguna: label NIM/NIDN dan bagian khusus peran ([data-hanya="mahasiswa pembimbing"]) */
  document.querySelectorAll('[data-peran-select]').forEach((select) => {
    const form = select.form;
    const label = form.querySelector('[data-label-nomor]');
    const ubah = () => {
      if (label) label.textContent = select.value === 'pembimbing' ? 'NIDN' : 'NIM';
      form.querySelectorAll('[data-hanya]').forEach((el) => {
        const tampil = el.dataset.hanya.split(' ').includes(select.value);
        el.hidden = !tampil;
        el.querySelectorAll('input, select, textarea').forEach((i) => { i.disabled = !tampil; });
      });
    };
    select.addEventListener('change', ubah);
    ubah();
  });

  /* Pengajuan: kolom instansi baru hanya tampil jika memilih "Instansi lain" */
  document.querySelectorAll('[data-tempat-select]').forEach((select) => {
    const blok = select.form.querySelector('[data-tempat-baru]');
    const ubah = () => {
      const baru = select.value === '';
      blok.hidden = !baru;
      blok.querySelectorAll('input, textarea').forEach((i) => { i.disabled = !baru; });
    };
    select.addEventListener('change', ubah);
    ubah();
  });

  /* Slider persentase progress */
  document.querySelectorAll('[data-range-output]').forEach((range) => {
    const keluaran = document.getElementById(range.dataset.rangeOutput);
    const sinkron = () => { keluaran.textContent = `${range.value}%`; };
    range.addEventListener('input', sinkron);
    sinkron();
  });



  /* Penilaian pembimbing: hitung nilai tertimbang secara langsung */
  document.querySelectorAll('[data-hitung-nilai]').forEach((form) => {
    const hasil = form.querySelector('[data-hasil]');
    const hitung = () => {
      let total = 0;
      let lengkap = true;
      form.querySelectorAll('[data-bobot]').forEach((i) => {
        if (i.value === '') lengkap = false;
        total += (parseFloat(i.value) || 0) * parseFloat(i.dataset.bobot) / 100;
      });
      hasil.textContent = lengkap ? total.toFixed(2).replace('.', ',') : '–';
    };
    form.addEventListener('input', hitung);
    hitung();
  });
});
