-- ============================================================
-- MHC Parish System — Full Database Migration for Supabase
-- Paste this entire script into Supabase SQL Editor and Run
-- ============================================================

-- Migrations tracking table
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- 1. families
CREATE TABLE IF NOT EXISTS families (
    id BIGSERIAL PRIMARY KEY,
    family_name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    barangay VARCHAR(255),
    city VARCHAR(255) DEFAULT 'Cabuyao',
    province VARCHAR(255) DEFAULT 'Laguna',
    contact_number VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS families_family_name_index ON families(family_name);
CREATE INDEX IF NOT EXISTS families_barangay_index ON families(barangay);

-- 2. parishioners
CREATE TABLE IF NOT EXISTS parishioners (
    id BIGSERIAL PRIMARY KEY,
    family_id BIGINT REFERENCES families(id) ON DELETE SET NULL,
    first_name VARCHAR(255) NOT NULL,
    middle_name VARCHAR(255),
    last_name VARCHAR(255) NOT NULL,
    suffix VARCHAR(255),
    birthdate DATE,
    gender VARCHAR(10) CHECK (gender IN ('male','female','other')),
    civil_status VARCHAR(20) CHECK (civil_status IN ('single','married','widowed','separated','annulled')),
    address VARCHAR(255),
    barangay VARCHAR(255),
    city VARCHAR(255) DEFAULT 'Cabuyao',
    province VARCHAR(255) DEFAULT 'Laguna',
    postal_code VARCHAR(10),
    contact_number VARCHAR(255),
    email VARCHAR(255),
    photo_path VARCHAR(255),
    is_head_of_family BOOLEAN DEFAULT false,
    relationship_to_head VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS parishioners_name_index ON parishioners(last_name, first_name);
CREATE INDEX IF NOT EXISTS parishioners_barangay_index ON parishioners(barangay);
CREATE INDEX IF NOT EXISTS parishioners_family_id_index ON parishioners(family_id);
CREATE INDEX IF NOT EXISTS parishioners_email_index ON parishioners(email);

-- 3. users
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    two_factor_code VARCHAR(6),
    two_factor_expires_at TIMESTAMP,
    parishioner_id BIGINT REFERENCES parishioners(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT true,
    last_login_at TIMESTAMP,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS users_email_index ON users(email);
CREATE INDEX IF NOT EXISTS users_parishioner_id_index ON users(parishioner_id);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions(last_activity);

-- 4. sacramental_records
CREATE TABLE IF NOT EXISTS sacramental_records (
    id BIGSERIAL PRIMARY KEY,
    parishioner_id BIGINT NOT NULL REFERENCES parishioners(id) ON DELETE CASCADE,
    spouse_parishioner_id BIGINT REFERENCES parishioners(id) ON DELETE SET NULL,
    type VARCHAR(30) NOT NULL CHECK (type IN ('baptism','first_communion','confirmation','marriage','death_burial')),
    date_administered DATE NOT NULL,
    celebrant VARCHAR(255) NOT NULL,
    venue VARCHAR(255),
    register_number VARCHAR(255),
    page_number VARCHAR(255),
    line_number VARCHAR(255),
    godparents JSONB,
    witnesses JSONB,
    sponsors JSONB,
    document_references JSONB,
    notes TEXT,
    recorded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    verified_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    verified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS sacramental_records_parishioner_type_index ON sacramental_records(parishioner_id, type);
CREATE INDEX IF NOT EXISTS sacramental_records_type_index ON sacramental_records(type);
CREATE INDEX IF NOT EXISTS sacramental_records_date_index ON sacramental_records(date_administered);

-- 5. bookings
CREATE TABLE IF NOT EXISTS bookings (
    id BIGSERIAL PRIMARY KEY,
    parishioner_id BIGINT NOT NULL REFERENCES parishioners(id) ON DELETE CASCADE,
    booking_type VARCHAR(255) NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','confirmed','completed','cancelled')),
    service_fee DECIMAL(10,2) DEFAULT 0,
    address VARCHAR(255),
    notes TEXT,
    admin_notes TEXT,
    confirmed_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    confirmed_at TIMESTAMP,
    cancelled_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    cancelled_at TIMESTAMP,
    cancellation_reason TEXT,
    reference_number VARCHAR(255) UNIQUE NOT NULL,
    reminder_sent BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS bookings_date_status_index ON bookings(scheduled_date, status);
CREATE INDEX IF NOT EXISTS bookings_parishioner_id_index ON bookings(parishioner_id);
CREATE INDEX IF NOT EXISTS bookings_type_index ON bookings(booking_type);
CREATE INDEX IF NOT EXISTS bookings_reference_index ON bookings(reference_number);

-- 6. certificates (before payments — payments references certificates)
CREATE TABLE IF NOT EXISTS certificates (
    id BIGSERIAL PRIMARY KEY,
    parishioner_id BIGINT NOT NULL REFERENCES parishioners(id) ON DELETE CASCADE,
    sacramental_record_id BIGINT REFERENCES sacramental_records(id) ON DELETE SET NULL,
    type VARCHAR(255) NOT NULL,
    certificate_number VARCHAR(255) UNIQUE NOT NULL,
    issued_date DATE NOT NULL,
    issued_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    purpose VARCHAR(255),
    file_path VARCHAR(255),
    qr_code_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft','issued','released')),
    payment_id BIGINT,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS certificates_parishioner_id_index ON certificates(parishioner_id);
CREATE INDEX IF NOT EXISTS certificates_type_index ON certificates(type);
CREATE INDEX IF NOT EXISTS certificates_number_index ON certificates(certificate_number);

-- 7. payments
CREATE TABLE IF NOT EXISTS payments (
    id BIGSERIAL PRIMARY KEY,
    parishioner_id BIGINT REFERENCES parishioners(id) ON DELETE SET NULL,
    booking_id BIGINT REFERENCES bookings(id) ON DELETE SET NULL,
    certificate_id BIGINT REFERENCES certificates(id) ON DELETE SET NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(20) DEFAULT 'cash' CHECK (payment_method IN ('gcash','maya','cash','bank','card')),
    transaction_type VARCHAR(10) DEFAULT 'debit' CHECK (transaction_type IN ('debit','credit')),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','paid','failed','refunded','voided')),
    reference_number VARCHAR(255) UNIQUE NOT NULL,
    gateway_reference VARCHAR(255),
    submitted_reference VARCHAR(255),
    proof_path VARCHAR(255),
    payer_contact VARCHAR(255),
    gateway_response JSONB,
    paid_at TIMESTAMP,
    receipt_number VARCHAR(255),
    notes TEXT,
    refund_reason TEXT,
    refunded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    refunded_at TIMESTAMP,
    void_reason TEXT,
    voided_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    voided_at TIMESTAMP,
    verified_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    verified_at TIMESTAMP,
    rejection_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS payments_parishioner_id_index ON payments(parishioner_id);
CREATE INDEX IF NOT EXISTS payments_status_index ON payments(status);
CREATE INDEX IF NOT EXISTS payments_paid_at_index ON payments(paid_at);
CREATE INDEX IF NOT EXISTS payments_reference_index ON payments(reference_number);

-- Add FK from certificates to payments (circular ref resolved)
ALTER TABLE certificates DROP CONSTRAINT IF EXISTS certificates_payment_id_fkey;
ALTER TABLE certificates ADD CONSTRAINT certificates_payment_id_fkey
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL;

-- 8. qr_codes
CREATE TABLE IF NOT EXISTS qr_codes (
    id BIGSERIAL PRIMARY KEY,
    qr_codeable_type VARCHAR(255) NOT NULL,
    qr_codeable_id BIGINT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    verification_url VARCHAR(255) NOT NULL,
    qr_image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    scan_count INTEGER DEFAULT 0,
    last_scanned_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS qr_codes_token_index ON qr_codes(token);
CREATE INDEX IF NOT EXISTS qr_codes_morph_index ON qr_codes(qr_codeable_type, qr_codeable_id);

-- 9. audit_logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    auditable_type VARCHAR(255),
    auditable_id BIGINT,
    action VARCHAR(255) NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS audit_logs_user_id_index ON audit_logs(user_id);
CREATE INDEX IF NOT EXISTS audit_logs_action_index ON audit_logs(action);
CREATE INDEX IF NOT EXISTS audit_logs_created_at_index ON audit_logs(created_at);
CREATE INDEX IF NOT EXISTS audit_logs_morph_index ON audit_logs(auditable_type, auditable_id);

-- 10. profile_change_logs
CREATE TABLE IF NOT EXISTS profile_change_logs (
    id BIGSERIAL PRIMARY KEY,
    parishioner_id BIGINT NOT NULL REFERENCES parishioners(id) ON DELETE CASCADE,
    changed_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    field_name VARCHAR(255) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS profile_change_logs_parishioner_id_index ON profile_change_logs(parishioner_id);

-- 11. announcements
CREATE TABLE IF NOT EXISTS announcements (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    is_published BOOLEAN DEFAULT false,
    published_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    category VARCHAR(255) DEFAULT 'general',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS announcements_is_published_index ON announcements(is_published);
CREATE INDEX IF NOT EXISTS announcements_published_at_index ON announcements(published_at);

-- 12. mass_schedules
CREATE TABLE IF NOT EXISTS mass_schedules (
    id BIGSERIAL PRIMARY KEY,
    day_of_week SMALLINT,
    time TIME NOT NULL,
    language VARCHAR(255) DEFAULT 'Filipino',
    celebrant VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    notes TEXT,
    special_date DATE,
    special_title VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 13. services
CREATE TABLE IF NOT EXISTS services (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category VARCHAR(255) NOT NULL,
    description TEXT,
    requirements JSONB,
    fee DECIMAL(10,2) DEFAULT 0,
    duration_minutes INTEGER DEFAULT 60,
    is_bookable BOOLEAN DEFAULT true,
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 14. email_logs
CREATE TABLE IF NOT EXISTS email_logs (
    id BIGSERIAL PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    to_name VARCHAR(255),
    subject VARCHAR(255) NOT NULL,
    template VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('sent','failed','pending')),
    sent_at TIMESTAMP,
    error_message TEXT,
    related_type VARCHAR(255),
    related_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS email_logs_to_email_index ON email_logs(to_email);
CREATE INDEX IF NOT EXISTS email_logs_status_index ON email_logs(status);

-- 15. chat_messages
CREATE TABLE IF NOT EXISTS chat_messages (
    id BIGSERIAL PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    sender VARCHAR(10) NOT NULL CHECK (sender IN ('user','bot','staff')),
    message TEXT NOT NULL,
    intent VARCHAR(255),
    is_escalated BOOLEAN DEFAULT false,
    escalated_to BIGINT REFERENCES users(id) ON DELETE SET NULL,
    escalated_at TIMESTAMP,
    ip_address VARCHAR(45),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS chat_messages_session_id_index ON chat_messages(session_id);

-- 16. permissions (spatie/laravel-permission)
CREATE TABLE IF NOT EXISTS permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(name, guard_name)
);

CREATE TABLE IF NOT EXISTS roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(name, guard_name)
);

CREATE TABLE IF NOT EXISTS model_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);
CREATE INDEX IF NOT EXISTS model_has_permissions_model_index ON model_has_permissions(model_id, model_type);

CREATE TABLE IF NOT EXISTS model_has_roles (
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);
CREATE INDEX IF NOT EXISTS model_has_roles_model_index ON model_has_roles(model_id, model_type);

CREATE TABLE IF NOT EXISTS role_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

-- 17. jobs
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs(queue);

-- 18. events
CREATE TABLE IF NOT EXISTS events (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    event_start TIMESTAMP NOT NULL,
    event_end TIMESTAMP,
    category VARCHAR(255) DEFAULT 'general',
    image_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'published' CHECK (status IN ('draft','published','cancelled')),
    is_featured BOOLEAN DEFAULT false,
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 19. notifications
CREATE TABLE IF NOT EXISTS notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS notifications_notifiable_index ON notifications(notifiable_type, notifiable_id);

-- 20. gallery_items
CREATE TABLE IF NOT EXISTS gallery_items (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255),
    caption TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(255) DEFAULT 'general',
    album VARCHAR(255),
    album_cover VARCHAR(255),
    is_featured BOOLEAN DEFAULT false,
    sort_order INTEGER DEFAULT 0,
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS gallery_items_album_index ON gallery_items(album);

-- 21. livestreams
CREATE TABLE IF NOT EXISTS livestreams (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    youtube_url VARCHAR(255) NOT NULL,
    youtube_id VARCHAR(255),
    type VARCHAR(20) DEFAULT 'recorded' CHECK (type IN ('live','recorded','upcoming')),
    scheduled_at TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    is_featured BOOLEAN DEFAULT false,
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 22. settings
CREATE TABLE IF NOT EXISTS settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

INSERT INTO settings (key, value, created_at, updated_at) VALUES
    ('social_facebook',  '', NOW(), NOW()),
    ('social_messenger', '', NOW(), NOW()),
    ('social_instagram', '', NOW(), NOW()),
    ('social_youtube',   '', NOW(), NOW()),
    ('social_tiktok',    '', NOW(), NOW())
ON CONFLICT (key) DO NOTHING;

-- 23. ledger_entries
CREATE TABLE IF NOT EXISTS ledger_entries (
    id BIGSERIAL PRIMARY KEY,
    type VARCHAR(10) NOT NULL CHECK (type IN ('credit','debit')),
    category VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    entry_date DATE NOT NULL,
    reference_number VARCHAR(100),
    notes TEXT,
    recorded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS ledger_entries_type_date_index ON ledger_entries(type, entry_date);
CREATE INDEX IF NOT EXISTS ledger_entries_date_index ON ledger_entries(entry_date);

-- Mark all migrations as run
INSERT INTO migrations (migration, batch) VALUES
    ('2024_01_01_000001_create_families_table', 1),
    ('2024_01_01_000002_create_parishioners_table', 1),
    ('2024_01_01_000003_create_users_table', 1),
    ('2024_01_01_000004_create_sacramental_records_table', 1),
    ('2024_01_01_000005_create_bookings_table', 1),
    ('2024_01_01_000006_create_payments_table', 1),
    ('2024_01_01_000007_create_certificates_table', 1),
    ('2024_01_01_000008_create_qr_codes_table', 1),
    ('2024_01_01_000009_create_audit_logs_table', 1),
    ('2024_01_01_000010_create_profile_change_logs_table', 1),
    ('2024_01_01_000011_create_announcements_table', 1),
    ('2024_01_01_000012_create_mass_schedules_table', 1),
    ('2024_01_01_000013_create_services_table', 1),
    ('2024_01_01_000014_create_email_logs_table', 1),
    ('2024_01_01_000015_create_chat_messages_table', 1),
    ('2026_04_28_132750_create_permission_tables', 1),
    ('2026_04_29_151751_add_postal_code_to_parishioners_table', 1),
    ('2026_04_30_125604_add_two_factor_to_users_table', 1),
    ('2026_05_20_235351_create_jobs_table', 1),
    ('2026_05_22_200250_add_payment_proof_to_payments_table', 1),
    ('2026_07_01_000001_create_events_table', 1),
    ('2026_07_08_160903_create_notifications_table', 1),
    ('2026_07_09_000001_create_gallery_items_table', 1),
    ('2026_07_09_000002_create_livestreams_table', 1),
    ('2026_07_15_134252_add_album_to_gallery_items_table', 1),
    ('2026_07_15_134312_create_settings_table', 1),
    ('2026_07_23_090224_create_ledger_entries_table', 1),
    ('2026_08_07_133800_add_card_to_payment_method_enum', 1),
    ('2026_08_24_000001_add_transaction_type_to_payments_table', 1)
ON CONFLICT DO NOTHING;
