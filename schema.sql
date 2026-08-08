CREATE DATABASE IF NOT EXISTS mediqueue_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediqueue_ai;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin', 'patient', 'doctor') NOT NULL,
  profile_image_path VARCHAR(255) NULL,
  is_kiosk_account TINYINT(1) NOT NULL DEFAULT 0,
  kiosk_label VARCHAR(120) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS location_departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NOT NULL,
  department_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_location_department (location_id, department_id),
  CONSTRAINT fk_location_departments_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
  CONSTRAINT fk_location_departments_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS doctor_locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_user_id INT NOT NULL,
  location_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_doctor_single_location (doctor_user_id),
  UNIQUE KEY uniq_doctor_location (doctor_user_id, location_id),
  CONSTRAINT fk_doctor_locations_user FOREIGN KEY (doctor_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_doctor_locations_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS doctor_departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_user_id INT NOT NULL,
  department_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_doctor_single_department (doctor_user_id),
  UNIQUE KEY uniq_doctor_department (doctor_user_id, department_id),
  CONSTRAINT fk_doctor_departments_user FOREIGN KEY (doctor_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_doctor_departments_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS doctor_availability (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_user_id INT NOT NULL,
  weekday TINYINT NOT NULL COMMENT '0=Sunday ... 6=Saturday',
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_doctor_availability (doctor_user_id, weekday, start_time, end_time),
  CONSTRAINT fk_doctor_availability_user FOREIGN KEY (doctor_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS patient_locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_user_id INT NOT NULL,
  location_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_patient_single_location (patient_user_id),
  UNIQUE KEY uniq_patient_location (patient_user_id, location_id),
  CONSTRAINT fk_patient_locations_user FOREIGN KEY (patient_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_patient_locations_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  date_of_birth DATE NULL,
  gender ENUM('male', 'female', 'other') NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  service_name VARCHAR(100) NOT NULL,
  location_name VARCHAR(100) NOT NULL,
  doctor_name VARCHAR(100) NOT NULL DEFAULT 'Dr. On Duty',
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  ai_priority_score TINYINT UNSIGNED NOT NULL DEFAULT 1,
  symptoms TEXT NULL,
  notes TEXT NULL,
  status ENUM('scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_slot (appointment_date, appointment_time, location_name),
  KEY idx_queue (appointment_date, location_name, status, appointment_time),
  CONSTRAINT fk_appointment_patient FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  actor_user_id INT NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id INT NULL,
  details_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_time (created_at),
  KEY idx_audit_actor (actor_user_id),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON DELETE SET NULL
);

INSERT INTO users (username, password_hash, full_name, role, profile_image_path, is_kiosk_account, kiosk_label, is_active)
VALUES
  ('admin1', '$2y$12$VLeKqXDfrl/CZb7GSv3hD.HxZ17lsWFfpSv1SRKkoVwS7HapkLb4.', 'System Admin', 'admin', NULL, 0, NULL, 1),
  ('patient1', '$2y$12$VLeKqXDfrl/CZb7GSv3hD.HxZ17lsWFfpSv1SRKkoVwS7HapkLb4.', 'Patient Self-Service Kiosk', 'patient', '/uploads/profiles/default-avatar.svg', 1, 'Main Branch Self-Service Kiosk', 1),
  ('doctor1', '$2y$12$VLeKqXDfrl/CZb7GSv3hD.HxZ17lsWFfpSv1SRKkoVwS7HapkLb4.', 'Dr. Samiul Hasan', 'doctor', '/uploads/profiles/default-doctor.svg', 0, NULL, 1)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  role = VALUES(role),
  profile_image_path = VALUES(profile_image_path),
  is_kiosk_account = VALUES(is_kiosk_account),
  kiosk_label = VALUES(kiosk_label),
  is_active = VALUES(is_active);

INSERT INTO locations (name, is_active)
VALUES ('Main Branch', 1), ('North Clinic', 1), ('South Clinic', 1)
ON DUPLICATE KEY UPDATE is_active = VALUES(is_active);

INSERT INTO departments (name, is_active)
VALUES ('General Checkup', 1), ('Dental', 1), ('Cardiology', 1), ('Orthopedics', 1), ('Cancer', 1)
ON DUPLICATE KEY UPDATE is_active = VALUES(is_active);

INSERT IGNORE INTO location_departments (location_id, department_id)
SELECT l.id, d.id
FROM locations l
CROSS JOIN departments d
WHERE l.is_active = 1 AND d.is_active = 1;

INSERT IGNORE INTO doctor_locations (doctor_user_id, location_id)
SELECT u.id, l.id
FROM users u
JOIN locations l ON l.name = 'Main Branch'
WHERE u.username = 'doctor1';

INSERT INTO doctor_departments (doctor_user_id, department_id)
SELECT u.id, d.id
FROM users u
JOIN departments d ON d.name = 'General Checkup'
WHERE u.username = 'doctor1'
ON DUPLICATE KEY UPDATE department_id = VALUES(department_id);

INSERT IGNORE INTO doctor_availability (doctor_user_id, weekday, start_time, end_time)
SELECT u.id, d.weekday, '14:00:00', '17:00:00'
FROM users u
JOIN (
  SELECT 1 AS weekday UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) d
WHERE u.username = 'doctor1';

INSERT IGNORE INTO patient_locations (patient_user_id, location_id)
SELECT u.id, l.id
FROM users u
JOIN locations l ON l.name = 'Main Branch'
WHERE u.username = 'patient1';
