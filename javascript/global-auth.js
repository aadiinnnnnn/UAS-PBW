document.addEventListener("DOMContentLoaded", function () {
  // Ambil status login dan username dari variabel yang di-set PHP
  const isLoggedIn = typeof isLoggedIn_php !== "undefined" ? isLoggedIn_php : false;
  const loggedInUsername = typeof loggedInUsername_php !== "undefined" ? loggedInUsername_php : "";
  const currentPage = typeof currentPage_php !== "undefined" ? currentPage_php : window.location.pathname.split("/").pop();
  const currentDir = typeof currentDir_php !== "undefined" ? currentDir_php : "";

  // Fungsi untuk mengupdate navbar berdasarkan status login
  function updateNavbar(isLoggedInStatus, username = "") {
    const navbarElement = document.querySelector(".navbar");
    if (!navbarElement) {
      console.warn("Elemen .navbar tidak ditemukan. Navbar tidak akan diupdate oleh global-auth.js.");
      return;
    }

    const navMenu = navbarElement.querySelector(".nav-menu");
    if (!navMenu) {
      console.warn("Elemen .nav-menu di dalam .navbar tidak ditemukan. Navbar tidak akan diupdate oleh global-auth.js.");
      return;
    }

    let navMenuHTML = "";

    // Path untuk gambar profil. Menggunakan path absolut dari root server.
    // Berdasarkan file indexuser.php
    const profileIconHTML = `
            <div class="profile-icon">
                <img src="/assets/img/red-truck.png" alt="User Profile" />
            </div>`;

    const aboutContactHTML = `
            <a href="#">About</a>
            <a href="#">Contact</a>
        `;

    if (isLoggedInStatus) {
      // Pengguna sudah login
      navMenuHTML = `
                ${profileIconHTML}
                <span class="nav-link-text" style="color:white; margin-right:10px; margin-left:5px;">Halo, ${username}!</span>
                ${aboutContactHTML}
                <a href="order.php">
                    <button class="order-btn">Order <span class="arrow">▶</span></button>
                </a>
                <a href="logout.php">
                    <button class="logout-btn" id="logoutButtonJs">Logout</button>
                </a>
            `;
    } else {
      // Pengguna belum login
      // Tombol login mengarah ke login.php di direktori yang sama (admin)
      // Tombol order juga mengarah ke order.php (akan dilindungi oleh session.php di server)
      navMenuHTML = `
                <a href="login.php">
                    <button class="login-btn">Login</button>
                </a>
                ${aboutContactHTML}
                <a href="order.php">
                    <button class="order-btn">Order <span class="arrow">▶</span></button>
                </a>
            `;
    }
    navMenu.innerHTML = navMenuHTML;

    // Tambahkan event listener untuk tombol logout jika pengguna login
    if (isLoggedInStatus) {
      const logoutButton = document.getElementById("logoutButtonJs"); // Menggunakan ID unik untuk JS
      if (logoutButton) {
        logoutButton.addEventListener("click", function (e) {
          e.preventDefault();
          // Arahkan ke skrip logout PHP untuk menghancurkan sesi server
          window.location.href = "logout.php"; //
        });
      }
    }
  }

  // --- Panggil updateNavbar ---
  // Jika Anda ingin JavaScript yang mengatur tampilan navbar secara dinamis, panggil fungsi ini.
  // Jika PHP sudah mengatur navbar dengan benar berdasarkan sesi, baris ini bisa di-comment atau dihapus,
  // dan JavaScript hanya akan menambahkan event listener ke tombol logout yang sudah dirender PHP.
  updateNavbar(isLoggedIn, loggedInUsername);

  // --- Logika Redirect Client-Side (Opsional, sebagai pelengkap session.php) ---
  // File-file seperti indexuser.php, order.php, login.php ada di direktori 'admin'.
  const publicPages = ["index.php", "login.php", "registrasi.php"];
  const protectedPages = ["indexuser.php", "order.php"];

  // Hanya jalankan logika redirect jika halaman saat ini ada di dalam direktori 'admin'
  // dan bukan merupakan file utility seperti koneksi.php atau session.php itu sendiri.
  const relevantPagesForRedirect = [...publicPages, ...protectedPages];

  if (currentDir === "admin" && relevantPagesForRedirect.includes(currentPage)) {
    if (isLoggedIn) {
      // Jika sudah login:
      // Arahkan ke indexuser.php jika pengguna berada di halaman login atau registrasi.
      if (currentPage === "login.php" || currentPage === "registrasi.php") {
        window.location.href = "indexuser.php"; // Halaman utama setelah login
      }
    } else {
      // Jika belum login:
      // Arahkan ke login.php jika mencoba akses halaman terproteksi.
      // Ini adalah fallback, karena session.php di server seharusnya sudah melakukan ini.
      if (protectedPages.includes(currentPage)) {
        window.location.href = "login.php"; //
      }
    }
  }
});
