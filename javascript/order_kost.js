document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formOrderKost");
  if (!form) return;

  const durationSelect = document.getElementById("duration");
  const basePriceEl = document.getElementById("hiddenBasePrice");
  const basePrice = basePriceEl ? parseFloat(basePriceEl.value) : 0;

  const summarySubtotalEl = document.getElementById("summarySubtotal");
  const summaryDiskonWrapperEl = document.getElementById("summaryDiskonWrapper");
  const summaryDiskonLabelEl = document.getElementById("summaryDiskonLabel");
  const summaryDiskonNilaiEl = document.getElementById("summaryDiskonNilai");
  const totalPriceDisplayEl = document.getElementById("totalPriceDisplay");
  const submitButton = document.getElementById("submitOrderLink");

  const formatRupiah = (angka) => {
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(angka);
  };

  function calculateAndUpdatePrice() {
    if (!durationSelect || !summarySubtotalEl || !totalPriceDisplayEl) return;

    const selectedOption = durationSelect.options[durationSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
      summarySubtotalEl.textContent = formatRupiah(0);
      totalPriceDisplayEl.textContent = formatRupiah(0);
      summaryDiskonWrapperEl.style.display = "none";
      return;
    }

    const durationValue = parseInt(selectedOption.value);
    const diskonPersen = parseFloat(selectedOption.getAttribute("data-diskon")) || 0;

    const subtotal = basePrice * durationValue;
    const nilaiDiskon = subtotal * (diskonPersen / 100);
    const totalCalculatedPrice = subtotal - nilaiDiskon;

    summarySubtotalEl.textContent = formatRupiah(subtotal);
    totalPriceDisplayEl.textContent = formatRupiah(totalCalculatedPrice);

    if (diskonPersen > 0) {
      summaryDiskonLabelEl.textContent = `Diskon (${diskonPersen}%)`;
      summaryDiskonNilaiEl.textContent = `- ${formatRupiah(nilaiDiskon)}`;
      summaryDiskonWrapperEl.style.display = "flex";
    } else {
      summaryDiskonWrapperEl.style.display = "none";
    }
  }

  if (durationSelect) {
    durationSelect.addEventListener("change", calculateAndUpdatePrice);
    calculateAndUpdatePrice();
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    if (!form.checkValidity()) {
      event.stopPropagation();
      form.classList.add("was-validated");
      return;
    }

    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';

    const selectedOption = durationSelect.options[durationSelect.selectedIndex];
    const subtotal = basePrice * parseInt(selectedOption.value);
    const diskon = subtotal * (parseFloat(selectedOption.getAttribute("data-diskon")) / 100);

    const orderData = {
      idKost: document.getElementById("hiddenIdKost")?.value,
      fullName: document.getElementById("fullName").value,
      email: document.getElementById("email").value,
      phoneNumber: document.getElementById("phoneNumber").value,
      checkInDate: document.getElementById("checkInDate").value,
      duration: document.getElementById("duration").value,
      totalPrice: subtotal - diskon,
      paymentMethod: document.querySelector('input[name="paymentMethod"]:checked')?.value,
      notes: document.getElementById("notes").value,
      kostName: document.getElementById("summaryKostName")?.textContent,
      kostAddress: document.getElementById("summaryKostAddress")?.textContent,
    };

    fetch("order_kost.php", {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify(orderData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          window.location.href = "order_kost_sukses.php";
        } else {
          alert("Gagal: " + (data.message || "Terjadi kesalahan."));
          submitButton.disabled = false;
          submitButton.textContent = "Pesan Kost";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Kesalahan koneksi atau server.");
        submitButton.disabled = false;
        submitButton.textContent = "Pesan Kost";
      });
  });
});
