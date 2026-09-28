-- ============================================================
--  Foreign Links Campus, Moro – Admission Portal
--  Database: foreignlinks_db
--  Engine:   InnoDB | Charset: utf8mb4
--  Version:  1.0  |  2025
-- ============================================================

CREATE DATABASE IF NOT EXISTS foreignlinks_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE foreignlinks_db;

-- ============================================================
-- 1. COLLEGES  (the 5 divisions of the campus)
-- ============================================================
CREATE TABLE IF NOT EXISTS colleges (
  id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(20)  NOT NULL UNIQUE,   -- e.g. COE, CHT, SON, ADP, FUL
  name        VARCHAR(120) NOT NULL,
  description TEXT,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO colleges (code, name, description) VALUES
('COE',  'College of Education',
  'Offers NCE programmes accredited by the National Commission for Colleges of Education (NCCE).'),
('CHT',  'College of Health Technology',
  'Offers ND, HND, Diploma and Technician programmes accredited by NBTE.'),
('SON',  'School of Nursing',
  'Professional nursing programmes with entrance examination requirements.'),
('ADP',  'Affiliate Degree Programmes',
  'Undergraduate degree programmes run in affiliation with Ambrose Alli University and Kwara State University.'),
('FUL',  'Foreign University Linkage Programmes',
  'International transfer and admission facilitation to universities in Ghana, Kenya, South Africa, Poland, Europe, America and Canada.');


-- ============================================================
-- 2. DEPARTMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS departments (
  id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  college_id  TINYINT UNSIGNED  NOT NULL,
  name        VARCHAR(120) NOT NULL,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO departments (college_id, name) VALUES
-- College of Education (1)
(1, 'Business Education'),
(1, 'Christian Religious Studies'),
(1, 'Computer Education'),
(1, 'Economics Education'),
(1, 'English Education'),
(1, 'Geography Education'),
(1, 'Islamic Studies Education'),
(1, 'Mathematics Education'),
(1, 'Primary Education Studies'),
(1, 'Social Studies Education'),
(1, 'Yoruba Education'),
-- College of Health Technology (2)
(2, 'Community Health'),
(2, 'Environmental Health'),
(2, 'Dental Technology'),
(2, 'Health Information Management'),
(2, 'Health Promotion & Education'),
(2, 'Medical Laboratory Technology'),
(2, 'Nursing Science'),
(2, 'Pharmacy Technology'),
(2, 'Public Health Technology'),
(2, 'Radiography Technology'),
-- School of Nursing (3)
(3, 'General Nursing'),
-- Affiliate Degree Programmes (4)
(4, 'Faculty of Education'),
(4, 'School of General Administration & Management'),
-- Foreign University Linkage (5)
(5, 'Health Sciences'),
(5, 'Sciences'),
(5, 'Management Sciences'),
(5, 'Postgraduate Programmes');


-- ============================================================
-- 3. PROGRAMMES / COURSES
-- ============================================================
CREATE TABLE IF NOT EXISTS programmes (
  id               SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id    SMALLINT UNSIGNED NOT NULL,
  college_id       TINYINT UNSIGNED  NOT NULL,
  code             VARCHAR(20)  NOT NULL UNIQUE,
  name             VARCHAR(180) NOT NULL,
  qualification    VARCHAR(60)  NOT NULL,   -- NCE, ND, HND, B.Sc, Diploma, etc.
  duration_years   TINYINT      NOT NULL DEFAULT 2,
  requirements     TEXT,                    -- entry requirements
  application_fee  DECIMAL(10,2) NOT NULL DEFAULT 5000.00,
  acceptance_fee   DECIMAL(10,2) NOT NULL DEFAULT 10000.00,
  tuition_fee      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_active        TINYINT(1)   NOT NULL DEFAULT 1,
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
  FOREIGN KEY (college_id)    REFERENCES colleges(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- College of Education – NCE Programmes
INSERT INTO programmes (department_id, college_id, code, name, qualification, duration_years) VALUES
(1,  1, 'NCE-BED',   'Business Education',                          'NCE', 3),
(2,  1, 'NCE-CRS-ENG','Christian Religious Studies / English',       'NCE', 3),
(2,  1, 'NCE-CRS-SST','Christian Religious Studies / Social Studies','NCE', 3),
(3,  1, 'NCE-COMP-ENG','Computer Education / English',               'NCE', 3),
(3,  1, 'NCE-COMP-MTH','Computer Science Education / Mathematics',   'NCE', 3),
(4,  1, 'NCE-ECO-MTH', 'Economics / Mathematics',                    'NCE', 3),
(4,  1, 'NCE-ECO-SST', 'Economics / Social Studies',                 'NCE', 3),
(5,  1, 'NCE-ENG-SST', 'English / Social Studies',                   'NCE', 3),
(6,  1, 'NCE-GEO-SST', 'Geography / Social Studies',                 'NCE', 3),
(7,  1, 'NCE-ISL-SST', 'Islamic Studies / Social Studies',           'NCE', 3),
(9,  1, 'NCE-PES',     'Primary Education Studies',                  'NCE', 3),
(10, 1, 'NCE-SST-YOR', 'Social Studies / Yoruba',                    'NCE', 3),
(11, 1, 'NCE-YOR-COMP','Yoruba / Computer Science',                  'NCE', 3),
(11, 1, 'NCE-YOR-SST', 'Yoruba / Social Studies',                    'NCE', 3);

-- College of Health Technology
INSERT INTO programmes (department_id, college_id, code, name, qualification, duration_years) VALUES
(12, 2, 'CHT-CHEW',   'Community Health Extension Worker',          'Certificate', 2),
(12, 2, 'CHT-JCHEW',  'Junior Community Health Extension Worker',   'Certificate', 1),
(13, 2, 'CHT-EHT',    'Environmental Health Technology',            'ND',  2),
(13, 2, 'CHT-EH',     'Environmental Health',                       'ND',  2),
(13, 2, 'CHT-EHA',    'Environmental Health Assistant',             'Certificate', 1),
(14, 2, 'CHT-DST',    'Dental Surgery Technician',                  'Diploma', 2),
(14, 2, 'CHT-DTH',    'Dental Therapy',                             'Diploma', 2),
(14, 2, 'CHT-DN',     'Dental Nursing',                             'Diploma', 2),
(15, 2, 'CHT-HIM-ND', 'Health Information Management',              'ND',  2),
(15, 2, 'CHT-HIM-HND','Health Information Management',              'HND', 2),
(15, 2, 'CHT-HIM-DIP','Health Information Management',              'Diploma', 1),
(16, 2, 'CHT-HPE-ND', 'Health Promotion & Education',               'ND',  2),
(16, 2, 'CHT-HPE-DIP','Health Promotion & Education',               'Diploma', 1),
(17, 2, 'CHT-MLT',    'Medical Laboratory Technician',              'Certificate', 2),
(17, 2, 'CHT-MHA',    'Medical Health Assistant',                   'Certificate', 1),
(17, 2, 'CHT-HAM',    'Health Assistant Medical (HAM)',             'Certificate', 1),
(19, 2, 'CHT-PHT-ND', 'Public Health Technology',                   'ND',  2),
(19, 2, 'CHT-PHN',    'Public Health Nursing',                      'Certificate', 2),
(19, 2, 'CHT-PHTEC',  'Public Health Technician',                   'Certificate', 1),
(19, 2, 'CHT-CNT',    'Community Nutrition Technician',             'Certificate', 2),
(19, 2, 'CHT-FH',     'Food Hygienist',                             'Certificate', 1),
(20, 2, 'CHT-PHARM',  'Pharmacy Technician',                        'Certificate', 2),
(20, 2, 'CHT-XRAY',   'X-Ray Technician',                           'Certificate', 2);

-- School of Nursing
INSERT INTO programmes (department_id, college_id, code, name, qualification, duration_years) VALUES
(21, 3, 'SON-RN', 'General Nursing', 'RN', 3);

-- Affiliate Degree Programmes – AAU
INSERT INTO programmes (department_id, college_id, code, name, qualification, duration_years) VALUES
(22, 4, 'ADP-BUS-ED',  'B.Sc Business Education',                   'B.Sc', 4),
(22, 4, 'ADP-ACCT',    'B.Sc Accounting',                           'B.Sc', 4),
(22, 4, 'ADP-SEC',     'B.Sc Secretarial Studies',                  'B.Sc', 4),
(22, 4, 'ADP-HEIT',    'B.Sc Health Education',                     'B.Sc', 4),
(22, 4, 'ADP-ECO-ED',  'B.Sc Economics',                            'B.Sc', 4),
(22, 4, 'ADP-POL',     'B.Sc Political Science',                    'B.Sc', 4),
(23, 4, 'ADP-GADM',    'B.Sc General Administration',               'B.Sc', 4),
(23, 4, 'ADP-PADM',    'B.Sc Public Administration',                'B.Sc', 4),
(23, 4, 'ADP-ECO-ADM', 'B.Sc Economics (Admin)',                    'B.Sc', 4),
(23, 4, 'ADP-ACCT-ADM','B.Sc Accounting (Admin)',                   'B.Sc', 4),
(23, 4, 'ADP-BNK',     'B.Sc Banking & Finance',                    'B.Sc', 4),
(23, 4, 'ADP-CS',      'B.Sc Computer Science & Management',        'B.Sc', 4);

-- Foreign University Linkage Programmes
INSERT INTO programmes (department_id, college_id, code, name, qualification, duration_years) VALUES
(24, 5, 'FUL-BSC-NRS', 'B.Sc Nursing',                              'B.Sc', 4),
(24, 5, 'FUL-BSC-PHM', 'B.Sc Pharmacy',                             'B.Sc', 4),
(25, 5, 'FUL-BSC-MCB', 'B.Sc Microbiology',                         'B.Sc', 4),
(25, 5, 'FUL-BSC-CHM', 'B.Sc Chemistry',                            'B.Sc', 4),
(24, 5, 'FUL-BSC-PHY', 'B.Sc Physiotherapy',                        'B.Sc', 4),
(26, 5, 'FUL-MSC-CS',  'M.Sc Computer Science',                     'M.Sc', 2),
(26, 5, 'FUL-MSC-ACC', 'M.Sc Accountancy',                          'M.Sc', 2),
(26, 5, 'FUL-MSC-ECO', 'M.Sc Economics',                            'M.Sc', 2),
(26, 5, 'FUL-MSC-PAD', 'M.Sc Public Administration',                'M.Sc', 2),
(26, 5, 'FUL-MBA',     'Business Administration (MBA)',              'MBA',  2);


-- ============================================================
-- 4. ACADEMIC SESSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS academic_sessions (
  id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session     VARCHAR(20)  NOT NULL UNIQUE,  -- e.g. 2024/2025
  is_current  TINYINT(1)   NOT NULL DEFAULT 0,
  start_date  DATE,
  end_date    DATE,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO academic_sessions (session, is_current, start_date, end_date) VALUES
('2023/2024', 0, '2023-10-01', '2024-07-31'),
('2024/2025', 1, '2024-10-01', '2025-07-31');


-- ============================================================
-- 5. APPLICANTS  (account / login)
-- ============================================================
CREATE TABLE IF NOT EXISTS applicants (
  id              INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
  ref_number      VARCHAR(20)      NOT NULL UNIQUE,  -- FLC-2025-000001
  surname         VARCHAR(80)      NOT NULL,
  firstname       VARCHAR(80)      NOT NULL,
  middlename      VARCHAR(80),
  email           VARCHAR(150)     NOT NULL UNIQUE,
  phone           VARCHAR(20)      NOT NULL,
  password_hash   VARCHAR(255)     NOT NULL,
  gender          ENUM('Male','Female','Other') NOT NULL,
  date_of_birth   DATE             NOT NULL,
  state_of_origin VARCHAR(60)      NOT NULL,
  lga             VARCHAR(80)      NOT NULL,
  nationality     VARCHAR(60)      NOT NULL DEFAULT 'Nigerian',
  religion        VARCHAR(40),
  home_address    TEXT             NOT NULL,
  passport_photo  VARCHAR(255),                  -- file path
  is_verified     TINYINT(1)       NOT NULL DEFAULT 0,
  verify_token    VARCHAR(100),
  reset_token     VARCHAR(100),
  reset_expires   DATETIME,
  last_login      DATETIME,
  created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ref   (ref_number),
  INDEX idx_email (email)
) ENGINE=InnoDB;


-- ============================================================
-- 6. APPLICATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS applications (
  id                  INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  applicant_id        INT UNSIGNED   NOT NULL,
  session_id          TINYINT UNSIGNED NOT NULL,
  college_id          TINYINT UNSIGNED NOT NULL,
  programme_id        SMALLINT UNSIGNED NOT NULL,
  application_type    ENUM('Fresh','Transfer','Direct Entry','International') NOT NULL DEFAULT 'Fresh',

  -- Academic background
  jamb_reg_number     VARCHAR(20),
  jamb_score          SMALLINT,
  olevel_school       VARCHAR(150),
  olevel_year1        YEAR,
  olevel_year2        YEAR,
  entry_mode          ENUM('UTME','Direct Entry','HND','Transfer','Entrance Exam','International') NOT NULL,

  -- For foreign linkage
  destination_country VARCHAR(80),
  foreign_university  VARCHAR(150),

  -- Status tracking
  status              ENUM('Pending','Under Review','Shortlisted',
                           'Admitted','Rejected','Deferred') NOT NULL DEFAULT 'Pending',
  admin_remark        TEXT,
  reviewed_by         SMALLINT UNSIGNED,
  reviewed_at         DATETIME,

  -- Payment
  form_fee_paid       TINYINT(1)  NOT NULL DEFAULT 0,
  acceptance_paid     TINYINT(1)  NOT NULL DEFAULT 0,
  tuition_paid        TINYINT(1)  NOT NULL DEFAULT 0,

  -- Steps completed  (bitmask or individual flags)
  step_personal       TINYINT(1)  NOT NULL DEFAULT 0,
  step_academic       TINYINT(1)  NOT NULL DEFAULT 0,
  step_documents      TINYINT(1)  NOT NULL DEFAULT 0,
  step_payment        TINYINT(1)  NOT NULL DEFAULT 0,
  step_submitted      TINYINT(1)  NOT NULL DEFAULT 0,

  submitted_at        DATETIME,
  created_at          TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (applicant_id)  REFERENCES applicants(id)          ON DELETE CASCADE,
  FOREIGN KEY (session_id)    REFERENCES academic_sessions(id)   ON DELETE RESTRICT,
  FOREIGN KEY (college_id)    REFERENCES colleges(id)            ON DELETE RESTRICT,
  FOREIGN KEY (programme_id)  REFERENCES programmes(id)          ON DELETE RESTRICT,
  INDEX idx_status     (status),
  INDEX idx_applicant  (applicant_id),
  INDEX idx_programme  (programme_id)
) ENGINE=InnoDB;


-- ============================================================
-- 7. O'LEVEL RESULTS
-- ============================================================
CREATE TABLE IF NOT EXISTS olevel_results (
  id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  application_id  INT UNSIGNED   NOT NULL,
  sitting         ENUM('First','Second') NOT NULL DEFAULT 'First',
  exam_type       ENUM('WAEC','NECO','NABTEB','GCE','Others') NOT NULL,
  exam_year       YEAR           NOT NULL,
  subject         VARCHAR(80)    NOT NULL,
  grade           VARCHAR(5)     NOT NULL,   -- A1, B2, C4, etc.
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
  INDEX idx_app (application_id)
) ENGINE=InnoDB;


-- ============================================================
-- 8. DOCUMENTS / FILE UPLOADS
-- ============================================================
CREATE TABLE IF NOT EXISTS documents (
  id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  application_id  INT UNSIGNED   NOT NULL,
  document_type   ENUM(
    'Passport Photo',
    'Birth Certificate',
    'WAEC/NECO Result',
    'JAMB Result',
    'O Level Certificate',
    'First Degree Certificate',
    'HND Certificate',
    'NYSC Certificate',
    'Medical Certificate',
    'LGA Identification',
    'Attestation Letter',
    'International Passport',
    'Visa Document',
    'Transfer Letter',
    'Payment Proof',
    'Other'
  ) NOT NULL,
  file_name       VARCHAR(255)   NOT NULL,  -- original name
  file_path       VARCHAR(255)   NOT NULL,  -- stored path
  file_size       INT UNSIGNED,             -- bytes
  mime_type       VARCHAR(80),
  is_verified     TINYINT(1)     NOT NULL DEFAULT 0,
  uploaded_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
  INDEX idx_app (application_id)
) ENGINE=InnoDB;


-- ============================================================
-- 9. PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
  id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  application_id  INT UNSIGNED   NOT NULL,
  applicant_id    INT UNSIGNED   NOT NULL,
  payment_type    ENUM('Application Fee','Acceptance Fee','Tuition Fee',
                       'Entrance Exam Fee','Other') NOT NULL,
  amount          DECIMAL(12,2)  NOT NULL,
  payment_method  ENUM('Bank Transfer','Online Payment','Cash','Other') NOT NULL DEFAULT 'Bank Transfer',
  transaction_ref VARCHAR(100),              -- bank teller / Paystack ref
  bank_name       VARCHAR(80),
  proof_file      VARCHAR(255),              -- screenshot/teller upload
  payment_date    DATE,
  status          ENUM('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  verified_by     SMALLINT UNSIGNED,
  verified_at     DATETIME,
  remark          TEXT,
  created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id)  ON DELETE CASCADE,
  FOREIGN KEY (applicant_id)   REFERENCES applicants(id)    ON DELETE CASCADE,
  INDEX idx_app  (application_id),
  INDEX idx_ref  (transaction_ref)
) ENGINE=InnoDB;


-- ============================================================
-- 10. ENTRANCE EXAM (Nursing + others)
-- ============================================================
CREATE TABLE IF NOT EXISTS entrance_exams (
  id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  application_id  INT UNSIGNED   NOT NULL,
  exam_date       DATE,
  exam_time       TIME,
  venue           VARCHAR(150),
  seat_number     VARCHAR(20),
  score           DECIMAL(5,2),
  status          ENUM('Scheduled','Appeared','Absent','Passed','Failed') NOT NULL DEFAULT 'Scheduled',
  remark          TEXT,
  created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 11. SCREENING / INTERVIEW
-- ============================================================
CREATE TABLE IF NOT EXISTS screenings (
  id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  application_id  INT UNSIGNED   NOT NULL,
  scheduled_date  DATE,
  scheduled_time  TIME,
  venue           VARCHAR(150),
  outcome         ENUM('Pending','Passed','Failed','Deferred') NOT NULL DEFAULT 'Pending',
  screened_by     VARCHAR(80),
  notes           TEXT,
  created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- 12. NOTIFICATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
  id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  applicant_id    INT UNSIGNED   NOT NULL,
  channel         ENUM('Email','SMS','Portal') NOT NULL DEFAULT 'Portal',
  subject         VARCHAR(200),
  message         TEXT           NOT NULL,
  is_read         TINYINT(1)     NOT NULL DEFAULT 0,
  sent_at         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
  INDEX idx_applicant (applicant_id)
) ENGINE=InnoDB;


-- ============================================================
-- 13. ADMINS
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
  id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fullname    VARCHAR(120)  NOT NULL,
  email       VARCHAR(150)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role        ENUM('Super Admin','Admission Officer','Data Entry','Viewer') NOT NULL DEFAULT 'Viewer',
  college_id  TINYINT UNSIGNED,              -- restrict officer to one college (NULL = all)
  is_active   TINYINT(1)    NOT NULL DEFAULT 1,
  last_login  DATETIME,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default super admin (password: Admin@FLC2025 — change immediately)
INSERT INTO admins (fullname, email, password_hash, role) VALUES
('Super Administrator',
 'admin@foreignlinks.edu.ng',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- bcrypt of 'Admin@FLC2025'
 'Super Admin');


-- ============================================================
-- 14. SITE SETTINGS  (key-value store)
-- ============================================================
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key   VARCHAR(80)  NOT NULL PRIMARY KEY,
  setting_value TEXT,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name',            'Foreign Links Campus'),
('site_tagline',         'Excellence in Education – Moro, Ife-North, Osun State'),
('current_session',      '2024/2025'),
('admission_open',       '1'),
('application_fee_coe',  '5000'),
('application_fee_cht',  '5000'),
('application_fee_son',  '7000'),
('application_fee_adp',  '8000'),
('application_fee_ful',  '10000'),
('bank_name',            'First Bank of Nigeria'),
('account_name',         'Foreign Links Campus'),
('account_number',       '0000000000'),
('provost_name',         'Dr. Nsikak Akpan'),
('contact_phone',        '+234 803 3705 261'),
('contact_email',        'info@foreignlinks.edu.ng'),
('address',              'Osogbo Road, Moro, Ife-North LGA, Osun State, Nigeria'),
('smtp_host',            'smtp.gmail.com'),
('smtp_port',            '587'),
('smtp_user',            ''),
('smtp_pass',            '');


-- ============================================================
-- 15. AUDIT LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
  id          INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
  user_type   ENUM('Applicant','Admin') NOT NULL,
  user_id     INT UNSIGNED   NOT NULL,
  action      VARCHAR(100)   NOT NULL,
  description TEXT,
  ip_address  VARCHAR(45),
  created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_type, user_id)
) ENGINE=InnoDB;


-- ============================================================
-- END OF SCHEMA
-- ============================================================
