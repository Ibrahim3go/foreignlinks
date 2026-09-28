<?php
// ============================================================
//  Foreign Links Campus – Shared Footer
//  /includes/footer.php
// ============================================================
?>

<!-- ── Footer ──────────────────────────────────────────────── -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Column 1 – About -->
            <div class="footer-col">
                <div class="footer-brand">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png"
                         alt="<?= SITE_NAME ?>" class="footer-logo"
                         onerror="this.style.display='none'">
                    <span><?= SITE_NAME ?></span>
                </div>
                <p class="footer-about">
                    A premier multi-school educational campus in Moro, Ife-North,
                    Osun State, offering Health Technology, Education, Nursing,
                    Degree and International Transfer programmes.
                </p>
                <p class="footer-accred">
                    Accredited by <strong>NBTE</strong> &amp; <strong>NCCE</strong>
                </p>
            </div>

            <!-- Column 2 – Quick Links -->
            <div class="footer-col">
                <h4 class="footer-heading">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/apply.php">Apply Now</a></li>
                    <li><a href="<?= BASE_URL ?>/login.php">Applicant Login</a></li>
                    <li><a href="<?= BASE_URL ?>/status.php">Check Application Status</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php#about">About the Campus</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php#contact">Contact Us</a></li>
                </ul>
            </div>

            <!-- Column 3 – Programmes -->
            <div class="footer-col">
                <h4 class="footer-heading">Our Programmes</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/index.php?college=COE">College of Education (NCE)</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=CHT">College of Health Technology</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=SON">School of Nursing</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=ADP">Affiliate Degree Programmes</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=FUL">Foreign University Linkage</a></li>
                </ul>
            </div>

            <!-- Column 4 – Contact -->
            <div class="footer-col">
                <h4 class="footer-heading">Contact Us</h4>
                <ul class="footer-contact">
                    <li>
                        <span class="contact-icon">📍</span>
                        <span>Osogbo Road, Moro, Ife-North LGA, Osun State, Nigeria</span>
                    </li>
                    <li>
                        <span class="contact-icon">📞</span>
                        <a href="tel:+2348033705261"><?= SITE_PHONE ?></a>
                    </li>
                    <li>
                        <span class="contact-icon">📞</span>
                        <a href="tel:+2347071578491">+234 707 157 8491</a>
                    </li>
                    <li>
                        <span class="contact-icon">✉️</span>
                        <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
                    </li>
                </ul>
                <div class="footer-provost">
                    <small>Provost: <strong><?= PROVOST_NAME ?></strong></small>
                </div>
            </div>

        </div><!-- end footer-grid -->

        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <p>
                &copy; <?= date('Y') ?> <?= SITE_NAME ?>, Moro. All rights reserved.
                &nbsp;|&nbsp; <?= SITE_SESSION ?> Admission Session
            </p>
            <p class="footer-disclaimer">
                This portal is the official admission platform of Foreign Links Campus.
                For support, contact the ICT unit.
            </p>
        </div>

    </div>
</footer>
<!-- End Footer -->

<!-- Main JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extraJS)) echo $extraJS; ?>

</body>
</html>
