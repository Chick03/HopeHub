-- =====================================================
-- HopeHub - Orphanage Donation Management System
-- Single-Orphanage Edition
-- =====================================================

CREATE DATABASE IF NOT EXISTS hopehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hopehub;

-- ---------------------------------------------------
-- Safe to re-run: drops any existing tables first, whether
-- they're from an earlier import of this same file or from
-- the older multi-orphanage version of the project. This
-- deletes all existing data in these tables, so only re-run
-- this file if you're OK starting fresh.
-- ---------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS receipts;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS needs;
DROP TABLE IF EXISTS orphans;
DROP TABLE IF EXISTS orphanage_profile;
DROP TABLE IF EXISTS orphanages;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------
-- USERS  (base: Donor + Admin, role-based)
-- ---------------------------------------------------
CREATE TABLE users (
    user_id      INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    google_id    VARCHAR(100) DEFAULT NULL UNIQUE,
    oauth_token  TEXT DEFAULT NULL,
    role         ENUM('donor','admin') NOT NULL DEFAULT 'donor',
    phone        VARCHAR(20) DEFAULT NULL,
    address      VARCHAR(255) DEFAULT NULL,
    status       ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- ORPHANAGE PROFILE
-- This project manages exactly ONE orphanage, so this
-- table intentionally always holds a single row (id = 1).
-- Admins edit it from admin/orphanage_profile.php instead
-- of creating/deleting records.
-- ---------------------------------------------------
CREATE TABLE orphanage_profile (
    id                  INT PRIMARY KEY DEFAULT 1,
    name                VARCHAR(150) NOT NULL,
    address             VARCHAR(255) NOT NULL,
    description         TEXT,
    founder_name        VARCHAR(100) DEFAULT NULL,
    founder_bio         TEXT,
    registration_number VARCHAR(100) DEFAULT NULL,
    email               VARCHAR(150) DEFAULT NULL,
    mobile_number       VARCHAR(20) DEFAULT NULL,
    whatsapp_number     VARCHAR(20) DEFAULT NULL,
    image_path          VARCHAR(255) DEFAULT NULL,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT single_row CHECK (id = 1)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- ORPHANS  (children housed at the orphanage)
-- ---------------------------------------------------
CREATE TABLE orphans (
    orphan_id      INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    age            INT NOT NULL,
    gender         ENUM('Male','Female','Other') NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- NEEDS / WISHLIST  ("50 blankets needed")
-- ---------------------------------------------------
CREATE TABLE needs (
    need_id        INT AUTO_INCREMENT PRIMARY KEY,
    item_name      VARCHAR(150) NOT NULL,
    category       ENUM('Cash','Food','Clothes','Books','Medical','Other') NOT NULL,
    quantity       INT DEFAULT 1,
    status         ENUM('open','fulfilled') NOT NULL DEFAULT 'open',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- DONATIONS
-- ---------------------------------------------------
CREATE TABLE donations (
    donation_id       INT AUTO_INCREMENT PRIMARY KEY,
    donor_id          INT NOT NULL,
    need_id           INT DEFAULT NULL,
    donation_type     ENUM('Cash','Food','Clothes','Books','Medical') NOT NULL,
    amount            DECIMAL(10,2) DEFAULT 0.00,
    quantity          VARCHAR(100) DEFAULT NULL,
    notes             VARCHAR(255) DEFAULT NULL,
    razorpay_order_id VARCHAR(50) DEFAULT NULL,
    status            ENUM('pending','success','failed','verified') NOT NULL DEFAULT 'pending',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (need_id) REFERENCES needs(need_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- PAYMENTS  (Razorpay — real API, test-mode keys by default)
-- ---------------------------------------------------
CREATE TABLE payments (
    payment_id      INT AUTO_INCREMENT PRIMARY KEY,
    donation_id     INT NOT NULL,
    method          VARCHAR(30) NOT NULL,
    transaction_ref VARCHAR(50) NOT NULL UNIQUE,
    status          ENUM('success','failed') NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- RECEIPTS
-- ---------------------------------------------------
CREATE TABLE receipts (
    receipt_id   INT AUTO_INCREMENT PRIMARY KEY,
    donation_id  INT NOT NULL UNIQUE,
    issue_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_path    VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- NOTIFICATIONS  (simulated email / SMS log)
-- ---------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    message          VARCHAR(255) NOT NULL,
    type             ENUM('email','sms') NOT NULL DEFAULT 'email',
    is_read          TINYINT(1) NOT NULL DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SEED DATA
-- =====================================================

-- Admin account. Replace this email with your real Google account
-- email before you log in for the first time (see README).
INSERT INTO users (name, email, role) VALUES
('S. Thanvi Sree', 'admin@hopehub.local', 'admin');

-- The one orphanage this project manages. Edit freely from
-- admin/orphanage_profile.php, or change these seed values directly —
-- replace every placeholder below (especially the founder name/bio and
-- the phone numbers) with your real orphanage's actual details.
INSERT INTO orphanage_profile (id, name, address, description, founder_name, founder_bio, registration_number, email, mobile_number, whatsapp_number) VALUES
(1, 'Sunshine Children Home', 'Banjara Hills, Hyderabad',
 'Sunshine Children Home has been providing shelter, education, and care to orphaned and abandoned children in Hyderabad. We currently house over 40 children and rely entirely on community donations to cover food, clothing, education, and medical expenses.',
 'Lakshmi Devi',
 'Lakshmi Devi founded Sunshine Children Home in 2010 after volunteering at a local shelter and seeing how many children in the area had nowhere safe to grow up. She has led the organization for over a decade, focusing on education and healthcare for the children in its care. (Example placeholder — replace with your real founder''s real name and story.)',
 'REG-XXXXXXXX (Trust/Society registration number)',
 'sunshine.home@example.com',
 'xxxxxxxxxx',
 'xxxxxxxxxx');

-- Sample children
INSERT INTO orphans (name, age, gender) VALUES
('Ravi Kumar', 9, 'Male'),
('Anjali Reddy', 7, 'Female'),
('Kiran Das', 11, 'Male'),
('Sneha Rao', 6, 'Female'),
('Arjun Naik', 10, 'Male');

-- Needs / wishlist — still tracked and manageable from the admin panel
-- (Admin & Reporting Module), just no longer displayed on the public homepage.
INSERT INTO needs (item_name, category, quantity, status) VALUES
('Blankets', 'Clothes', 50, 'open'),
('School Notebooks', 'Books', 100, 'open'),
('Rice & Groceries', 'Food', 1, 'open'),
('First-Aid Kits', 'Medical', 10, 'open'),
('Winter Uniforms', 'Clothes', 30, 'open');
