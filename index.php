<?php
// ============================================================
//  Foreign Links Campus – Homepage
//  /index.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$pageTitle = 'Welcome – Official Admission Portal';
$bodyClass = 'page-home';

$colleges = getColleges($pdo);

$stats = [
    ['number' => '21,387+', 'label' => 'Students Admitted'],
    ['number' => '50+',     'label' => 'Accredited Programmes'],
    ['number' => '5',       'label' => 'Colleges & Schools'],
    ['number' => '95%',     'label' => 'University Progression'],
];

$collegeDetails = [
    'COE' => [
        'icon'  => '🎓',
        'color' => 'green',
        'desc'  => 'NCE programmes in Education, accredited by NCCE. Train as a professional teacher across multiple subject combinations.',
        'programmes' => ['Business Education','Computer Education','English Studies','Mathematics Education','+10 more'],
    ],
    'CHT' => [
        'icon'  => '🏥',
        'color' => 'blue',
        'desc'  => 'ND, HND, Diploma and Certificate programmes in Health Technology, accredited by NBTE.',
        'programmes' => ['Community Health','Medical Lab Technology','Pharmacy Technician','Environmental Health','+19 more'],
    ],
    'SON' => [
        'icon'  => '💉',
        'color' => 'red',
        'desc'  => 'Professional nursing programme with entrance examination, clinical training and RN certification.',
        'programmes' => ['General Nursing (RN)'],
    ],
    'ADP' => [
        'icon'  => '📚',
        'color' => 'purple',
        'desc'  => 'Undergraduate B.Sc degrees run in affiliation with Ambrose Alli University and Kwara State University.',
        'programmes' => ['B.Sc Accounting','B.Sc Public Administration','B.Sc Computer Science & Management','+9 more'],
    ],
    'FUL' => [
        'icon'  => '✈️',
        'color' => 'gold',
        'desc'  => 'International transfer and admission facilitation to universities in Ghana, Poland, Canada, UK and more.',
        'programmes' => ['B.Sc Nursing','B.Sc Pharmacy','M.Sc Computer Science','M.Sc Economics','+6 more'],
    ],
];

$filterCollege = clean($_GET['college'] ?? '');

require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════ -->
<section class="hero" id="hero">
    <div class="hero-bg">
        <div class="hero-overlay"></div>
        <div class="hero-pattern"></div>
    </div>
    <div class="container hero-content">
        <div class="hero-badge animate-fade-in">
            <span class="badge-dot"></span>
            <?= SITE_SESSION ?> Admission — Now Open
        </div>
        <h1 class="hero-title animate-slide-up">
            Your Future Starts<br>
            <span class="hero-title-accent">Right Here.</span>
        </h1>
        <p class="hero-subtitle animate-slide-up delay-1">
            Foreign Links Campus, Moro — offering world-class Health Technology,
            Education, Nursing, Degree and International Transfer programmes
            in the heart of Osun State, Nigeria.
        </p>
        <div class="hero-actions animate-slide-up delay-2">
            <?php if (ADMISSION_OPEN): ?>
            <a href="<?= BASE_URL ?>/apply.php" class="btn btn-primary btn-lg">
                Start Your Application <span class="btn-arrow">→</span>
            </a>
            <?php endif; ?>
            <a href="#programmes" class="btn btn-outline-white btn-lg">
                View Programmes
            </a>
        </div>
        <div class="hero-meta animate-fade-in delay-3">
            <span>📍 Moro, Ife-North, Osun State</span>
            <span>•</span>
            <span>Accredited by NBTE &amp; NCCE</span>
            <span>•</span>
            <span>Provost: <?= PROVOST_NAME ?></span>
        </div>
    </div>
    <div class="hero-scroll-hint">
        <span>Scroll to explore</span>
        <div class="scroll-arrow">↓</div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     STATS BAR
══════════════════════════════════════════════════ -->
<section class="stats-bar">
    <div class="container stats-grid">
        <?php foreach ($stats as $stat): ?>
        <div class="stat-item">
            <span class="stat-number"><?= e($stat['number']) ?></span>
            <span class="stat-label"><?= e($stat['label']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     ABOUT
══════════════════════════════════════════════════ -->
<section class="about-section section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <span class="section-label">About the Campus</span>
                <h2 class="section-title">A Multi-School Campus<br>Built for Excellence</h2>
                <p>
                    Foreign Links Campus is a private multi-school educational institution
                    located along Osogbo Road, Moro, Ife-North Local Government Area,
                    Osun State, Nigeria.
                </p>
                <p>
                    The campus operates five distinct educational divisions — from professional
                    health technology programmes to NCE teacher education, nursing, affiliate
                    degree programmes, and international university transfer pathways covering
                    Ghana, Kenya, South Africa, Poland, Europe, America and Canada.
                </p>
                <div class="about-highlights">
                    <div class="highlight-item">
                        <span class="highlight-icon">✓</span>
                        <span>NBTE &amp; NCCE Accredited Programmes</span>
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-icon">✓</span>
                        <span>Affiliated with Ambrose Alli University &amp; KWASU</span>
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-icon">✓</span>
                        <span>International University Linkage Directorate</span>
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-icon">✓</span>
                        <span>1,500+ Fresh Students Matriculated in 2024/2025</span>
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-icon">✓</span>
                        <span>21,387+ Students Admitted Historically</span>
                    </div>
                </div>
                <a href="#programmes" class="btn btn-primary mt-2">Explore Programmes</a>
            </div>
            <div class="about-image-block">
                <div class="about-img-card main-card">
                    <img src="<?= BASE_URL ?>/assets/images/campus.jpg"
                         alt="Foreign Links Campus, Moro"
                         onerror="this.parentElement.classList.add('img-placeholder')">
                    <div class="img-caption">Foreign Links Campus, Moro — Osogbo Road, Ife-North</div>
                </div>
                <div class="about-badge-card">
                    <span class="badge-big-number">4.3</span>
                    <span class="badge-big-label">Google Rating</span>
                    <span class="badge-stars">★★★★☆</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     COLLEGES / PROGRAMMES
══════════════════════════════════════════════════ -->
<section class="programmes-section section" id="programmes">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">What We Offer</span>
            <h2 class="section-title">Our Colleges &amp; Schools</h2>
            <p class="section-desc">
                Five distinct educational pathways — choose the one that matches your career goals.
            </p>
        </div>

        <div class="college-tabs">
            <button class="tab-btn <?= !$filterCollege ? 'active' : '' ?>"
                    onclick="filterColleges('all')">All</button>
            <?php foreach ($colleges as $col): ?>
            <button class="tab-btn <?= $filterCollege === $col['code'] ? 'active' : '' ?>"
                    onclick="filterColleges('<?= e($col['code']) ?>')">
                <?= e($col['code']) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="colleges-grid" id="collegesGrid">
            <?php foreach ($colleges as $col):
                $det   = $collegeDetails[$col['code']] ?? [];
                $cclass = 'college-' . ($det['color'] ?? 'green');
            ?>
            <div class="college-card <?= $cclass ?>" data-college="<?= e($col['code']) ?>">
                <div class="college-card-header">
                    <span class="college-icon"><?= $det['icon'] ?? '📋' ?></span>
                    <span class="college-code"><?= e($col['code']) ?></span>
                </div>
                <h3 class="college-name"><?= e($col['name']) ?></h3>
                <p class="college-desc"><?= e($det['desc'] ?? $col['description']) ?></p>
                <ul class="college-programmes-list">
                    <?php foreach (($det['programmes'] ?? []) as $prog): ?>
                    <li><?= e($prog) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="college-card-footer">
                    <a href="<?= BASE_URL ?>/apply.php?college=<?= urlencode($col['code']) ?>"
                       class="btn btn-primary btn-sm btn-full">
                        Apply for <?= e($col['code']) ?> →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     HOW TO APPLY
══════════════════════════════════════════════════ -->
<section class="how-section section bg-light" id="how">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Admission Process</span>
            <h2 class="section-title">How to Apply</h2>
            <p class="section-desc">Five simple steps to secure your admission.</p>
        </div>
        <div class="steps-grid">
            <div class="step-item">
                <div class="step-number">01</div>
                <div class="step-body">
                    <h4>Create Account</h4>
                    <p>Register on this portal with your email address and personal details to get started.</p>
                </div>
            </div>
            <div class="step-connector">→</div>
            <div class="step-item">
                <div class="step-number">02</div>
                <div class="step-body">
                    <h4>Choose Programme</h4>
                    <p>Select your college and preferred programme of study from our catalogue.</p>
                </div>
            </div>
            <div class="step-connector">→</div>
            <div class="step-item">
                <div class="step-number">03</div>
                <div class="step-body">
                    <h4>Fill Application</h4>
                    <p>Complete your personal, academic and programme details accurately.</p>
                </div>
            </div>
            <div class="step-connector">→</div>
            <div class="step-item">
                <div class="step-number">04</div>
                <div class="step-body">
                    <h4>Pay &amp; Upload</h4>
                    <p>Pay the application fee and upload all required supporting documents.</p>
                </div>
            </div>
            <div class="step-connector">→</div>
            <div class="step-item">
                <div class="step-number">05</div>
                <div class="step-body">
                    <h4>Track Status</h4>
                    <p>Monitor your application and download your admission letter when approved.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/apply.php" class="btn btn-primary btn-lg">
                Begin Application Now
            </a>
            <a href="<?= BASE_URL ?>/status.php" class="btn btn-outline btn-lg ml-2">
                Check Existing Application
            </a>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     FEES
══════════════════════════════════════════════════ -->
<section class="fees-section section" id="fees">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Application Fees</span>
            <h2 class="section-title">Fee Schedule – <?= SITE_SESSION ?></h2>
            <p class="section-desc">Pay to your designated bank account and upload proof on the portal.</p>
        </div>
        <div class="fees-grid">
            <?php
            $feeLabels = [
                'COE' => 'College of Education',
                'CHT' => 'College of Health Technology',
                'SON' => 'School of Nursing',
                'ADP' => 'Affiliate Degree Programmes',
                'FUL' => 'Foreign University Linkage',
            ];
            foreach (FEES as $code => $fee): ?>
            <div class="fee-card">
                <div class="fee-college"><?= e($feeLabels[$code] ?? $code) ?></div>
                <div class="fee-badge"><?= e($code) ?></div>
                <div class="fee-row">
                    <span>Application Fee</span>
                    <strong><?= formatMoney($fee['application']) ?></strong>
                </div>
                <div class="fee-row">
                    <span>Acceptance Fee</span>
                    <strong><?= formatMoney($fee['acceptance']) ?></strong>
                </div>
                <a href="<?= BASE_URL ?>/apply.php?college=<?= urlencode($code) ?>"
                   class="btn btn-sm btn-primary btn-full mt-1">Apply →</a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="fees-note">
            <p>
                <strong>Bank:</strong> <?= BANK_NAME ?> &nbsp;|&nbsp;
                <strong>Account Name:</strong> <?= ACCOUNT_NAME ?> &nbsp;|&nbsp;
                <strong>Account No:</strong> <?= ACCOUNT_NUMBER ?>
            </p>
            <p>After payment, upload your teller / proof of payment on your dashboard.</p>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     ACCREDITATION STRIP
══════════════════════════════════════════════════ -->
<section class="accred-strip">
    <div class="container accred-inner">
        <span class="accred-label">Accreditations &amp; Affiliations</span>
        <div class="accred-items">
            <div class="accred-item">
                <span class="accred-icon">🏛️</span>
                <span>NBTE Accredited</span>
            </div>
            <div class="accred-divider">|</div>
            <div class="accred-item">
                <span class="accred-icon">🏛️</span>
                <span>NCCE Accredited</span>
            </div>
            <div class="accred-divider">|</div>
            <div class="accred-item">
                <span class="accred-icon">🎓</span>
                <span>Ambrose Alli University Affiliate</span>
            </div>
            <div class="accred-divider">|</div>
            <div class="accred-item">
                <span class="accred-icon">🎓</span>
                <span>Kwara State University Affiliate</span>
            </div>
            <div class="accred-divider">|</div>
            <div class="accred-item">
                <span class="accred-icon">🌍</span>
                <span>International University Linkages</span>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     CONTACT
══════════════════════════════════════════════════ -->
<section class="contact-section section bg-dark" id="contact">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <span class="section-label light">Get In Touch</span>
                <h2 class="section-title light">Contact the<br>Admissions Office</h2>
                <div class="contact-items">
                    <div class="contact-item">
                        <span class="ci-icon">📍</span>
                        <div>
                            <strong>Address</strong>
                            <p>Osogbo Road, Moro, Ife-North LGA,<br>Osun State, Nigeria</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="ci-icon">📞</span>
                        <div>
                            <strong>Phone</strong>
                            <p>
                                <a href="tel:+2348033705261"><?= SITE_PHONE ?></a><br>
                                <a href="tel:+2347071578491">+234 707 157 8491</a><br>
                                <a href="tel:+2347018393022">+234 701 839 3022</a>
                            </p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="ci-icon">✉️</span>
                        <div>
                            <strong>Email</strong>
                            <p>
                                <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a><br>
                                <a href="mailto:info@foreignlinkscampus.edu.ng">info@foreignlinkscampus.edu.ng</a>
                            </p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="ci-icon">🕐</span>
                        <div>
                            <strong>Office Hours</strong>
                            <p>Monday – Friday: 8:00am – 4:00pm<br>Saturday: 9:00am – 1:00pm</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-cta-box">
                <div class="cta-header">
                    <span class="cta-session-tag"><?= SITE_SESSION ?> Session</span>
                    <h3>Ready to Apply?</h3>
                    <p>
                        Admission is currently
                        <strong class="text-success">open</strong>.
                        Start your application today and secure your place.
                    </p>
                </div>
                <div class="cta-buttons">
                    <a href="<?= BASE_URL ?>/apply.php"
                       class="btn btn-primary btn-lg btn-full">
                        🎓 Start Application
                    </a>
                    <a href="<?= BASE_URL ?>/status.php"
                       class="btn btn-outline-white btn-lg btn-full">
                        🔍 Check Application Status
                    </a>
                    <a href="<?= BASE_URL ?>/login.php"
                       class="btn btn-ghost btn-lg btn-full">
                        🔐 Applicant Login
                    </a>
                </div>
                <div class="cta-footer">
                    <p>Need help? Call <a href="tel:+2348033705261"><?= SITE_PHONE ?></a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
