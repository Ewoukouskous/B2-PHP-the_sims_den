-- Insert admin user for testing
-- Username: admin
-- Password: admin123 (hashed with password_hash() function)
-- Email: admin@thesimden.com

INSERT INTO user_account (user_role, username, email, password_hash, id_profile_pic)
VALUES (
    'admin',
    'admin',
    'admin@thesimden.com',
    -- This is a bcrypt hash of 'admin123'
    '$2y$10$uyQrhLpgKDs0c3R6uaTK1Od50/kPNphDYT1wsGrJ.PeW3oDteOCRS',
    1
);


