// ============================================================
//  Foreign Links Campus – Admin Panel JavaScript
//  /assets/js/admin.js
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar toggle (mobile) ───────────────────────────
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        // Close sidebar when clicking outside
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // ── Auto-dismiss flash alerts after 5s ───────────────
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // ── Select all checkboxes ─────────────────────────────
    window.toggleAll = function (master) {
        document.querySelectorAll('.row-check')
            .forEach(cb => cb.checked = master.checked);
    };

    // ── Confirm dangerous actions ─────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // ── Highlight active sidebar link ─────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-item').forEach(function (link) {
        if (link.getAttribute('href') &&
            currentPath.includes(link.getAttribute('href').split('/').pop())) {
            link.classList.add('active');
        }
    });

    // ── Status filter quick tabs ──────────────────────────
    document.querySelectorAll('.qst').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.qst').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ── Auto-submit filter form on select change ──────────
    document.querySelectorAll('.filter-form select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });

    // ── Table row click to review ─────────────────────────
    document.querySelectorAll('.admin-table tbody tr[data-href]').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function (e) {
            if (!e.target.closest('a, button, input')) {
                window.location = this.dataset.href;
            }
        });
    });

    // ── Copy to clipboard utility ─────────────────────────
    window.copyText = function (text) {
        navigator.clipboard.writeText(text).then(function () {
            showToast('Copied: ' + text);
        }).catch(function () {
            prompt('Copy this:', text);
        });
    };

    // ── Simple toast notification ─────────────────────────
    window.showToast = function (msg) {
        const toast = document.createElement('div');
        toast.textContent = msg;
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#0d3b2e;color:#fff;' +
            'padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;' +
            'z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,0.2);';
        document.body.appendChild(toast);
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    };

    // ── Print slip shortcut ───────────────────────────────
    const printBtn = document.getElementById('adminPrintBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    // ── Stat card counter animation ───────────────────────
    document.querySelectorAll('.sc-number').forEach(function (el) {
        const target = parseInt(el.textContent.replace(/,/g, '')) || 0;
        if (target === 0) return;
        let current = 0;
        const step  = Math.ceil(target / 40);
        const timer = setInterval(function () {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString();
            if (current >= target) clearInterval(timer);
        }, 30);
    });

});
