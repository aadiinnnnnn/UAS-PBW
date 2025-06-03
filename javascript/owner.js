document.addEventListener("DOMContentLoaded", function () {
  // Set tahun sekarang di footer
  const tahunElement = document.getElementById("tahunSekarangDashboard");
  if (tahunElement) {
    tahunElement.textContent = new Date().getFullYear();
  }
  document.querySelectorAll('#navbarNavOwnerDashboard a.nav-link[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");
      try {
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          const headerOffset = document.querySelector(".header-custom.sticky-top")?.offsetHeight || 70; // Sesuaikan jika perlu
          const elementPosition = targetElement.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
          });
        }
      } catch (error) {
        console.warn("Smooth scroll target not found or invalid selector:", targetId, error);
      }
    });
  });
});
