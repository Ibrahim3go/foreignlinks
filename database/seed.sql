-- ============================================================
--  Foreign Links Campus – Seed / Test Data
--  Run AFTER schema.sql
--  Use only in development / testing environment
-- ============================================================

USE foreignlinks_db;

-- ============================================================
-- Sample Applicants (password for all: Test@1234)
-- bcrypt hash of 'Test@1234'
-- ============================================================
INSERT INTO applicants
  (ref_number, surname, firstname, middlename, email, phone,
   password_hash, gender, date_of_birth, state_of_origin, lga,
   nationality, home_address, is_verified)
VALUES
('FLC-2025-000001','Adeyemi','Fatimah','Blessing',
 'fatimah.adeyemi@email.com','08012345678',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Female','2002-04-15','Osun','Ife North','Nigerian',
 '12 Unity Street, Moro, Osun State', 1),

('FLC-2025-000002','Okafor','Chukwuemeka','Daniel',
 'emeka.okafor@email.com','08023456789',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Male','2001-09-22','Anambra','Onitsha North','Nigerian',
 '5 Palm Avenue, Onitsha, Anambra State', 1),

('FLC-2025-000003','Ibrahim','Aisha','Khadijah',
 'aisha.ibrahim@email.com','08034567890',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Female','2003-01-08','Kano','Kano Municipal','Nigerian',
 '7 Bello Road, Kano, Kano State', 1),

('FLC-2025-000004','Adeleke','Tunde','Samuel',
 'tunde.adeleke@email.com','08045678901',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Male','2000-07-30','Osun','Osogbo','Nigerian',
 '3 Ola Iya Road, Osogbo, Osun State', 1);

-- ============================================================
-- Sample Applications
-- ============================================================
INSERT INTO applications
  (applicant_id, session_id, college_id, programme_id,
   application_type, entry_mode, jamb_reg_number, jamb_score,
   status, step_personal, step_academic, step_documents, step_payment, step_submitted, submitted_at)
VALUES
-- Fatimah → NCE Business Education
(1, 2, 1, 1, 'Fresh', 'UTME', '21234567AB', 185,
 'Shortlisted', 1, 1, 1, 1, 1, NOW()),

-- Chukwuemeka → ND Health Information Management
(2, 2, 2, 9, 'Fresh', 'UTME', '21345678CD', 172,
 'Under Review', 1, 1, 1, 0, 0, NULL),

-- Aisha → School of Nursing
(3, 2, 3, 37, 'Fresh', 'Entrance Exam', NULL, NULL,
 'Pending', 1, 1, 0, 0, 0, NULL),

-- Tunde → B.Sc Accounting (AAU Affiliate)
(4, 2, 4, 39, 'Fresh', 'Direct Entry', '20456789EF', 201,
 'Admitted', 1, 1, 1, 1, 1, NOW());

-- ============================================================
-- Sample O'Level Results
-- ============================================================
INSERT INTO olevel_results (application_id, sitting, exam_type, exam_year, subject, grade) VALUES
(1,'First','WAEC',2021,'English Language','B2'),
(1,'First','WAEC',2021,'Mathematics','C4'),
(1,'First','WAEC',2021,'Economics','B3'),
(1,'First','WAEC',2021,'Commerce','B2'),
(1,'First','WAEC',2021,'Government','C5'),

(2,'First','NECO',2022,'English Language','B3'),
(2,'First','NECO',2022,'Mathematics','C4'),
(2,'First','NECO',2022,'Biology','B2'),
(2,'First','NECO',2022,'Chemistry','C5'),
(2,'First','NECO',2022,'Physics','C6');

-- ============================================================
-- Sample Payments
-- ============================================================
INSERT INTO payments
  (application_id, applicant_id, payment_type, amount,
   payment_method, transaction_ref, bank_name, payment_date, status)
VALUES
(1, 1, 'Application Fee', 5000.00, 'Bank Transfer', 'FBN20250115001', 'First Bank', '2025-01-15', 'Verified'),
(1, 1, 'Acceptance Fee',  10000.00,'Bank Transfer', 'FBN20250120001', 'First Bank', '2025-01-20', 'Verified'),
(4, 4, 'Application Fee', 8000.00, 'Bank Transfer', 'GTB20250112001', 'GTBank',     '2025-01-12', 'Verified'),
(4, 4, 'Acceptance Fee',  10000.00,'Online Payment','PSK-REF-2025001','Paystack',   '2025-01-18', 'Verified');

-- ============================================================
-- Sample Notifications
-- ============================================================
INSERT INTO notifications (applicant_id, channel, subject, message, is_read) VALUES
(1, 'Portal', 'Application Shortlisted',
 'Dear Fatimah, your application for NCE Business Education has been shortlisted. Please check your dashboard for next steps.', 0),
(4, 'Portal', 'Congratulations – Admission Offered',
 'Dear Tunde, you have been offered provisional admission into B.Sc Accounting for the 2024/2025 session. Please login to download your admission letter.', 0),
(2, 'Portal', 'Application Received',
 'Dear Chukwuemeka, your application is currently under review. You will be notified of any updates.', 1);

-- ============================================================
-- Sample Entrance Exam (Nursing)
-- ============================================================
INSERT INTO entrance_exams (application_id, exam_date, exam_time, venue, seat_number, status) VALUES
(3, '2025-02-10', '09:00:00', 'Foreign Links Campus – Main Hall', 'SON-025', 'Scheduled');

-- ============================================================
-- Additional Admin Accounts
-- ============================================================
INSERT INTO admins (fullname, email, password_hash, role, college_id) VALUES
('Health Admission Officer',  'health.officer@foreignlinks.edu.ng',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admission Officer', 2),
('COE Admission Officer',     'coe.officer@foreignlinks.edu.ng',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admission Officer', 1),
('Data Entry Staff',          'dataentry@foreignlinks.edu.ng',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Data Entry',        NULL);

-- ============================================================
-- END OF SEED
-- ============================================================
