// ============================================================
//  Foreign Links Campus – Main JavaScript  /assets/js/main.js
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // ── Navbar scroll effect
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        window.addEventListener('scroll', function () {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        });
    }

    // ── Mobile nav toggle
    const toggle   = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (toggle && navLinks) {
        toggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (navbar && !navbar.contains(e.target)) {
                navLinks.classList.remove('open');
            }
        });
    }

    // ── College filter tabs
    window.filterColleges = function (code) {
        const cards   = document.querySelectorAll('.college-card');
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        buttons.forEach(btn => {
            const oc = btn.getAttribute('onclick') || '';
            if (oc.includes("'" + code + "'")) btn.classList.add('active');
        });
        cards.forEach(card => {
            card.style.display = (code === 'all' || card.dataset.college === code) ? 'flex' : 'none';
        });
    };

    // ── Auto-dismiss flash alerts after 5 s
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // ── Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (navLinks) navLinks.classList.remove('open');
            }
        });
    });

});
