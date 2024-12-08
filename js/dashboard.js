    
function toggleMenu() {
    const menu = document.querySelector("nav ul");
    menu.classList.toggle("show");
  }
  
  // Script for the slider functionality
  // Script for slider functionality
  let currentSlide = 0;
  
  function moveSlide(direction) {
  const slides = document.querySelector('.slides');
  const slideWidth = slides.children[0].offsetWidth + 15; // Tambahkan gap antar-slide
  const totalSlides = slides.children.length;
  const maxSlide = totalSlides - Math.floor(slides.offsetWidth / slideWidth);
  
  // Update posisi slide secara melingkar
  currentSlide += direction;
  
  if (currentSlide < 0) {
  currentSlide = maxSlide; // Jika di awal, lompat ke akhir
  } else if (currentSlide > maxSlide) {
  currentSlide = 0; // Jika di akhir, lompat ke awal
  }
  
  // Terapkan transformasi untuk menggeser slider
  slides.style.transform = `translateX(-${currentSlide * slideWidth}px)`;
  }
  
  // Mengaktifkan dropdown saat diklik
  document.addEventListener('DOMContentLoaded', () => {
  const dropdownToggles = document.querySelectorAll('.dropdown > a');
  
  dropdownToggles.forEach(toggle => {
  toggle.addEventListener('click', (e) => {
    e.preventDefault(); // Mencegah aksi default link
    
    const dropdownMenu = toggle.nextElementSibling;
    const isOpen = dropdownMenu.classList.contains('open');
  
    // Tutup semua dropdown
    closeAllDropdowns();
  
    // Tampilkan dropdown jika tidak sedang terbuka
    if (!isOpen) {
      dropdownMenu.classList.add('open');
    }
  });
  });
  
  // Menutup dropdown jika klik di luar area dropdown
  document.addEventListener('click', (e) => {
  if (!e.target.closest('.dropdown')) {
    closeAllDropdowns();
  }
  });
  
  // Fungsi untuk menutup semua dropdown
  function closeAllDropdowns() {
  document.querySelectorAll('.dropdown-menu').forEach(menu => {
    menu.classList.remove('open');
  });
  }
  });
  