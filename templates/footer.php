<footer class="footer-app">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <div class="footer-brand">
                    <img src="../assets/logo/iconapk.png" alt="Logo Small" class="footer-logo">
                    <span class="fw-bold">Uang Sekolah</span>
                </div>
                <p class="copyright-text mb-0">
                    &copy; 2024 - <?= date('Y'); ?> Versi 1.0.0. Seluruh Hak Cipta Dilindungi.
                </p>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <div class="footer-links">
                    <a href="#" class="footer-link"><i class="fa-solid fa-circle-info me-1"></i> Bantuan</a>
                    <a href="#" class="footer-link"><i class="fa-solid fa-shield-halved me-1"></i> Kebijakan</a>
                    <span class="status-indicator">
                        <span class="dot"></span> Sistem Online
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Styling Footer */
    .footer-app {
        background-color: #ffffff;
        padding: 30px 0;
        margin-top: 50px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
    }

    .footer-logo {
        height: 25px;
        margin-right: 10px;
        opacity: 0.8;
    }

    .footer-brand {
        color: #1e293b;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (min-width: 768px) {
        .footer-brand {
            justify-content: flex-start;
        }
    }

    .copyright-text {
        font-size: 13px;
        color: #94a3b8;
    }

    .footer-link {
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        margin: 0 15px;
        transition: color 0.2s;
    }

    .footer-link:hover {
        color: #4f46e5;
    }

    .status-indicator {
        font-size: 13px;
        background: #f0fdf4;
        color: #16a34a;
        padding: 5px 12px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        margin-left: 10px;
    }

    .dot {
        height: 8px;
        width: 8px;
        background-color: #22c55e;
        border-radius: 50%;
        display: inline-block;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.3; }
        100% { opacity: 1; }
    }

    /* Penyesuaian agar footer menempel di bawah jika konten sedikit */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-content {
        flex: 1;
    }
</style>