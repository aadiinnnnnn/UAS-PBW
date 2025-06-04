// Di dalam file bersih.js
document.addEventListener("DOMContentLoaded", function () {
  // ... (kode formatRupiah Anda) ...

  const paketRadios = document.querySelectorAll(".paket-radio");
  const paketCards = document.querySelectorAll(".paket-card"); // Ambil semua kartu paket
  const ringkasanDetailEl = document.getElementById("ringkasanDetail");
  const totalBiayaTextEl = document.getElementById("totalBiayaText");
  const totalBiayaInputEl = document.getElementById("total_biaya_input");
  const namaPaketInputEl = document.getElementById("nama_paket_input");
  const idBkInputEl = document.getElementById("id_bk_input");
  const tanggalDatangInput = document.getElementById("tanggal_datang");

  function updateTotal() {
    let total = 0;
    let ringkasanHtml = '<p class="text-muted">Pilih paket untuk melihat rincian.</p>';
    let idBk = "";
    let namaPaketText = "";

    const paketTerpilih = document.querySelector('input[name="paket_kos"]:checked');

    if (paketTerpilih) {
      const hargaPaket = parseFloat(paketTerpilih.value);
      namaPaketText = paketTerpilih.getAttribute("data-nama");
      idBk = paketTerpilih.getAttribute("data-idbk");

      total = hargaPaket;

      ringkasanHtml = `
                <div class="d-flex justify-content-between">
                    <span>${namaPaketText.split(" - ")[0]}</span> {/* Ambil hanya nama paket */}
                    <span>${formatRupiah(hargaPaket)}</span>
                </div>
            `;

      if (namaPaketInputEl) namaPaketInputEl.value = namaPaketText; // Kirim nama + deskripsi
      if (totalBiayaInputEl) totalBiayaInputEl.value = total;
      if (idBkInputEl) idBkInputEl.value = idBk;
    } else {
      if (namaPaketInputEl) namaPaketInputEl.value = "";
      if (totalBiayaInputEl) totalBiayaInputEl.value = 0;
      if (idBkInputEl) idBkInputEl.value = "";
    }

    if (ringkasanDetailEl) ringkasanDetailEl.innerHTML = ringkasanHtml;
    if (totalBiayaTextEl) totalBiayaTextEl.innerText = formatRupiah(total);
  }

  // Event listener untuk klik pada kartu paket
  paketCards.forEach((card) => {
    card.addEventListener("click", function () {
      // Hapus kelas 'selected' dari semua kartu
      paketCards.forEach((c) => c.classList.remove("selected"));
      // Tambahkan kelas 'selected' ke kartu yang diklik
      this.classList.add("selected");

      // Tandai radio button di dalam kartu ini
      const radioInside = this.querySelector(".paket-radio");
      if (radioInside) {
        radioInside.checked = true;
        // Picu event change secara manual agar updateTotal terpanggil
        const event = new Event("change", { bubbles: true });
        radioInside.dispatchEvent(event);
      }
    });
  });

  // Event listener untuk perubahan radio (jika dipilih dengan cara lain, misal keyboard)
  paketRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      paketCards.forEach((card) => card.classList.remove("selected"));
      if (this.checked) {
        const parentCard = this.closest(".paket-card");
        if (parentCard) {
          parentCard.classList.add("selected");
        }
      }
      updateTotal();
    });
    // Inisialisasi tampilan 'selected' saat halaman dimuat jika ada yang sudah terpilih
    if (radio.checked) {
      const parentCard = radio.closest(".paket-card");
      if (parentCard) {
        parentCard.classList.add("selected");
      }
    }
  });

  // Inisialisasi tanggal datang
  if (tanggalDatangInput) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0"); // Bulan dari 0-11
    const dd = String(today.getDate()).padStart(2, "0");
    tanggalDatangInput.setAttribute("min", `${yyyy}-${mm}-${dd}`);
  }

  updateTotal(); // Panggil saat load pertama kali
});
