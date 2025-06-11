// Di dalam file javascript/order.js

// Fungsi kalkulasi biaya
function updateTotalBiaya() {
  let jarakInput = document.getElementById("jarak");
  let jarak = jarakInput ? parseFloat(jarakInput.value) || 0 : 0;

  let totalBiayaBarang = 0;
  const hargaBarang = {
    koper: 20000,
    kardusSedang: 35000,
    kardusBesar: 50000,
    lemariKecil: 100000,
    kasurSingle: 75000,
    mejaBelajar: 60000,
  };
  const HARGA_PER_KM = 5000;

  document.querySelectorAll('input[name="barang[]"]:checked').forEach(function (checkbox) {
    if (hargaBarang[checkbox.value]) {
      totalBiayaBarang += hargaBarang[checkbox.value];
    }
  });

  let biayaJarak = jarak * HARGA_PER_KM;
  let subtotal = biayaJarak + totalBiayaBarang;

  // BARU: Logika Diskon
  let diskonPersen = 0;
  if (subtotal > 200000) {
    diskonPersen = 15;
  } else if (subtotal > 100000) {
    diskonPersen = 10;
  }

  let nilaiDiskon = subtotal * (diskonPersen / 100);
  let totalKeseluruhan = subtotal - nilaiDiskon;

  // Helper untuk format Rupiah
  const formatRupiah = (angka) => "Rp " + angka.toLocaleString("id-ID");

  // MODIFIKASI: Update elemen UI dengan detail diskon
  let elemenBiayaJarak = document.getElementById("biayaJarak");
  if (elemenBiayaJarak) elemenBiayaJarak.textContent = formatRupiah(biayaJarak);

  let elemenSummaryJarak = document.getElementById("summaryJarak");
  if (elemenSummaryJarak) elemenSummaryJarak.textContent = jarak;

  let summaryBarangDiv = document.getElementById("summaryBarang");
  if (summaryBarangDiv) {
    summaryBarangDiv.innerHTML = "";
    document.querySelectorAll('input[name="barang[]"]:checked').forEach(function (checkbox) {
      if (hargaBarang[checkbox.value]) {
        let namaBarang = checkbox.parentElement.querySelector("label").textContent.split(" (Rp")[0];
        let hargaItem = hargaBarang[checkbox.value];
        summaryBarangDiv.innerHTML += `<div class="d-flex justify-content-between mb-1"><span>${namaBarang}</span><span>${formatRupiah(hargaItem)}</span></div>`;
      }
    });
  }

  // BARU: Tampilkan Subtotal & Diskon
  let elemenSubtotal = document.getElementById("subtotalBiaya");
  if (elemenSubtotal) elemenSubtotal.textContent = formatRupiah(subtotal);

  let elemenDiskonWrapper = document.getElementById("summaryDiskon");
  let elemenNilaiDiskon = document.getElementById("nilaiDiskon");
  if (elemenDiskonWrapper && elemenNilaiDiskon) {
    if (diskonPersen > 0) {
      elemenNilaiDiskon.textContent = `- ${formatRupiah(nilaiDiskon)}`;
      elemenDiskonWrapper.querySelector("span:first-child").textContent = `Diskon (${diskonPersen}%)`;
      elemenDiskonWrapper.style.display = "flex";
    } else {
      elemenDiskonWrapper.style.display = "none";
    }
  }

  // MODIFIKASI: Update Total Biaya Akhir
  let elemenTotalBiaya = document.getElementById("totalBiaya");
  if (elemenTotalBiaya) {
    elemenTotalBiaya.textContent = formatRupiah(totalKeseluruhan);
  }

  // Aktifkan/Nonaktifkan tombol bayar berdasarkan total biaya
  let bayarButton = document.getElementById("bayarButton");
  if (bayarButton) {
    bayarButton.disabled = totalKeseluruhan <= 0;
  }
}

// Event listener untuk form
document.addEventListener("DOMContentLoaded", function () {
  const formInputs = document.querySelectorAll('#jarak, input[name="barang[]"]');
  formInputs.forEach(function (input) {
    input.addEventListener("input", updateTotalBiaya);
    input.addEventListener("change", updateTotalBiaya);
  });

  // Panggil sekali saat load untuk inisialisasi
  updateTotalBiaya();

  // Logic untuk submit form (tetap sama)
  const orderForm = document.getElementById("orderForm");
  const bayarButton = document.getElementById("bayarButton");

  if (bayarButton && orderForm) {
    bayarButton.addEventListener("click", function () {
      const formData = new FormData(orderForm);
      formData.append("submit_order_js", "1");

      // Validasi di sisi klien sebelum mengirim
      const alamatAsal = document.getElementById("asal").value.trim();
      const alamatTujuan = document.getElementById("tujuan").value.trim();
      const jarak = document.getElementById("jarak").value;
      const tanggalPindah = document.getElementById("tanggalPindah").value;
      const barangChecked = document.querySelectorAll('input[name="barang[]"]:checked').length > 0;

      if (!alamatAsal || !alamatTujuan || !jarak || !tanggalPindah || !barangChecked) {
        alert("Harap lengkapi semua field yang wajib diisi (Alamat, Jarak, Tanggal, dan minimal 1 barang).");
        return;
      }

      bayarButton.disabled = true;
      bayarButton.textContent = "Memproses...";

      fetch("order.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            window.location.href = "order_sukses.php";
          } else {
            alert("Terjadi kesalahan: " + data.message);
            bayarButton.disabled = false;
            bayarButton.textContent = "Pesan Sekarang";
          }
        })
        .catch((error) => {
          console.error("Error saat mengirim pesanan:", error);
          alert("Tidak dapat terhubung ke server. Silakan coba lagi.");
          bayarButton.disabled = false;
          bayarButton.textContent = "Pesan Sekarang";
        });
    });
  }
});
