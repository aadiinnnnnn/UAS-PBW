// Contoh di dalam fungsi kalkulasi di order.js
function updateTotalBiaya() {
  let jarakInput = document.getElementById("jarak"); // Pastikan ID 'jarak' benar
  let jarak = 0;
  if (jarakInput) {
    jarak = parseFloat(jarakInput.value) || 0;
    console.log("Jarak:", jarak);
  }

  let totalBiayaBarang = 0;
  const hargaBarang = {
    // Pastikan ini sama dengan yang di PHP
    koper: 20000,
    kardusSedang: 35000,
    kardusBesar: 50000,
    lemariKecil: 100000,
    kasurSingle: 75000,
    mejaBelajar: 60000,
  };
  const HARGA_PER_KM = 5000; // Pastikan ini sama

  document.querySelectorAll('input[name="barang[]"]:checked').forEach(function (checkbox) {
    console.log("Barang dipilih:", checkbox.value, "Harga:", hargaBarang[checkbox.value]);
    if (hargaBarang[checkbox.value]) {
      totalBiayaBarang += hargaBarang[checkbox.value];
    }
  });
  console.log("Total Biaya Barang:", totalBiayaBarang);

  let biayaJarak = jarak * HARGA_PER_KM;
  console.log("Biaya Jarak:", biayaJarak);

  let totalKeseluruhan = biayaJarak + totalBiayaBarang;
  console.log("Total Keseluruhan:", totalKeseluruhan);

  let elemenTotalBiaya = document.getElementById("totalBiaya"); // Pastikan ID 'totalBiaya' benar
  if (elemenTotalBiaya) {
    elemenTotalBiaya.textContent = "Rp " + totalKeseluruhan.toLocaleString("id-ID");
  }

  // Update juga ringkasan biaya jarak dan barang jika ada elemennya
  let elemenBiayaJarak = document.getElementById("biayaJarak");
  if (elemenBiayaJarak) {
    elemenBiayaJarak.textContent = "Rp " + biayaJarak.toLocaleString("id-ID");
  }
  let elemenSummaryJarak = document.getElementById("summaryJarak");
  if (elemenSummaryJarak) {
    elemenSummaryJarak.textContent = jarak;
  }

  let summaryBarangDiv = document.getElementById("summaryBarang");
  if (summaryBarangDiv) {
    summaryBarangDiv.innerHTML = ""; // Kosongkan dulu
    document.querySelectorAll('input[name="barang[]"]:checked').forEach(function (checkbox) {
      if (hargaBarang[checkbox.value]) {
        // Ambil nama barang dari label atau definisikan di JS
        let namaBarang = checkbox.parentElement.querySelector("label").textContent.split(" (Rp")[0];
        let hargaItem = hargaBarang[checkbox.value];
        summaryBarangDiv.innerHTML += `<div class="d-flex justify-content-between mb-1"><span>${namaBarang}</span><span>Rp ${hargaItem.toLocaleString("id-ID")}</span></div>`;
      }
    });
  }
  // Aktifkan/Nonaktifkan tombol bayar berdasarkan total biaya
  let bayarButton = document.getElementById("bayarButton");
  if (bayarButton) {
    bayarButton.disabled = totalKeseluruhan <= 0;
  }
}

// Pastikan event listener terpasang dengan benar
document.addEventListener("DOMContentLoaded", function () {
  const jarakInput = document.getElementById("jarak");
  if (jarakInput) {
    jarakInput.addEventListener("input", updateTotalBiaya);
  }

  const checkboxesBarang = document.querySelectorAll('input[name="barang[]"]');
  checkboxesBarang.forEach(function (checkbox) {
    checkbox.addEventListener("change", updateTotalBiaya);
  });

  // Panggil sekali saat load untuk inisialisasi jika ada nilai default
  updateTotalBiaya();
});
// File: ../javascript/order.js (atau di dalam <script> di order.php)

document.addEventListener("DOMContentLoaded", function () {
  const orderForm = document.getElementById("orderForm"); // Pastikan ID form Anda adalah 'orderForm'
  const bayarButton = document.getElementById("bayarButton"); // Pastikan ID tombol Anda adalah 'bayarButton'

  if (bayarButton && orderForm) {
    bayarButton.addEventListener("click", function () {
      // Kumpulkan data form
      const formData = new FormData(orderForm);
      formData.append("submit_order_js", "1"); // Tambahkan flag untuk identifikasi di PHP

      // Nonaktifkan tombol untuk mencegah submit ganda
      bayarButton.disabled = true;
      bayarButton.textContent = "Memproses...";

      fetch("order.php", {
        // Pastikan path ke order.php benar
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Pesanan berhasil diproses oleh server
            // Server sudah menyimpan detail di $_SESSION['order_details']
            // Sekarang, redirect ke halaman order_sukses.php
            window.location.href = "order_sukses.php"; // Pastikan path ini benar
          } else {
            // Ada kesalahan, tampilkan pesan error
            alert("Terjadi kesalahan: " + data.message);
            bayarButton.disabled = false; // Aktifkan kembali tombol
            bayarButton.textContent = "Pesan Sekarang";
          }
        })
        .catch((error) => {
          console.error("Error saat mengirim pesanan:", error);
          alert("Tidak dapat terhubung ke server. Silakan coba lagi.");
          bayarButton.disabled = false; // Aktifkan kembali tombol
          bayarButton.textContent = "Pesan Sekarang";
        });
    });
  }
});
