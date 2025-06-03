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
    formOrderKost.addEventListener("submit", function (event) {
      event.preventDefault();

      const termsAgreement = document.getElementById("termsAgreement");
      if (!termsAgreement.checked) {
        showToast("Anda harus menyetujui Syarat & Ketentuan Pemesanan.", "warning");
        termsAgreement.focus();
        return;
      }

      let isValid = true;
      let firstInvalidField = null;
      formOrderKost.querySelectorAll("[required]").forEach((input) => {
        input.classList.remove("is-invalid");
        let value = input.value.trim();
        if (input.type === "radio" || input.type === "checkbox") {
          if (input.name) {
            const group = formOrderKost.querySelectorAll(`input[name="${input.name}"]`);
            const isGroupChecked = Array.from(group).some((rb) => rb.checked);
            if (!isGroupChecked && input.required) {
              isValid = false;
              if (!firstInvalidField) firstInvalidField = input;
            }
          }
        } else if (!value) {
          isValid = false;
          input.classList.add("is-invalid");
          if (!firstInvalidField) firstInvalidField = input;
        }
      });

      if (!isValid) {
        showToast("Harap lengkapi semua field yang wajib diisi dan ditandai.", "error");
        if (firstInvalidField) firstInvalidField.focus();
        return;
      }

      const formData = new FormData(formOrderKost);
      const orderData = {};
      formData.forEach((value, key) => {
        orderData[key] = value;
      });

      orderData.idKost = document.getElementById("hiddenIdKost")?.value || "N/A";
      orderData.kostName = summaryKostNameEl?.textContent || "Kost Tidak Diketahui";
      orderData.kostAddress = summaryKostAddressEl?.textContent || "Alamat Kost Tidak Tersedia";
      orderData.basePrice = basePriceKost;
      orderData.totalPrice = parseFloat(totalPriceDisplayEl?.textContent.replace(/\D/g, "")) || 0;
      orderData.displayPricePerDuration = pricePerDurationDisplayEl?.textContent || "-";

      const simulatedOrderId = "KOSTORDER-" + Math.floor(Math.random() * 899999 + 100000);
      orderData.orderId = simulatedOrderId;

      console.log("Data Pemesanan Kost (akan dikirim ke backend):", orderData);
      showToast("Pesanan Anda sedang diproses...", "info");

      // SIMULASI PENGIRIMAN KE BACKEND (dan pengalihan)
      // Jika Anda sudah mengimplementasikan orderkost_data.php untuk memproses dan menyimpan ke DB,
      // Anda akan mengganti blok setTimeout ini dengan fetch() ke orderkost_data.php.
      setTimeout(() => {
        // Anggap ini adalah respons dari orderkost_data.php
        const backendResponse = {
          success: true, // Ini akan true jika orderkost_data.php berhasil menyimpan
          message: "Pemesanan kost berhasil!", // Pesan dari orderkost_data.php
          orderId: orderData.orderId, // ID dari orderkost_data.php (hasil $stmt->insert_id)
        };

        if (backendResponse.success) {
          // Data yang akan dikirim ke halaman sukses (via localStorage atau session yang di-set oleh backend)
          // orderkost_data.php sudah mengatur $_SESSION['latestKostOrderDetails']
          // Jadi, localStorage ini berfungsi sebagai fallback atau jika Anda belum sepenuhnya
          // mengandalkan session yang di-set oleh orderkost_data.php.
          // Jika orderkost_data.php sudah benar mengisi session, baris localStorage.setItem ini bisa di-skip.
          localStorage.setItem(
            "latestKostOrderDetails",
            JSON.stringify({
              ...orderData, // Data dari form
              // Tambahkan data yang mungkin dikembalikan oleh backend jika ada,
              // misalnya ID pesanan asli jika berbeda dari yang disimulasikan.
              // Di sini, orderData sudah mencakup simulatedOrderId.
              // Jika backendResponse.orderId adalah ID asli dari DB, gunakan itu.
              orderId: backendResponse.orderId, // Gunakan orderId dari respons backend
              serverMessage: backendResponse.message,
            })
          );

          // === PERBAIKAN UTAMA ADA DI SINI ===
          window.location.href = "order_kost_sukses.php";
          // =================================
        } else {
          showToast("Gagal memproses pesanan: " + (backendResponse.message || "Silakan coba lagi."), "error");
        }
      }, 1500); // Mengurangi delay untuk testing, sesuaikan jika perlu
    });
  }
});
