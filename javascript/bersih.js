// Di dalam file bersih.js
document.addEventListener("DOMContentLoaded", function () {
  // Fungsi helper untuk format Rupiah
  function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(angka);
  }

  const paketRadios = document.querySelectorAll(".paket-radio");
  const paketCards = document.querySelectorAll(".paket-card");
  const ringkasanDetailEl = document.getElementById("ringkasanDetail");
  const totalBiayaTextEl = document.getElementById("totalBiayaText");
  const totalBiayaInputEl = document.getElementById("total_biaya_input");
  const namaPaketInputEl = document.getElementById("nama_paket_input");
  const idBkInputEl = document.getElementById("id_bk_input");
  const tanggalDatangInput = document.getElementById("tanggal_datang");

  function updateTotal() {
    let hargaSetelahDiskon = 0;
    let ringkasanHtml = '<p class="text-muted text-center mb-0">Pilih paket untuk melihat rincian.</p>';
    let idBk = "";
    let namaPaketText = "";

    const paketTerpilih = document.querySelector('input[name="paket_kos"]:checked');

    if (paketTerpilih) {
      const hargaAsli = parseFloat(paketTerpilih.value);
      const diskonPersen = parseFloat(paketTerpilih.getAttribute("data-diskon")) || 0;
      namaPaketText = paketTerpilih.getAttribute("data-nama");
      const deskripsiPaket = paketTerpilih.getAttribute("data-deskripsi");
      idBk = paketTerpilih.getAttribute("data-idbk");

      const nilaiDiskon = hargaAsli * (diskonPersen / 100);
      hargaSetelahDiskon = hargaAsli - nilaiDiskon;

      ringkasanHtml = `
          <div class="d-flex justify-content-between">
              <span>${namaPaketText}</span>
              <span>${formatRupiah(hargaAsli)}</span>
          </div>
      `;

      if (diskonPersen > 0) {
        ringkasanHtml += `
          <div class="d-flex justify-content-between text-danger">
              <span>Diskon ${diskonPersen}%</span>
              <span>- ${formatRupiah(nilaiDiskon)}</span>
          </div>
          <hr class="my-2">
        `;
      }

      if (namaPaketInputEl) namaPaketInputEl.value = `${namaPaketText} - ${deskripsiPaket}`;
      if (totalBiayaInputEl) totalBiayaInputEl.value = hargaSetelahDiskon;
      if (idBkInputEl) idBkInputEl.value = idBk;
    } else {
      if (namaPaketInputEl) namaPaketInputEl.value = "";
      if (totalBiayaInputEl) totalBiayaInputEl.value = 0;
      if (idBkInputEl) idBkInputEl.value = "";
    }

    if (ringkasanDetailEl) ringkasanDetailEl.innerHTML = ringkasanHtml;
    if (totalBiayaTextEl) totalBiayaTextEl.innerText = formatRupiah(hargaSetelahDiskon);
  }

  paketCards.forEach((card) => {
    card.addEventListener("click", function (event) {
      // Hanya proses jika target klik bukan elemen interaktif lain di dalam kartu
      if (event.target.tagName === "A" || event.target.tagName === "BUTTON") return;

      paketCards.forEach((c) => c.classList.remove("selected"));
      this.classList.add("selected");

      const radioInside = this.querySelector(".paket-radio");
      if (radioInside && !radioInside.checked) {
        radioInside.checked = true;
        const changeEvent = new Event("change", { bubbles: true });
        radioInside.dispatchEvent(changeEvent);
      }
    });
  });

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
    if (radio.checked) {
      const parentCard = radio.closest(".paket-card");
      if (parentCard) {
        parentCard.classList.add("selected");
      }
    }
  });

  if (tanggalDatangInput) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    tanggalDatangInput.setAttribute("min", `${yyyy}-${mm}-${dd}`);
  }

  updateTotal();
});
