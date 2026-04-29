-- ============================================================
-- Mary Help of Christians Parish Management System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS mhc_parish CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mhc_parish;

-- Families
CREATE TABLE families (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_name     VARCHAR(255) NOT NULL,
    address         VARCHAR(255),
    barangay        VARCHAR(100),
    city            VARCHAR(100) DEFAULT 'Cabuyao',
    province        VARCHAR(100) DEFAULT 'Laguna',
    contact_number  VARCHAR(20),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    INDEX idx_family_name (family_name),
    INDEX idx_barangay (barangay)
);

-- Parishioners
CREATE TABLE parishioners (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_id             BIGINT UNSIGNED,
    first_name            VARCHAR(100) NOT NULL,
    middle_name           VARCHAR(100),
    last_name             VARCHAR(100) NOT NULL,
    suffix                VARCHAR(20),
    birthdate             DATE,
    gender                ENUM('male','female','other'),
    civil_status          ENUM('single','married','widowed','separated','annulled'),
    address               VARCHAR(255),
    barangay              VARCHAR(100),
    city                  VARCHAR(100) DEFAULT 'Cabuyao',
    province              VARCHAR(100) DEFAULT 'Laguna',
    contact_number        VARCHAR(20),
    email                 VARCHAR(255),
    photo_path            VARCHAR(255),
    is_head_of_family     TINYINT(1) DEFAULT 0,
    relationship_to_head  VARCHAR(100),
    is_active             TINYINT(1) DEFAULT 1,
    notes                 TEXT,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at            TIMESTAMP NULL,
    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE SET NULL,
    INDEX idx_name (last_name, first_name),
    INDEX idx_barangay (barangay),
    INDEX idx_email (email)
);

-- Users
CREATE TABLE users (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(255) NOT NULL,
    email            VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password         VARCHAR(255) NOT NULL,
    parishioner_id   BIGINT UNSIGNED,
    is_active        TINYINT(1) DEFAULT 1,
    last_login_at    TIMESTAMP NULL,
    remember_token   VARCHAR(100),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parishioner_id) REFERENCES parishioners(id) ON DELETE SET NULL
);

-- Sacramental Records
CREATE TABLE sacramental_records (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parishioner_id        BIGINT UNSIGNED NOT NULL,
    spouse_parishioner_id BIGINT UNSIGNED,
    type                  ENUM('baptism','first_communion','confirmation','marriage','death_burial') NOT NULL,
    date_administered     DATE NOT NULL,
    celebrant             VARCHAR(255) NOT NULL,
    venue                 VARCHAR(255),
    register_number       VARCHAR(50),
    page_number           VARCHAR(20),
    line_number           VARCHAR(20),
    godparents            JSON,
    witnesses             JSON,
    sponsors              JSON,
    document_references   JSON,
    notes                 TEXT,
    recorded_by           BIGINT UNSIGNED,
    verified_by           BIGINT UNSIGNED,
    verified_at           TIMESTAMP NULL,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at            TIMESTAMP NULL,
    FOREIGN KEY (parishioner_id) REFERENCES parishioners(id) ON DELETE CASCADE,
    FOREIGN KEY (spouse_parishioner_id) REFERENCES parishioners(id) ON DELETE SET NULL,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_parishioner_type (parishioner_id, type),
    INDEX idx_date (date_administered)
);

-- Bookings
CREATE TABLE bookings (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parishioner_id       BIGINT UNSIGNED NOT NULL,
    booking_type         VARCHAR(100) NOT NULL,
    scheduled_date       DATE NOT NULL,
    scheduled_time       TIME,
    status               ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    service_fee          DECIMAL(10,2) DEFAULT 0,
    address              VARCHAR(255),
    notes                TEXT,
    admin_notes          TEXT,
    confirmed_by         BIGINT UNSIGNED,
    confirmed_at         TIMESTAMP NULL,
    cancelled_by         BIGINT UNSIGNED,
    cancelled_at         TIMESTAMP NULL,
    cancellation_reason  TEXT,
    reference_number     VARCHAR(50) UNIQUE NOT NULL,
    reminder_sent        TINYINT(1) DEFAULT 0,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at           TIMESTAMP NULL,
    FOREIGN KEY (parishioner_id) REFERENCES parishioners(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_date_status (scheduled_date, status),
    INDEX idx_reference (reference_number)
);

-- Payments
CREATE TABLE payments (
    id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parishioner_id     BIGINT UNSIGNED,
    booking_id         BIGINT UNSIGNED,
    certificate_id     BIGINT UNSIGNED,
    amount             DECIMAL(10,2) NOT NULL,
    payment_method     ENUM('gcash','maya','cash','bank') DEFAULT 'cash',
    status             ENUM('pending','paid','failed','refunded','voided') DEFAULT 'pending',
    reference_number   VARCHAR(50) UNIQUE NOT NULL,
    gateway_reference  VARCHAR(255),
    gateway_response   JSON,
    paid_at            TIMESTAMP NULL,
    receipt_number     VARCHAR(50),
    notes              TEXT,
    refund_reason      TEXT,
    refunded_by        BIGINT UNSIGNED,
    refunded_at        TIMESTAMP NULL,
    void_reason        TEXT,
    voided_by          BIGINT UNSIGNED,
    voided_at          TIMESTAMP NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parishioner_id) REFERENCES parishioners(id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_paid_at (paid_at)
);

-- Certificates
CREATE TABLE certificates (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parishioner_id        BIGINT UNSIGNED NOT NULL,
    sacramental_record_id BIGINT UNSIGNED,
    type                  VARCHAR(50) NOT NULL,
    certificate_number    VARCHAR(50) UNIQUE NOT NULL,
    issued_date           DATE NOT NULL,
    issued_by             BIGINT UNSIGNED,
    purpose               VARCHAR(255),
    file_path             VARCHAR(255),
    qr_code_path          VARCHAR(255),
    status                ENUM('draft','issued','released') DEFAULT 'draft',
    payment_id            BIGINT UNSIGNED,
    notes                 TEXT,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parishioner_id) REFERENCES parishioners(id) ON DELETE CASCADE,
    FOREIGN KEY (sacramental_record_id) REFERENCES sacramental_records(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_cert_number (certificate_number)
);

-- QR Codes
CREATE TABLE qr_codes (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    qr_codeable_type  VARCHAR(255) NOT NULL,
    qr_codeable_id    BIGINT UNSIGNED NOT NULL,
    token             VARCHAR(64) UNIQUE NOT NULL,
    verification_url  VARCHAR(500) NOT NULL,
    qr_image_path     VARCHAR(255),
    is_active         TINYINT(1) DEFAULT 1,
    scan_count        INT UNSIGNED DEFAULT 0,
    last_scanned_at   TIMESTAMP NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_codeable (qr_codeable_type, qr_codeable_id)
);

-- Audit Logs
CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED,
    auditable_type  VARCHAR(255),
    auditable_id    BIGINT UNSIGNED,
    action          VARCHAR(100) NOT NULL,
    old_values      JSON,
    new_values      JSON,
    ip_address      VARCHAR(45),
    user_agent      TEXT,
    description     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- Profile Change Logs
CREATE TABLE profile_change_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parishioner_id  BIGINT UNSIGNED NOT NULL,
    changed_by      BIGINT UNSIGNED,
    field_name      VARCHAR(100) NOT NULL,
    old_value       TEXT,
    new_value       TEXT,
    reason          VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parishioner_id) REFERENCES parishioners(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Announcements
CREATE TABLE announcements (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    content      LONGTEXT NOT NULL,
    image_path   VARCHAR(255),
    is_published TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP NULL,
    expires_at   TIMESTAMP NULL,
    created_by   BIGINT UNSIGNED,
    category     VARCHAR(50) DEFAULT 'general',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Mass Schedules
CREATE TABLE mass_schedules (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    day_of_week   TINYINT COMMENT '0=Sunday, 6=Saturday',
    time          TIME NOT NULL,
    language      VARCHAR(50) DEFAULT 'Filipino',
    celebrant     VARCHAR(255),
    is_active     TINYINT(1) DEFAULT 1,
    notes         TEXT,
    special_date  DATE,
    special_title VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services
CREATE TABLE services (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(255) NOT NULL,
    slug             VARCHAR(100) UNIQUE NOT NULL,
    category         VARCHAR(100) NOT NULL,
    description      TEXT,
    requirements     JSON,
    fee              DECIMAL(10,2) DEFAULT 0,
    duration_minutes INT UNSIGNED DEFAULT 60,
    is_bookable      TINYINT(1) DEFAULT 1,
    is_active        TINYINT(1) DEFAULT 1,
    sort_order       INT UNSIGNED DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email Logs
CREATE TABLE email_logs (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    to_email      VARCHAR(255) NOT NULL,
    to_name       VARCHAR(255),
    subject       VARCHAR(255) NOT NULL,
    template      VARCHAR(100),
    status        ENUM('sent','failed','pending') DEFAULT 'pending',
    sent_at       TIMESTAMP NULL,
    error_message TEXT,
    related_type  VARCHAR(255),
    related_id    BIGINT UNSIGNED,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Chat Messages
CREATE TABLE chat_messages (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id    VARCHAR(100) NOT NULL,
    sender        ENUM('user','bot','staff') NOT NULL,
    message       TEXT NOT NULL,
    intent        VARCHAR(100),
    is_escalated  TINYINT(1) DEFAULT 0,
    escalated_to  BIGINT UNSIGNED,
    escalated_at  TIMESTAMP NULL,
    ip_address    VARCHAR(45),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (escalated_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id)
);
