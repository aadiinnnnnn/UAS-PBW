// addkost.js
//
document.addEventListener("DOMContentLoaded", function () {
  // Tahun di footer sudah di-set oleh PHP di pemilik.php
  // const tahunElement = document.getElementById('tahunSekarangTambahKost');
  // if (tahunElement) {
  //     tahunElement.textContent = new Date().getFullYear();
  // }

  function setupFileInput(fileInputId, previewElementId) {
    const fileInput = document.getElementById(fileInputId);
    if (fileInput) {
      fileInput.addEventListener("change", function (event) {
        const inputFile = event.target;
        const label = $(inputFile).next(".custom-file-label");
        let fileNameText = "Pilih file...";

        if (inputFile.files && inputFile.files.length === 1) {
          fileNameText = inputFile.files[0].name;
        } else if (inputFile.files && inputFile.files.length > 1) {
          fileNameText = `${inputFile.files.length} file dipilih`;
        }

        if (label.length > 0) {
          label.html(fileNameText);
        }

        if (previewElementId && inputFile.files && inputFile.files[0]) {
          const previewContainer = document.getElementById(previewElementId);
          if (previewContainer) {
            previewContainer.innerHTML = "";

            const reader = new FileReader();
            reader.onload = function (e) {
              const img = document.createElement("img");
              img.src = e.target.result;
              img.style.maxWidth = "100%";
              img.style.maxHeight = "150px";
              img.style.marginTop = "10px";
              img.classList.add("img-thumbnail");
              previewContainer.appendChild(img);
            };
            reader.readAsDataURL(inputFile.files[0]);
          }
        } else if (previewElementId && (!inputFile.files || inputFile.files.length === 0)) {
          const previewContainer = document.getElementById(previewElementId);
          if (previewContainer) {
            previewContainer.innerHTML = "";
          }
        }
      });
    }
  }

  setupFileInput("fotoUtama", "previewFotoUtama");

  const form = document.getElementById("formTambahKostPage");
  if (form) {
    form.addEventListener("submit", function (event) {
      const requiredTextInputs = [
        { id: "namaKost", label: "Nama Kost" },
        { id: "alamatKost", label: "Alamat Lengkap Kost" },
        { id: "hargaBulanan", label: "Harga per Bulan" },
        { id: "jumlahKamar", label: "Jumlah Total Kamar" },
        { id: "nomorTeleponKontak", label: "Nomor Telepon/WA Aktif" },
        { id: "namaKontak", label: "Nama Pemilik/Penjaga" },
      ];

      for (let item of requiredTextInputs) {
        const inputElement = document.getElementById(item.id);
        const labelElement = inputElement ? (inputElement.labels ? inputElement.labels[0] : null) : null; // Check if labels exist
        const labelText = labelElement ? labelElement.textContent.replace("*", "").trim() : item.id;

        if (inputElement && inputElement.value.trim() === "") {
          alert(`Kolom "${labelText}" wajib diisi!`);
          event.preventDefault();
          inputElement.focus();
          return;
        }
      }

      const tipeKostSelect = document.getElementById("tipeKost");
      if (tipeKostSelect && tipeKostSelect.value === "") {
        alert("Tipe Kost wajib dipilih!");
        event.preventDefault();
        tipeKostSelect.focus();
        return;
      }

      const hargaBulananInput = document.getElementById("hargaBulanan");
      if (hargaBulananInput && (hargaBulananInput.value.trim() === "" || parseFloat(hargaBulananInput.value) < 0)) {
        alert("Harga per Bulan wajib diisi dan tidak boleh negatif!");
        event.preventDefault();
        hargaBulananInput.focus();
        return;
      }
      const jumlahKamarInput = document.getElementById("jumlahKamar");
      if (jumlahKamarInput && (jumlahKamarInput.value.trim() === "" || parseInt(jumlahKamarInput.value) < 0)) {
        alert("Jumlah Kamar wajib diisi dan tidak boleh negatif!");
        event.preventDefault();
        jumlahKamarInput.focus();
        return;
      }
      const nomorTeleponKontakInput = document.getElementById("nomorTeleponKontak");
      const phonePattern = /^[0-9]{10,15}$/;
      if (nomorTeleponKontakInput && !phonePattern.test(nomorTeleponKontakInput.value.trim())) {
        alert("Format Nomor Telepon Kontak tidak valid (10-15 digit angka).");
        event.preventDefault();
        nomorTeleponKontakInput.focus();
        return;
      }
    });
  }
});
