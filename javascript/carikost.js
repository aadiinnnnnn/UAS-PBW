document.addEventListener("DOMContentLoaded", function () {
  const searchForm = document.getElementById("searchForm");
  const hasilPencarianKost = document.getElementById("hasilPencarianKost");
  const loadingIndicator = document.getElementById("loadingIndicator");

  // Mengambil semua elemen filter dari form utama dan form filter tambahan
  const allFilterInputs = document.querySelectorAll("#searchForm input, #searchForm select, #collapseFilters input, #collapseFilters select");

  let fetchTimeout; // Variabel untuk menyimpan timeout

  function fetchKostData() {
    // Tampilkan indikator loading dan bersihkan hasil sebelumnya
    if (loadingIndicator) {
      loadingIndicator.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height: 200px;"><div class="spinner-border text-primary" role="status"><span class="sr-only">Mencari...</span></div></div>';
    }
    if (hasilPencarianKost) {
      hasilPencarianKost.innerHTML = "";
    }

    // Kumpulkan data dari semua form/filter
    const formData = new FormData(searchForm); // Form utama

    // Tambahkan filter dari #collapseFilters jika ada
    const advancedFiltersContainer = document.getElementById("collapseFilters");
    if (advancedFiltersContainer) {
      const advancedFilterInputs = advancedFiltersContainer.querySelectorAll("input, select");
      advancedFilterInputs.forEach((input) => {
        if (input.type === "checkbox") {
          if (input.checked) {
            formData.append(input.name, input.value);
          }
        } else if (input.value) {
          formData.append(input.name, input.value);
        }
      });
    }

    const params = new URLSearchParams(formData);

    // Path ke script backend PHP Anda
    const backendUrl = "carikost_data.php"; // Pastikan ini adalah path yang benar relatif terhadap carikost.php

    fetch(`${backendUrl}?${params.toString()}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (loadingIndicator) {
          loadingIndicator.innerHTML = ""; // Hapus indikator loading
        }

        if (data.success && data.data.length > 0) {
          data.data.forEach((kost) => {
            // Memproses fasilitas dari string menjadi badge
            const fasilitasList = kost.fasilitas
              ? kost.fasilitas
                  .split(",")
                  .map((f) => `<span class="badge badge-facility">${f.trim()}</span>`)
                  .join(" ")
              : '<span class="badge badge-light">Fasilitas tidak diatur</span>';

            // Format harga
            const hargaText = kost.harga_sewa !== null ? `Rp ${parseFloat(kost.harga_sewa).toLocaleString("id-ID")} <span class="price-period">/ ${kost.periode_sewa || "Bulan"}</span>` : "Harga belum diatur";

            // Menentukan path gambar yang benar dan gambar cadangan (placeholder)
            // Diasumsikan gambar_url dari DB adalah path relatif dari root proyek, misal: "uploads/kost_images/gambar.jpg"
            // Karena script.js ada di javascript/ dan carikost_data.php di admin/,
            // dan carikost.php juga di admin/, path untuk <img> src harus relatif dari carikost.php
            const imageUrl = kost.gambar_url ? `../${kost.gambar_url}` : "https://via.placeholder.com/400x300.png?text=Tidak+Ada+Gambar";

            const kostId = kost.id_kos_plk; // ID Kost untuk link pemesanan

            const kostCard = `
              <div class="col-md-6 col-lg-4 d-flex align-items-stretch mb-4">
                  <div class="card kost-card w-100">
                      <div class="img-container">
                          <img src="${imageUrl}" class="card-img-top" alt="${kost.nama || "Nama Kost Tidak Tersedia"}" onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300.png?text=Gagal+Muat';">
                      </div>
                      <div class="card-body d-flex flex-column">
                          <h5 class="kost-name">${kost.nama || "Nama Kost Tidak Tersedia"}</h5>
                          <p class="kost-location"><i class="fas fa-map-marker-alt"></i> ${kost.lokasi || "Lokasi Tidak Tersedia"}</p>
                          <div class="kost-facilities mb-2">
                            ${fasilitasList}
                          </div>
                          <p class="kost-price mt-auto">${hargaText}</p>
                          <a href="order_kost.php?id_kost=${kostId}" class="btn btn-detail mt-2">Pesan Kost Ini</a>
                      </div>
                  </div>
              </div>
            `;
            if (hasilPencarianKost) {
              hasilPencarianKost.innerHTML += kostCard;
            }
          });
        } else {
          if (hasilPencarianKost) {
            hasilPencarianKost.innerHTML = `<div class="col-12 text-center py-5"><p class="lead">${data.message || "Tidak ada kost yang ditemukan dengan kriteria Anda."}</p></div>`;
          }
        }
      })
      .catch((error) => {
        console.error("Kesalahan saat mengambil data kost:", error);
        if (loadingIndicator) {
          loadingIndicator.innerHTML = ""; // Kosongkan loading
        }
        if (hasilPencarianKost) {
          hasilPencarianKost.innerHTML = `<div class="col-12 text-center py-5"><p class="text-danger">Gagal memuat data kost. Silakan coba lagi nanti atau periksa koneksi Anda.</p></div>`;
        }
      });
  }

  // Event listener untuk form pencarian utama (saat tombol 'Cari' di-klik)
  if (searchForm) {
    searchForm.addEventListener("submit", function (event) {
      event.preventDefault();
      clearTimeout(fetchTimeout); // Hapus timeout jika ada
      fetchKostData();
    });
  }

  // Event listener untuk semua input filter agar interaktif
  allFilterInputs.forEach((filter) => {
    // 'input' event untuk text & number agar lebih responsif saat mengetik
    // 'change' untuk select & checkbox
    const eventType = filter.type === "text" || filter.type === "number" ? "input" : "change";
    filter.addEventListener(eventType, () => {
      clearTimeout(fetchTimeout); // Batalkan request sebelumnya jika user masih mengetik/mengubah
      // Atur timeout agar tidak mengirim request pada setiap ketikan/perubahan cepat
      fetchTimeout = setTimeout(() => {
        fetchKostData();
      }, 700); // Delay 700ms sebelum fetch (bisa disesuaikan)
    });
  });

  // Pengambilan data awal saat halaman pertama kali dimuat
  // Hanya panggil jika elemen-elemen penting ada
  if (searchForm && hasilPencarianKost && loadingIndicator) {
    fetchKostData();
  } else {
    console.warn("Satu atau lebih elemen DOM penting untuk pencarian kost tidak ditemukan.");
  }
});
