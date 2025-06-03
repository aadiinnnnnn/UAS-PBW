document.addEventListener("DOMContentLoaded", function () {
  let orderDetails = null;

  // Prioritas 1: Coba ambil data dari variabel global `phpOrderDetails` (dari session PHP)
  if (typeof phpOrderDetails !== "undefined" && phpOrderDetails !== null && Object.keys(phpOrderDetails).length > 0) {
    //
    orderDetails = phpOrderDetails; //
    console.log("Order details loaded from PHP session (via global JS variable):", orderDetails); //
    localStorage.removeItem("latestKostOrderDetails"); // Bersihkan localStorage jika data dari session valid
  } else {
    // Prioritas 2: Jika tidak ada dari session, coba dari localStorage (fallback)
    const storedOrderDetails = localStorage.getItem("latestKostOrderDetails"); //
    if (storedOrderDetails) {
      //
      try {
        orderDetails = JSON.parse(storedOrderDetails); //
        console.log("Order details loaded from localStorage (fallback):", orderDetails); //
        localStorage.removeItem("latestKostOrderDetails"); // Hapus dari localStorage setelah dibaca
      } catch (e) {
        console.error("Gagal parse orderDetails dari localStorage:", e); //
      }
    }
  }

  const orderIdEl = document.getElementById("orderId"); //
  const totalBiayaEl = document.getElementById("totalBiaya"); //
  const metodePembayaranEl = document.getElementById("metodePembayaran"); //
  const tanggalSewaEl = document.getElementById("tanggalSewa"); //
  const durasiSewaDisplayEl = document.getElementById("durasiSewaDisplay"); //
  const summaryKostNameEl = document.getElementById("summaryKostName"); //
  const summaryKostAddressEl = document.getElementById("summaryKostAddress"); //
  const catatanTambahanEl = document.getElementById("catatanTambahan"); //

  if (orderDetails) {
    //
    if (orderIdEl) {
      //
      orderIdEl.textContent = orderDetails.orderId ? `${orderDetails.orderId}` : "Tidak Tersedia"; //
    }
    if (summaryKostNameEl) {
      //
      summaryKostNameEl.textContent = orderDetails.kostName || "Tidak Diketahui"; //
    }
    if (summaryKostAddressEl) {
      //
      summaryKostAddressEl.textContent = orderDetails.kostAddress || "Tidak Diketahui"; //
    }

    if (tanggalSewaEl && orderDetails.checkInDate) {
      //
      try {
        const date = new Date(orderDetails.checkInDate + "T00:00:00"); //
        if (!isNaN(date.getTime())) {
          //
          const options = { day: "numeric", month: "long", year: "numeric", timeZone: "UTC" }; //
          tanggalSewaEl.textContent = date.toLocaleDateString("id-ID", options); //
        } else {
          tanggalSewaEl.textContent = orderDetails.checkInDate; //
        }
      } catch (e) {
        console.error("Error parsing tanggal sewa:", e); //
        tanggalSewaEl.textContent = "Tidak Tersedia"; //
      }
    } else if (tanggalSewaEl) {
      //
      tanggalSewaEl.textContent = "Tidak Tersedia"; //
    }

    if (durasiSewaDisplayEl) {
      //
      durasiSewaDisplayEl.textContent = orderDetails.durationText || orderDetails.displayPricePerDuration || "Tidak Diketahui"; //
    }

    if (totalBiayaEl) {
      //
      totalBiayaEl.textContent = orderDetails.totalPrice !== undefined ? parseFloat(orderDetails.totalPrice).toLocaleString("id-ID") : "Tidak Diketahui"; //
    }

    if (metodePembayaranEl && orderDetails.paymentMethod) {
      //
      let paymentMethodText = orderDetails.paymentMethod.replace(/_/g, " "); //
      paymentMethodText = paymentMethodText //
        .toLowerCase() //
        .split(" ") //
        .map((s) => s.charAt(0).toUpperCase() + s.substring(1)) //
        .join(" "); //
      if (orderDetails.paymentMethod.toLowerCase() === "bank transfer") {
        //
        paymentMethodText = "Bank Transfer (BCA, Mandiri, BRI)"; //
      } else if (orderDetails.paymentMethod.toLowerCase() === "cash") {
        //
        paymentMethodText = "Cash (Bayar di Tempat)"; //
      }
      metodePembayaranEl.textContent = paymentMethodText; //
    } else if (metodePembayaranEl) {
      //
      metodePembayaranEl.textContent = "Tidak Diketahui"; //
    }

    if (catatanTambahanEl) {
      //
      catatanTambahanEl.textContent = orderDetails.notes && orderDetails.notes.trim() !== "" ? orderDetails.notes : "-"; //
    }
  } else {
    const defaultText = "Tidak Dapat Dimuat"; //
    if (orderIdEl) orderIdEl.textContent = defaultText; //
    if (totalBiayaEl) totalBiayaEl.textContent = defaultText; //
    if (metodePembayaranEl) metodePembayaranEl.textContent = defaultText; //
    if (tanggalSewaEl) tanggalSewaEl.textContent = defaultText; //
    if (durasiSewaDisplayEl) durasiSewaDisplayEl.textContent = defaultText; //
    if (summaryKostNameEl) summaryKostNameEl.textContent = defaultText; //
    if (summaryKostAddressEl) summaryKostAddressEl.textContent = defaultText; //
    if (catatanTambahanEl) catatanTambahanEl.textContent = "-"; //

    console.warn("Tidak ada detail pesanan ditemukan dari session maupun localStorage untuk ditampilkan."); //
    const successCard = document.querySelector(".success-card-standalone"); //
    if (successCard) {
      //
      const detailsDiv = successCard.querySelector(".order-details-summary-standalone"); //
      const pError = document.createElement("p"); //
      pError.innerHTML = '<strong style="color:red; margin-top:15px; display:block;">Gagal memuat detail pesanan. Silakan cek riwayat pesanan Anda atau hubungi customer service.</strong>'; //
      if (detailsDiv) {
        //
        detailsDiv.style.display = "none"; // Sembunyikan detail jika error
        detailsDiv.insertAdjacentElement("afterend", pError); //
      } else {
        //
        successCard.appendChild(pError); //
      }
    }
  }
  console.log("Halaman Pesanan Kost Berhasil (order-kost-sukses.js) selesai dimuat."); //
});
