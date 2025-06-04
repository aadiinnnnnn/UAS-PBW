// Di dalam file ../javascript/bersih.js

function formatRupiah(angka, prefix = "Rp ") {
  if (angka === null || angka === undefined || isNaN(parseFloat(angka))) {
    return prefix + "0"; // Mengembalikan Rp 0 untuk input yang tidak valid
  }
  let number_string = parseFloat(angka)
      .toString()
      .replace(/[^,\d]/g, ""),
    split = number_string.split(","),
    sisa = split[0].length % 3,
    rupiah = split[0].substr(0, sisa),
    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

  if (ribuan) {
    let separator = sisa ? "." : "";
    rupiah += separator + ribuan.join(".");
  }

  rupiah = split[1] !== undefined ? rupiah + "," + split[1] : rupiah;
  return prefix + rupiah;
}

document.addEventListener("DOMContentLoaded", function () {
  const paketRadios = document.querySelectorAll(".paket-radio");
  const paketCards = document.querySelectorAll(".paket-card");
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
                    <span>${namaPaketText.split(" - ")[0]}</span>
                    <span>${formatRupiah(hargaPaket)}</span>
                </div>
            `;

      if (namaPaketInputEl) namaPaketInputEl.value = namaPaketText;
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

  paketCards.forEach((card) => {
    card.addEventListener("click", function () {
      paketCards.forEach((c) => c.classList.remove("selected"));
      this.classList.add("selected");

      const radioInside = this.querySelector(".paket-radio");
      if (radioInside) {
        radioInside.checked = true;
        const event = new Event("change", { bubbles: true });
        radioInside.dispatchEvent(event);
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
