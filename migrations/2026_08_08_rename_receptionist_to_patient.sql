USE mediqueue_ai;

-- Add the new role value before converting existing rows.
ALTER TABLE users
  MODIFY role ENUM('admin', 'receptionist', 'patient', 'doctor') NOT NULL;

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

ALTER TABLE users
  ADD COLUMN is_kiosk_account TINYINT(1) NOT NULL DEFAULT 0 AFTER profile_image_path,
  ADD COLUMN kiosk_label VARCHAR(120) NULL AFTER is_kiosk_account;

INSERT IGNORE INTO patient_locations (patient_user_id, location_id, created_at)
SELECT receptionist_user_id, location_id, created_at
FROM receptionist_locations;

UPDATE users
SET role = 'patient',
    username = CASE
      WHEN username = 'reception1'
       AND NOT EXISTS (SELECT 1 FROM (SELECT username FROM users WHERE username = 'patient1') AS existing_patient_user)
      THEN 'patient1'
      ELSE username
    END,
    full_name = CASE WHEN full_name = 'Reception Desk' THEN 'Patient Self-Service Kiosk' ELSE full_name END,
    profile_image_path = CASE WHEN profile_image_path = '/uploads/profiles/default-reception.svg' THEN '/uploads/profiles/default-avatar.svg' ELSE profile_image_path END,
    is_kiosk_account = 1,
    kiosk_label = COALESCE(kiosk_label, full_name)
WHERE role = 'receptionist';

ALTER TABLE users
  MODIFY role ENUM('admin', 'patient', 'doctor') NOT NULL;
