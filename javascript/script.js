document.addEventListener("DOMContentLoaded", function () {
  // Set tahun sekarang di footer
  const tahunElement = document.getElementById("tahunSekarang");
  if (tahunElement) {
    tahunElement.textContent = new Date().getFullYear();
  }

  const searchForm = document.getElementById("searchForm");
  const hasilPencarianKost = document.getElementById("hasilPencarianKost");
  const loadingIndicator = document.getElementById("loadingIndicator");

  function fetchKostData() {
    loadingIndicator.innerHTML = "<p>Mencari kost...</p>";
    hasilPencarianKost.innerHTML = ""; // Bersihkan hasil sebelumnya

    const lokasi = document.getElementById("lokasiKost").value;
    const tipe = document.getElementById("tipeKost").value;
    const hargaMaks = document.getElementById("hargaKost").value;
    const durasiSewa = document.getElementById("durasiSewa").value;
    const urutkan = document.getElementById("urutkan").value;

    const fasilitasCheckboxes = document.querySelectorAll('input[name="fasilitas[]"]:checked');
    const fasilitas = Array.from(fasilitasCheckboxes).map((cb) => cb.value);

    const params = new URLSearchParams();
    if (lokasi) params.append("lokasi", lokasi);
    if (tipe) params.append("tipe", tipe);
    if (hargaMaks) params.append("harga_maks", hargaMaks);
    if (durasiSewa) params.append("durasi_sewa", durasiSewa);
    if (urutkan) params.append("urutkan", urutkan);
    fasilitas.forEach((f) => params.append("fasilitas[]", f)); // Tambahkan setiap fasilitas sebagai array

    fetch(`carikost_data.php?${params.toString()}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        loadingIndicator.innerHTML = ""; // Bersihkan pesan pemuatan

        if (data.success && data.data.length > 0) {
          data.data.forEach((kost) => {
            const fasilitasList = kost.fasilitas
              ? kost.fasilitas
                  .split(", ")
                  .map((f) => `<li>${f}</li>`)
                  .join("")
              : "";
            const hargaText = kost.harga_sewa !== null ? `Rp ${parseFloat(kost.harga_sewa).toLocaleString("id-ID")} <span class="price-period">/ ${kost.periode_sewa}</span>` : "Harga belum diatur";

            const kostCard = `
              <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                  <div class="card kost-card-search w-100">
                      <img src="${kost.gambar_url_display}" class="card-img-top" alt="${kost.nama}" onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300.png?text=No+Image';">
                      <div class="card-body d-flex flex-column">
                          <h5 class="kost-name">${kost.nama}</h5>
                          <p class="kost-location"><i class="fas fa-map-marker-alt"></i> ${kost.lokasi}</p>
                          <p class="kost-price">${hargaText}</p>
                          ${fasilitasList ? `<ul class="fasilitas-list mt-auto mb-2">${fasilitasList}</ul>` : ""}
                          <button class="btn btn-detail mt-auto">Lihat Detail</button>
                      </div>
                  </div>
              </div>
            `;
            hasilPencarianKost.innerHTML += kostCard;
          });
        } else {
          hasilPencarianKost.innerHTML = `<div class="col-12 text-center"><p>${data.message || "Tidak ada kost yang ditemukan dengan kriteria Anda."}</p></div>`;
        }
      })
      .catch((error) => {
        console.error("Kesalahan saat mengambil data kost:", error);
        loadingIndicator.innerHTML = `<p class="text-danger">Gagal memuat data kost. Silakan coba lagi nanti.</p>`;
      });
  }

  // Pendengar peristiwa untuk formulir pencarian dan filter
  searchForm.addEventListener("submit", function (event) {
    event.preventDefault(); // Mencegah pengiriman formulir default
    fetchKostData();
  });

  // Lampirkan pendengar peristiwa untuk filter lanjutan saat perubahan
  document.getElementById("tipeKost").addEventListener("change", fetchKostData);
  document.getElementById("hargaKost").addEventListener("input", fetchKostData);
  document.getElementById("durasiSewa").addEventListener("change", fetchKostData);
  document.getElementById("urutkan").addEventListener("change", fetchKostData);

  document.querySelectorAll('input[name="fasilitas[]"]').forEach((checkbox) => {
    checkbox.addEventListener("change", fetchKostData);
  });

  // Pengambilan awal saat halaman dimuat
  fetchKostData();
});
