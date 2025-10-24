-- Online General Diary System - Seed Data
-- Sample data for testing and initial setup

-- Insert default GD statuses
INSERT INTO gd_statuses (status_name, description) VALUES
('Open', 'GD has been filed and is awaiting assignment'),
('Assigned', 'GD has been assigned to an SI'),
('Under Investigation', 'SI is actively investigating the case'),
('Pending Evidence', 'Waiting for additional evidence or documents'),
('Resolved', 'Case has been resolved successfully'),
('Closed', 'Case has been closed'),
('Rejected', 'GD has been rejected due to invalid information');

-- Insert sample users
-- Password for all users is 'password123' (hashed using password_hash())
INSERT INTO users (f_name, l_name, email, password, phone, nid, address, role) VALUES
-- Admin user
('John', 'Admin', 'admin@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345678', '1234567890123', 'Admin Office, Dhaka', 'admin'),

-- SI users
('Sarah', 'Ahmed', 'sarah.si@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345679', '1234567890124', 'Police Station, Dhaka', 'si'),
('Mohammad', 'Hasan', 'hasan.si@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345680', '1234567890125', 'Police Station, Chittagong', 'si'),
('Fatima', 'Begum', 'fatima.si@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345681', '1234567890126', 'Police Station, Sylhet', 'si'),

-- Regular users
('Rahim', 'Khan', 'rahim.user@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345682', '1234567890127', 'Dhanmondi, Dhaka', 'user'),
('Karim', 'Uddin', 'karim.user@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345683', '1234567890128', 'Gulshan, Dhaka', 'user'),
('Nasir', 'Ali', 'nasir.user@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345684', '1234567890129', 'Banani, Dhaka', 'user'),
('Salma', 'Khatun', 'salma.user@gd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345685', '1234567890130', 'Uttara, Dhaka', 'user');

-- Insert sample GDs
INSERT INTO gds (gd_number, user_id, status_id, assigned_si_id, subject, description, incident_date, incident_time, location) VALUES
('GD20241201001', 5, 2, 2, 'Theft of Mobile Phone', 'My mobile phone was stolen from my pocket while shopping at New Market. The phone is a Samsung Galaxy S21.', '2024-11-25', '14:30:00', 'New Market, Dhaka'),
('GD20241201002', 6, 3, 2, 'Vehicle Accident', 'My car was hit by a motorcycle at the intersection of Dhanmondi Road 27. The motorcycle driver fled the scene.', '2024-11-26', '09:15:00', 'Dhanmondi Road 27, Dhaka'),
('GD20241201003', 7, 1, NULL, 'Fraud Complaint', 'Someone called me claiming to be from a bank and asked for my account details. I provided the information but now suspect it was fraud.', '2024-11-27', '16:45:00', 'Banani, Dhaka'),
('GD20241201004', 8, 2, 3, 'Property Dispute', 'My neighbor is claiming ownership of 2 feet of my land. We have been arguing about this for months.', '2024-11-28', '11:20:00', 'Uttara Sector 7, Dhaka'),
('GD20241201005', 5, 4, 2, 'Missing Person', 'My 16-year-old daughter has been missing since yesterday evening. She left home saying she was going to meet friends.', '2024-11-29', '18:00:00', 'Dhanmondi, Dhaka'),
('GD20241201006', 6, 5, 3, 'Cyber Crime', 'Someone hacked my Facebook account and posted inappropriate content. My friends are receiving messages from my account.', '2024-11-30', '20:30:00', 'Gulshan, Dhaka');

-- Insert sample files (these would be actual files in the uploads directory)
INSERT INTO files (gd_id, filename, original_filename, file_path, file_size, file_type) VALUES
(1, 'mobile_theft_evidence_001.jpg', 'phone_receipt.jpg', 'uploads/gd_1/mobile_theft_evidence_001.jpg', 245760, 'image/jpeg'),
(1, 'mobile_theft_evidence_002.jpg', 'location_photo.jpg', 'uploads/gd_1/mobile_theft_evidence_002.jpg', 189440, 'image/jpeg'),
(2, 'accident_photos_001.jpg', 'car_damage.jpg', 'uploads/gd_2/accident_photos_001.jpg', 456320, 'image/jpeg'),
(2, 'accident_photos_002.jpg', 'scene_photo.jpg', 'uploads/gd_2/accident_photos_002.jpg', 389120, 'image/jpeg'),
(3, 'fraud_call_recording.mp3', 'call_recording.mp3', 'uploads/gd_3/fraud_call_recording.mp3', 1024000, 'audio/mpeg'),
(4, 'property_documents.pdf', 'land_documents.pdf', 'uploads/gd_4/property_documents.pdf', 512000, 'application/pdf'),
(5, 'missing_person_photo.jpg', 'daughter_photo.jpg', 'uploads/gd_5/missing_person_photo.jpg', 234560, 'image/jpeg'),
(6, 'facebook_screenshots.pdf', 'hacked_account_evidence.pdf', 'uploads/gd_6/facebook_screenshots.pdf', 678900, 'application/pdf');

-- Insert sample admin notes
INSERT INTO admin_notes (gd_id, admin_id, note_text, is_internal) VALUES
(1, 1, 'Case assigned to SI Sarah Ahmed. Initial investigation shows this is a common pickpocketing case in New Market area.', FALSE),
(2, 1, 'Vehicle accident case. Need to check CCTV footage from nearby cameras. SI Sarah Ahmed assigned.', FALSE),
(3, 1, 'Fraud case - need to investigate the phone number used for the call. Waiting for assignment.', TRUE),
(4, 1, 'Property dispute case assigned to SI Mohammad Hasan. This is a civil matter but police intervention needed.', FALSE),
(5, 1, 'Missing person case - high priority. SI Sarah Ahmed assigned. Need to coordinate with other stations.', FALSE),
(6, 1, 'Cyber crime case assigned to SI Mohammad Hasan. Need to coordinate with cyber crime unit.', FALSE);

-- Insert sample notifications
INSERT INTO notifications (user_id, gd_id, message, type) VALUES
(2, 1, 'You have been assigned GD #GD20241201001 - Theft of Mobile Phone', 'info'),
(2, 2, 'You have been assigned GD #GD20241201002 - Vehicle Accident', 'info'),
(2, 5, 'You have been assigned GD #GD20241201005 - Missing Person (High Priority)', 'warning'),
(3, 4, 'You have been assigned GD #GD20241201004 - Property Dispute', 'info'),
(3, 6, 'You have been assigned GD #GD20241201006 - Cyber Crime', 'info'),
(5, 1, 'Your GD #GD20241201001 has been assigned to SI Sarah Ahmed for investigation', 'success'),
(6, 2, 'Your GD #GD20241201002 has been assigned to SI Sarah Ahmed for investigation', 'success'),
(7, 3, 'Your GD #GD20241201003 is under review and will be assigned soon', 'info'),
(8, 4, 'Your GD #GD20241201004 has been assigned to SI Mohammad Hasan for investigation', 'success'),
(5, 5, 'Your GD #GD20241201005 has been assigned to SI Sarah Ahmed (High Priority Case)', 'warning'),
(6, 6, 'Your GD #GD20241201006 has been assigned to SI Mohammad Hasan for investigation', 'success');

-- Insert sample activity log entries
INSERT INTO activity_log (user_id, action, description, gd_id, ip_address) VALUES
(1, 'GD_ASSIGNED', 'Assigned GD #GD20241201001 to SI Sarah Ahmed', 1, '192.168.1.100'),
(1, 'GD_ASSIGNED', 'Assigned GD #GD20241201002 to SI Sarah Ahmed', 2, '192.168.1.100'),
(1, 'GD_ASSIGNED', 'Assigned GD #GD20241201004 to SI Mohammad Hasan', 4, '192.168.1.100'),
(1, 'GD_ASSIGNED', 'Assigned GD #GD20241201005 to SI Sarah Ahmed', 5, '192.168.1.100'),
(1, 'GD_ASSIGNED', 'Assigned GD #GD20241201006 to SI Mohammad Hasan', 6, '192.168.1.100'),
(2, 'STATUS_UPDATED', 'Updated GD #GD20241201001 status to Under Investigation', 1, '192.168.1.101'),
(3, 'STATUS_UPDATED', 'Updated GD #GD20241201004 status to Under Investigation', 4, '192.168.1.102'),
(5, 'GD_FILED', 'Filed new GD #GD20241201001', 1, '192.168.1.103'),
(6, 'GD_FILED', 'Filed new GD #GD20241201002', 2, '192.168.1.104'),
(7, 'GD_FILED', 'Filed new GD #GD20241201003', 3, '192.168.1.105'),
(8, 'GD_FILED', 'Filed new GD #GD20241201004', 4, '192.168.1.106'),
(5, 'GD_FILED', 'Filed new GD #GD20241201005', 5, '192.168.1.107'),
(6, 'GD_FILED', 'Filed new GD #GD20241201006', 6, '192.168.1.108');

-- Note: Default password for all users is 'password123'
-- In production, users should change their passwords immediately after first login
