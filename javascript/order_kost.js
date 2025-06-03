document.addEventListener("DOMContentLoaded", function () {
  const tahunElement = document.getElementById("tahunSekarangOrder");
  if (tahunElement) {
    tahunElement.textContent = new Date().getFullYear();
  }

  const hiddenBasePriceEl = document.getElementById("hiddenBasePrice");
  const basePriceKost = hiddenBasePriceEl ? parseFloat(hiddenBasePriceEl.value) : 0;

  const summaryKostNameEl = document.getElementById("summaryKostName");
  const summaryKostAddressEl = document.getElementById("summaryKostAddress");
  const summaryBasePriceDisplayEl = document.getElementById("summaryBasePrice");

  if (summaryBasePriceDisplayEl && basePriceKost > 0) {
    summaryBasePriceDisplayEl.textContent = basePriceKost.toLocaleString("id-ID");
  }

  const durationSelect = document.getElementById("duration");
  const pricePerDurationDisplayEl = document.getElementById("pricePerDurationDisplay");
  const totalPriceDisplayEl = document.getElementById("totalPriceDisplay");
  const checkInDateInput = document.getElementById("checkInDate");

  if (checkInDateInput) {
    const today = new Date().toISOString().split("T")[0];
    checkInDateInput.setAttribute("min", today);
    checkInDateInput.value = today;
  }

  function calculateAndUpdatePrice() {
    if (!durationSelect || !pricePerDurationDisplayEl || !totalPriceDisplayEl || basePriceKost <= 0) {
      if (pricePerDurationDisplayEl) pricePerDurationDisplayEl.textContent = "-";
      if (totalPriceDisplayEl) totalPriceDisplayEl.textContent = "-";
      return;
    }

    const selectedOption = durationSelect.options[durationSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
      pricePerDurationDisplayEl.textContent = "-";
      totalPriceDisplayEl.textContent = "-";
      return;
    }
    const durationValue = parseInt(selectedOption.value);
    const priceMultiplier = parseFloat(selectedOption.getAttribute("data-price-multiplier"));

    if (durationValue && priceMultiplier) {
      const totalCalculatedPrice = basePriceKost * priceMultiplier;
      let pricePerUnitDurationText;

      if (durationValue === 1) {
        pricePerUnitDurationText = basePriceKost.toLocaleString("id-ID") + " / Bulan";
      } else {
        pricePerUnitDurationText = (totalCalculatedPrice / durationValue).toLocaleString("id-ID", { maximumFractionDigits: 0 }) + " / Bulan (rata-rata)";
      }

      pricePerDurationDisplayEl.textContent = pricePerUnitDurationText;
      totalPriceDisplayEl.textContent = totalCalculatedPrice.toLocaleString("id-ID");
    } else {
      pricePerDurationDisplayEl.textContent = "-";
      totalPriceDisplayEl.textContent = "-";
    }
  }

  if (durationSelect) {
    durationSelect.addEventListener("change", calculateAndUpdatePrice);
    calculateAndUpdatePrice();
  }

  const toastContainer = document.getElementById("toast-container");
  function showToast(message, type = "info", duration = 3500) {
    if (!toastContainer) {
      console.error("Toast container (#toast-container) not found!");
      alert(message);
      return;
    }
    const toast = document.createElement("div");
    toast.classList.add("toast-message", type);
    let iconClass = "";
    switch (type) {
      case "success":
        iconClass = "fas fa-check-circle";
        break;
      case "error":
        iconClass = "fas fa-times-circle";
        break;
      case "warning":
        iconClass = "fas fa-exclamation-triangle";
        break;
      case "info":
        iconClass = "fas fa-info-circle";
        break;
    }
    if (iconClass) {
      toast.innerHTML = `<span class="toast-icon"><i class="${iconClass}"></i></span>`;
    }
    const messageSpan = document.createElement("span");
    messageSpan.textContent = message;
    toast.appendChild(messageSpan);
    const closeButton = document.createElement("button");
    closeButton.classList.add("toast-close-button");
    closeButton.innerHTML = "&times;";
    closeButton.onclick = function () {
      toast.classList.remove("show");
      toast.classList.add("hide");
      setTimeout(() => toast.remove(), 400);
    };
    toast.appendChild(closeButton);
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.classList.add("show");
    }, 10);
    setTimeout(() => {
      toast.classList.remove("show");
      toast.classList.add("hide");
      setTimeout(() => {
        if (toast.parentElement) {
          toast.remove();
        }
      }, 400);
    }, duration);
  }

  const formOrderKost = document.getElementById("formOrderKost");
  if (formOrderKost) {
    // Menangkap klik pada link "Pesan Kost" dan mencegah perilaku default
    const submitOrderLink = document.getElementById("submitOrderLink");
    if (submitOrderLink) {
      submitOrderLink.addEventListener("click", function (event) {
        event.preventDefault(); // Mencegah navigasi langsung
        // Memicu submit form secara programatis
        formOrderKost.dispatchEvent(new Event("submit", { cancelable: true }));
      });
    }

    formOrderKost.addEventListener("submit", function (event) {
      event.preventDefault(); // Mencegah pengiriman formulir default

      const termsAgreement = document.getElementById("termsAgreement");
      if (!termsAgreement.checked) {
        showToast("Anda harus menyetujui Syarat & Ketentuan Pemesanan.", "warning");
        termsAgreement.focus();
        return;
      }

      let isValid = true;
      let firstInvalidField = null;
      formOrderKost.querySelectorAll("[required]").forEach((input) => {
        input.classList.remove("is-invalid"); // Hapus kelas invalid dari validasi sebelumnya
        let value = input.value.trim();
        if (input.type === "radio" || input.type === "checkbox") {
          // Khusus untuk radio/checkbox, cek apakah ada yang terpilih dalam grup
          if (input.name) {
            const group = formOrderKost.querySelectorAll(`input[name="${input.name}"]`);
            const isGroupChecked = Array.from(group).some((rb) => rb.checked);
            if (!isGroupChecked && input.required) {
              isValid = false;
              if (!firstInvalidField) firstInvalidField = input; // Ambil elemen pertama yang tidak valid
            }
          }
        } else if (!value) {
          isValid = false;
          input.classList.add("is-invalid"); // Tandai input yang tidak valid
          if (!firstInvalidField) firstInvalidField = input;
        }
      });

      if (!isValid) {
        showToast("Harap lengkapi semua field yang wajib diisi dan ditandai.", "error");
        if (firstInvalidField) firstInvalidField.focus();
        return;
      }

      // Ambil data untuk dikirim
      const orderData = {
        idKost: document.getElementById("hiddenIdKost")?.value || "N/A",
        fullName: document.getElementById("fullName").value,
        email: document.getElementById("email").value,
        phoneNumber: document.getElementById("phoneNumber").value,
        checkInDate: document.getElementById("checkInDate").value,
        duration: document.getElementById("duration").value,
        totalPrice: parseFloat(totalPriceDisplayEl?.textContent.replace(/\D/g, "")) || 0, // Pastikan ini angka bersih
        paymentMethod: document.querySelector('input[name="paymentMethod"]:checked')?.value || "",
        notes: document.getElementById("notes").value,
        // Tambahkan data kost yang mungkin dibutuhkan di backend untuk session
        kostName: summaryKostNameEl?.textContent || "Kost Tidak Diketahui",
        kostAddress: summaryKostAddressEl?.textContent || "Alamat Kost Tidak Tersedia",
      };

      showToast("Pesanan Anda sedang diproses...", "info");

      // Kirim data ke orderkost_data.php
      fetch("../admin/orderkost_data.php", {
        // Pastikan path ke script backend ini benar
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json", // Mengharapkan respons JSON dari server
        },
        body: JSON.stringify(orderData), // Kirim objek data sebagai JSON string
      })
        .then((response) => {
          // Cek jika respons HTTP tidak OK (misal 404, 500)
          if (!response.ok) {
            // Coba parse respons sebagai JSON untuk mendapatkan pesan error dari server
            return response
              .json()
              .then((errorData) => {
                throw new Error(errorData.message || `Server error: ${response.status}`);
              })
              .catch(() => {
                // Jika tidak bisa parse JSON, fallback ke pesan error generik
                throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
              });
          }
          return response.json(); // Jika respons OK, parse JSON
        })
        .then((data) => {
          if (data.success) {
            showToast("Pemesanan berhasil! Mengalihkan ke halaman sukses...", "success");
            // Redirect ke halaman sukses setelah menerima respons sukses dari server
            window.location.href = "order_kost_sukses.php";
          } else {
            // Tampilkan pesan error dari server jika `success` false
            showToast("Gagal memproses pesanan: " + (data.message || "Terjadi kesalahan yang tidak diketahui."), "error");
          }
        })
        .catch((error) => {
          console.error("Error saat mengirim pesanan:", error);
          showToast("Terjadi kesalahan koneksi atau server: " + error.message, "error", 5000);
        });
    });
  }
});
