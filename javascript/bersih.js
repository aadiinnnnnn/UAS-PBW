document.addEventListener("DOMContentLoaded", function () {
  // Set tanggal minimum untuk input tanggal_datang
  const tanggalDatangInput = document.getElementById("tanggal_datang");
  if (tanggalDatangInput) {
    const today = new Date().toISOString().split("T")[0];
    tanggalDatangInput.setAttribute("min", today);
  }

  // Format angka ke dalam format mata uang Rupiah
  function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(angka);
  }

  // Fungsi utama untuk memperbarui total dan ringkasan
  function updateTotal() {
    let total = 0;
    let ringkasanHtml = "";
    let idBk = "";
    let namaPaketText = "";

    // Ambil elemen DOM
    const ringkasanDetailEl = document.getElementById("ringkasanDetail");
    const totalBiayaTextEl = document.getElementById("totalBiayaText");
    const totalBiayaInputEl = document.getElementById("total_biaya_input");
    const namaPaketInputEl = document.getElementById("nama_paket_input"); // Perbaikan ID
    const idBkInputEl = document.getElementById("id_bk_input"); // Perbaikan ID

    // Cari radio button yang dipilih
    const paketTerpilih = document.querySelector('input[name="paket_kos"]:checked');

    if (paketTerpilih) {
      const hargaPaket = parseFloat(paketTerpilih.value); // Gunakan parseFloat untuk harga
      namaPaketText = paketTerpilih.getAttribute("data-nama");
      idBk = paketTerpilih.getAttribute("data-idbk"); // Ambil id_bk

      total = hargaPaket;

      // Ringkasan HTML
      ringkasanHtml = `
                <div class="d-flex justify-content-between">
                    <span>${namaPaketText}</span>
                    <span>${formatRupiah(hargaPaket)}</span>
                </div>
            `;

      // Simpan nilai di input tersembunyi
      namaPaketInputEl.value = namaPaketText;
      totalBiayaInputEl.value = total;
      idBkInputEl.value = idBk; // Set id_bk ke input tersembunyi
    } else {
      ringkasanHtml = '<p class="text-muted">Pilih paket untuk melihat rincian.</p>';
      namaPaketInputEl.value = "";
      totalBiayaInputEl.value = 0;
      idBkInputEl.value = ""; // Kosongkan id_bk jika tidak ada paket terpilih
    }

    // Tampilkan ke UI
    ringkasanDetailEl.innerHTML = ringkasanHtml;
    totalBiayaTextEl.innerText = formatRupiah(total);
  }

  // Tambahkan event listener ke setiap input radio
  const paketRadios = document.querySelectorAll(".paket-radio");
  paketRadios.forEach((radio) => {
    radio.addEventListener("change", updateTotal);
  });

  // Jalankan fungsi ini saat halaman pertama kali dimuat
  updateTotal();
});
