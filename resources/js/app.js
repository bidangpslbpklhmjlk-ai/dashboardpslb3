import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
import Chart from 'chart.js/auto';
window.Chart = Chart;

// --- Tambahkan Kode Kustom Scroll Reveal Ini ---
document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal');

    const revealOnScroll = () => {
        reveals.forEach((element) => {
            const windowHeight = window.innerHeight;
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 100; // Jarak dalam piksel sebelum elemen muncul

            if (elementTop < windowHeight - elementVisible) {
                element.classList.add('visible');
            }
        });
    };

    // Jalankan sekali saat load untuk memunculkan elemen yang berada di bagian atas layar
    revealOnScroll();
    
    // Jalankan setiap kali user melakukan scroll
    window.addEventListener('scroll', revealOnScroll);
});