CREATE DATABASE IF NOT EXISTS doctor_management;
USE doctor_management;

CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_appointments INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (doctor_id, day_of_week)
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_name VARCHAR(150) NOT NULL,
    patient_email VARCHAR(150) NOT NULL,
    patient_phone VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    notes TEXT,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Sample doctors
INSERT INTO doctors (name, specialty, email, phone, bio) VALUES
('Dr. Sarah Mitchell', 'Cardiology', 'sarah.mitchell@clinic.com', '+1-555-0101', 'Board-certified cardiologist with 15 years of experience.'),
('Dr. James Patel', 'Orthopedics', 'james.patel@clinic.com', '+1-555-0102', 'Specializes in sports injuries and joint replacement.'),
('Dr. Emily Chen', 'Pediatrics', 'emily.chen@clinic.com', '+1-555-0103', 'Dedicated pediatrician caring for patients from birth through adolescence.');

-- Sample schedules
INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, max_appointments) VALUES
(1, 'Monday', '09:00:00', '17:00:00', 12),
(1, 'Wednesday', '09:00:00', '17:00:00', 12),
(1, 'Friday', '09:00:00', '13:00:00', 6),
(2, 'Tuesday', '08:00:00', '16:00:00', 10),
(2, 'Thursday', '08:00:00', '16:00:00', 10),
(3, 'Monday', '10:00:00', '18:00:00', 15),
(3, 'Tuesday', '10:00:00', '18:00:00', 15),
(3, 'Wednesday', '10:00:00', '18:00:00', 15);

-- Sample appointments
INSERT INTO appointments (doctor_id, patient_name, patient_email, patient_phone, appointment_date, appointment_time, reason, status) VALUES
(1, 'John Doe', 'john.doe@email.com', '+1-555-1001', '2026-06-23', '10:00:00', 'Routine checkup', 'confirmed'),
(1, 'Mary Smith', 'mary.smith@email.com', '+1-555-1002', '2026-06-23', '11:00:00', 'Chest pain evaluation', 'pending'),
(2, 'Robert Brown', 'robert.brown@email.com', '+1-555-1003', '2026-06-24', '09:00:00', 'Knee pain', 'confirmed'),
(3, 'Lisa Johnson', 'lisa.johnson@email.com', '+1-555-1004', '2026-06-23', '14:00:00', 'Annual checkup', 'confirmed');
