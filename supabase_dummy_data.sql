-- ============================================================
-- MHC Parish System — Dummy Data SQL for Supabase
-- Run this in: Supabase Dashboard → SQL Editor → New query
--
-- SAFE TO RUN: Uses INSERT ... WHERE NOT EXISTS / ON CONFLICT DO NOTHING
-- Will NOT delete or overwrite existing production data.
--
-- RECORDS CREATED:
--   families             : 20
--   parishioners         : 50
--   users (portal)       : 20  (demo.parishioner001-020@example.com / DemoPass@2026)
--   sacramental_records  : 40
--   bookings             : 45  (all statuses, 6 months)
--   payments             : 33  (all methods, all statuses)
--   certificates         : 25
--   ledger_entries       : 60  (6 months, credit + debit)
--   events               : 8
--   announcements        : 8
--   livestreams          : 5
-- ============================================================

BEGIN;

-- ============================================================
-- STEP 1: FAMILIES (20 records)
-- ============================================================
INSERT INTO families (family_name, address, barangay, city, province, contact_number, notes, created_at, updated_at)
SELECT v.family_name, v.address, v.barangay, 'Cabuyao', 'Laguna', v.contact_number,
       '[DEMO] Demo family record.', NOW(), NOW()
FROM (VALUES
  ('Dela Cruz Family',   'Blk 3 Lot 12, Southville 1',  'Niugan',      '09171000001'),
  ('Reyes Family',       'Blk 7 Lot 5, Phase 2',        'Banay-banay', '09171000002'),
  ('Santos Family',      'Blk 12 Lot 8, Phase 3',       'Pulo',        '09171000003'),
  ('Bautista Family',    'Blk 1 Lot 20, Southville 2',  'Sala',        '09171000004'),
  ('Flores Family',      'Blk 9 Lot 3, Phase 4',        'Marinig',     '09171000005'),
  ('Torres Family',      'Blk 5 Lot 17, Southville 1',  'Niugan',      '09171000006'),
  ('Villanueva Family',  'Blk 14 Lot 6, Phase 1',       'Diezmo',      '09171000007'),
  ('Aquino Family',      'Blk 2 Lot 9, Southville 3',   'Mamatid',     '09171000008'),
  ('Garcia Family',      'Blk 6 Lot 11, Phase 2',       'Bigaa',       '09171000009'),
  ('Mendoza Family',     'Blk 8 Lot 4, Southville 4',   'Casile',      '09171000010'),
  ('Cruz Family',        'Blk 11 Lot 7, Phase 5',       'Butong',      '09171000011'),
  ('Ramos Family',       'Blk 4 Lot 15, Southville 1',  'Niugan',      '09171000012'),
  ('Castillo Family',    'Blk 16 Lot 2, Phase 3',       'Gulod',       '09171000013'),
  ('Morales Family',     'Blk 10 Lot 18, Southville 2', 'Pittland',    '09171000014'),
  ('Gonzales Family',    'Blk 13 Lot 1, Phase 1',       'Sucol',       '09171000015'),
  ('Hernandez Family',   'Blk 15 Lot 13, Southville 3', 'Sala',        '09171000016'),
  ('Pascual Family',     'Blk 17 Lot 10, Phase 2',      'Niugan',      '09171000017'),
  ('Soriano Family',     'Blk 18 Lot 14, Southville 4', 'Banay-banay', '09171000018'),
  ('Navarro Family',     'Blk 19 Lot 16, Phase 4',      'Pulo',        '09171000019'),
  ('Salazar Family',     'Blk 20 Lot 19, Southville 5', 'Marinig',     '09171000020')
) AS v(family_name, address, barangay, contact_number)
WHERE NOT EXISTS (SELECT 1 FROM families WHERE family_name = v.family_name);

-- ============================================================
-- STEP 2: PARISHIONERS (50 records)
-- ============================================================
INSERT INTO parishioners (
  family_id, first_name, middle_name, last_name, suffix, birthdate,
  gender, civil_status, address, barangay, city, province, postal_code,
  contact_number, email, is_head_of_family, relationship_to_head,
  is_active, notes, created_at, updated_at
)
SELECT
  f.id,
  v.first_name, v.middle_name, v.last_name, v.suffix,
  v.birthdate::date, v.gender, v.civil_status,
  'Demo Street, Southville 1', v.barangay, 'Cabuyao', 'Laguna', '4025',
  v.contact_number, v.email,
  v.is_head::boolean, v.relation,
  TRUE, '[DEMO] Demo parishioner — safe to delete.',
  v.created_at::timestamp, v.created_at::timestamp
FROM (VALUES
  ('Dela Cruz Family', 'Antonio',    'Santos',     'Dela Cruz',  NULL,  '1985-03-12','male',  'married',   'Niugan',      '09181000001','demo.parishioner001@example.com','true', 'Head',   '2025-01-15'),
  ('Dela Cruz Family', 'Marilou',    'Reyes',      'Dela Cruz',  NULL,  '1988-07-25','female','married',   'Niugan',      '09181000002','demo.parishioner002@example.com','false','Spouse', '2025-01-15'),
  ('Reyes Family',     'Jose',       'Bautista',   'Reyes',      NULL,  '1972-11-08','male',  'widowed',   'Banay-banay', '09181000003','demo.parishioner003@example.com','true', 'Head',   '2025-02-10'),
  ('Santos Family',    'Rosa',       'Flores',     'Santos',     NULL,  '2000-01-30','female','single',    'Pulo',        '09181000004','demo.parishioner004@example.com','true', 'Head',   '2025-02-20'),
  ('Bautista Family',  'Miguel',     'Cruz',       'Bautista',   NULL,  '1968-06-14','male',  'married',   'Sala',        '09181000005','demo.parishioner005@example.com','true', 'Head',   '2025-03-05'),
  ('Flores Family',    'Elena',      'Reyes',      'Flores',     NULL,  '1995-09-03','female','single',    'Marinig',     '09181000006','demo.parishioner006@example.com','false','Child',  '2025-03-10'),
  ('Torres Family',    'Carlos',     'Villanueva', 'Torres',     NULL,  '1980-12-19','male',  'married',   'Niugan',      '09181000007','demo.parishioner007@example.com','true', 'Head',   '2025-03-20'),
  ('Villanueva Family','Luz',        'Aquino',     'Villanueva', NULL,  '2005-04-22','female','single',    'Diezmo',      '09181000008','demo.parishioner008@example.com','false','Child',  '2025-04-01'),
  ('Aquino Family',    'Ramon',      'Torres',     'Aquino',     NULL,  '1958-08-07','male',  'married',   'Mamatid',     '09181000009','demo.parishioner009@example.com','true', 'Head',   '2025-04-15'),
  ('Garcia Family',    'Cecilia',    'Bautista',   'Garcia',     NULL,  '1943-02-11','female','widowed',   'Bigaa',       '09181000010','demo.parishioner010@example.com','true', 'Head',   '2025-05-01'),
  ('Mendoza Family',   'Eduardo',    'Santos',     'Mendoza',    'Jr.', '1977-05-16','male',  'married',   'Casile',      '09181000011','demo.parishioner011@example.com','true', 'Head',   '2025-05-10'),
  ('Cruz Family',      'Maricel',    'Diaz',       'Cruz',       NULL,  '1983-09-22','female','married',   'Butong',      '09181000012','demo.parishioner012@example.com','true', 'Head',   '2025-05-20'),
  ('Ramos Family',     'Fernando',   'Ocampo',     'Ramos',      NULL,  '1960-12-03','male',  'separated', 'Niugan',      '09181000013','demo.parishioner013@example.com','true', 'Head',   '2025-06-01'),
  ('Castillo Family',  'Josephine',  'Navarro',    'Castillo',   NULL,  '1990-03-18','female','single',    'Gulod',       '09181000014','demo.parishioner014@example.com','true', 'Head',   '2025-06-10'),
  ('Morales Family',   'Renato',     'Soriano',    'Morales',    NULL,  '1975-07-29','male',  'married',   'Pittland',    '09181000015','demo.parishioner015@example.com','true', 'Head',   '2025-07-01'),
  ('Gonzales Family',  'Evelyn',     'Pascual',    'Gonzales',   NULL,  '1988-11-14','female','married',   'Sucol',       '09181000016','demo.parishioner016@example.com','false','Spouse', '2025-07-15'),
  ('Hernandez Family', 'Danilo',     'Martinez',   'Hernandez',  NULL,  '2001-09-14','male',  'single',    'Sala',        '09181000017','demo.parishioner017@example.com','false','Child',  '2025-08-01'),
  ('Pascual Family',   'Teresita',   'Aguilar',    'Pascual',    NULL,  '1965-04-07','female','widowed',   'Niugan',      '09181000018','demo.parishioner018@example.com','true', 'Head',   '2025-08-15'),
  ('Soriano Family',   'Oscar',      'Lim',        'Soriano',    NULL,  '1993-12-25','male',  'single',    'Banay-banay', '09181000019','demo.parishioner019@example.com','true', 'Head',   '2025-09-01'),
  ('Salazar Family',   'Patricia',   'Tan',        'Salazar',    NULL,  '1970-06-30','female','annulled',  'Marinig',     '09181000020','demo.parishioner020@example.com','true', 'Head',   '2025-09-15'),
  -- Additional parishioners
  ('Dela Cruz Family', 'Marco',      'Santos',     'Dela Cruz',  NULL,  '2010-02-14','male',  'single',    'Niugan',      '09182000001',NULL,                             'false','Child',  '2025-10-01'),
  ('Reyes Family',     'Ana',        'Bautista',   'Reyes',      NULL,  '1992-08-19','female','married',   'Banay-banay', '09182000002',NULL,                             'false','Child',  '2025-10-10'),
  ('Santos Family',    'Pedro',      'Torres',     'Santos',     NULL,  '1955-03-25','male',  'married',   'Pulo',        '09182000003',NULL,                             'false','Spouse', '2025-10-20'),
  ('Bautista Family',  'Gloria',     'Cruz',       'Bautista',   NULL,  '1975-11-08','female','married',   'Sala',        '09182000004',NULL,                             'false','Spouse', '2025-11-01'),
  ('Flores Family',    'Rodrigo',    'Mendoza',    'Flores',     NULL,  '1948-07-12','male',  'widowed',   'Marinig',     '09182000005',NULL,                             'false','Parent', '2025-11-10'),
  ('Torres Family',    'Melinda',    'Garcia',     'Torres',     NULL,  '1982-04-03','female','married',   'Niugan',      '09182000006',NULL,                             'false','Spouse', '2025-11-20'),
  ('Aquino Family',    'Arturo',     'Hernandez',  'Aquino',     NULL,  '1988-09-17','male',  'married',   'Mamatid',     '09182000007',NULL,                             'false','Spouse', '2025-12-01'),
  ('Mendoza Family',   'Cecilia',    'Ramos',      'Mendoza',    NULL,  '1980-01-28','female','married',   'Casile',      '09182000008',NULL,                             'false','Spouse', '2025-12-15'),
  ('Cruz Family',      'Domingo',    'Castillo',   'Cruz',       NULL,  '1940-05-20','male',  'widowed',   'Butong',      '09182000009',NULL,                             'false','Parent', '2026-01-05'),
  ('Garcia Family',    'Florencia',  'Morales',    'Garcia',     NULL,  '1963-10-11','female','married',   'Bigaa',       '09182000010',NULL,                             'false','Spouse', '2026-01-15'),
  ('Ramos Family',     'Gregorio',   'Gonzales',   'Ramos',      NULL,  '1970-02-07','male',  'married',   'Niugan',      '09182000011',NULL,                             'false','Spouse', '2026-02-01'),
  ('Castillo Family',  'Herminia',   'Santos',     'Castillo',   NULL,  '1997-06-15','female','single',    'Gulod',       '09182000012',NULL,                             'false','Child',  '2026-02-15'),
  ('Morales Family',   'Isidro',     'Reyes',      'Morales',    NULL,  '1985-12-22','male',  'married',   'Pittland',    '09182000013',NULL,                             'false','Spouse', '2026-03-01'),
  ('Gonzales Family',  'Juliana',    'Cruz',       'Gonzales',   NULL,  '2003-03-30','female','single',    'Sucol',       '09182000014',NULL,                             'false','Child',  '2026-03-15'),
  ('Hernandez Family', 'Kevin',      'Torres',     'Hernandez',  NULL,  '2007-09-05','male',  'single',    'Sala',        '09182000015',NULL,                             'false','Child',  '2026-04-01'),
  ('Pascual Family',   'Lorena',     'Bautista',   'Pascual',    NULL,  '1960-07-18','female','widowed',   'Niugan',      '09182000016',NULL,                             'false','Sister', '2026-04-10'),
  ('Soriano Family',   'Manuel',     'Flores',     'Soriano',    NULL,  '1978-01-09','male',  'married',   'Banay-banay', '09182000017',NULL,                             'false','Spouse', '2026-04-20'),
  ('Salazar Family',   'Natividad',  'Aquino',     'Salazar',    NULL,  '1955-08-24','female','widowed',   'Marinig',     '09182000018',NULL,                             'false','Parent', '2026-05-01'),
  ('Dela Cruz Family', 'Quirino',    'Dela Cruz',  'Santos',     NULL,  '2012-11-16','male',  'single',    'Niugan',      '09182000019',NULL,                             'false','Child',  '2026-05-15'),
  ('Reyes Family',     'Remedios',   'Torres',     'Reyes',      NULL,  '1973-04-02','female','married',   'Banay-banay', '09182000020',NULL,                             'false','Sister', '2026-05-20'),
  ('Bautista Family',  'Salvador',   'Mendoza',    'Bautista',   NULL,  '1965-06-30','male',  'married',   'Sala',        '09183000001',NULL,                             'false','Brother','2026-06-01'),
  ('Flores Family',    'Teofilo',    'Cruz',       'Flores',     NULL,  '1990-10-05','male',  'single',    'Marinig',     '09183000002',NULL,                             'false','Child',  '2026-06-10'),
  ('Torres Family',    'Veronica',   'Garcia',     'Torres',     NULL,  '2001-02-18','female','single',    'Niugan',      '09183000003',NULL,                             'false','Child',  '2026-06-20'),
  ('Aquino Family',    'Wilfredo',   'Santos',     'Aquino',     NULL,  '1958-11-27','male',  'married',   'Mamatid',     '09183000004',NULL,                             'false','Brother','2026-07-01'),
  ('Cruz Family',      'Yolanda',    'Ramos',      'Cruz',       NULL,  '1985-03-15','female','married',   'Butong',      '09183000005',NULL,                             'false','Spouse', '2026-07-10'),
  ('Ramos Family',     'Zenaida',    'Castillo',   'Ramos',      NULL,  '1962-08-09','female','separated', 'Niugan',      '09183000006',NULL,                             'false','Sister', '2026-07-20'),
  ('Garcia Family',    'Ariel',      'Morales',    'Garcia',     NULL,  '1995-05-14','male',  'single',    'Bigaa',       '09183000007',NULL,                             'false','Child',  '2026-08-01'),
  ('Mendoza Family',   'Bernadette', 'Gonzales',   'Mendoza',    NULL,  '2008-01-22','female','single',    'Casile',      '09183000008',NULL,                             'false','Child',  '2026-08-10'),
  ('Gonzales Family',  'Cesar',      'Hernandez',  'Gonzales',   NULL,  '1980-07-03','male',  'married',   'Sucol',       '09183000009',NULL,                             'false','Spouse', '2026-08-20'),
  ('Pascual Family',   'Diana',      'Pascual',    'Santos',     NULL,  '1993-12-12','female','single',    'Niugan',      '09183000010',NULL,                             'false','Niece',  '2026-09-01')
) AS v(family_name, first_name, middle_name, last_name, suffix, birthdate,
       gender, civil_status, barangay, contact_number, email, is_head, relation, created_at)
INNER JOIN families f ON f.family_name = v.family_name
WHERE NOT EXISTS (
  SELECT 1 FROM parishioners p WHERE p.contact_number = v.contact_number
);

-- ============================================================
-- STEP 3: USERS for parishioner portal login (first 20 only)
-- Login: demo.parishioner001@example.com / DemoPass@2026
-- ============================================================
DO $$
DECLARE
  p_id  BIGINT;
  r_id  BIGINT;
  u_id  BIGINT;
  i     INT;
  e     TEXT;
  emails TEXT[] := ARRAY[
    'demo.parishioner001@example.com','demo.parishioner002@example.com',
    'demo.parishioner003@example.com','demo.parishioner004@example.com',
    'demo.parishioner005@example.com','demo.parishioner006@example.com',
    'demo.parishioner007@example.com','demo.parishioner008@example.com',
    'demo.parishioner009@example.com','demo.parishioner010@example.com',
    'demo.parishioner011@example.com','demo.parishioner012@example.com',
    'demo.parishioner013@example.com','demo.parishioner014@example.com',
    'demo.parishioner015@example.com','demo.parishioner016@example.com',
    'demo.parishioner017@example.com','demo.parishioner018@example.com',
    'demo.parishioner019@example.com','demo.parishioner020@example.com'
  ];
  names TEXT[] := ARRAY[
    'Antonio Dela Cruz','Marilou Dela Cruz','Jose Reyes','Rosa Santos',
    'Miguel Bautista','Elena Flores','Carlos Torres','Luz Villanueva',
    'Ramon Aquino','Cecilia Garcia','Eduardo Mendoza','Maricel Cruz',
    'Fernando Ramos','Josephine Castillo','Renato Morales','Evelyn Gonzales',
    'Danilo Hernandez','Teresita Pascual','Oscar Soriano','Patricia Salazar'
  ];
  -- bcrypt hash of 'DemoPass@2026'
  pw_hash TEXT := '$2y$12$PH4mOFHNOJT./5UkKBHyOehsJ4fmE8mfuvqMvTOPMNl3RzGnHJ95C';
BEGIN
  SELECT id INTO r_id FROM roles WHERE name = 'parishioner' LIMIT 1;
  FOR i IN 1..20 LOOP
    e := emails[i];
    SELECT id INTO p_id FROM parishioners WHERE email = e LIMIT 1;
    IF p_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE email = e) THEN
      INSERT INTO users (name, email, password, parishioner_id, is_active, created_at, updated_at)
      VALUES (names[i], e, pw_hash, p_id, TRUE, NOW(), NOW())
      RETURNING id INTO u_id;
      IF r_id IS NOT NULL THEN
        INSERT INTO model_has_roles (role_id, model_type, model_id)
        VALUES (r_id, 'App\Models\User', u_id)
        ON CONFLICT DO NOTHING;
      END IF;
    END IF;
  END LOOP;
END $$;

-- ============================================================
-- STEP 4: SACRAMENTAL RECORDS (40 records)
-- ============================================================
INSERT INTO sacramental_records (
  parishioner_id, type, date_administered, celebrant, venue,
  register_number, page_number, line_number, godparents, notes,
  created_at, updated_at
)
SELECT
  p.id, v.type, v.date_admin::date,
  'Rev. Fr. Erwin S. Sanchez', 'Mary Help of Christians Parish',
  v.register_number, v.page_num, v.line_num,
  v.godparents::jsonb,
  '[DEMO] Demo sacramental record.',
  v.date_admin::timestamp, v.date_admin::timestamp
FROM (VALUES
  ('09181000001','baptism',        '1985-03-24','B-1985-044','11','3','["Manuel Santos","Lucia Dela Cruz"]'),
  ('09181000002','baptism',        '1988-08-05','B-1988-067','17','1','["Pedro Reyes","Carmen Santos"]'),
  ('09181000003','baptism',        '1972-11-19','B-1972-088','22','6','["Arturo Bautista","Nena Cruz"]'),
  ('09181000005','baptism',        '1968-06-23','B-1968-031','8', '9','["Alfonso Aquino","Rosario Mendez"]'),
  ('09181000010','baptism',        '1943-02-20','B-1943-012','3', '4','["Domingo Bautista","Fe Santos"]'),
  ('09181000007','baptism',        '1980-12-25','B-1980-115','15','5','["Roberto Cruz","Alma Santos"]'),
  ('09181000009','baptism',        '1958-08-10','B-1958-077','20','2','["Ramon Torres","Lucia Aquino"]'),
  ('09181000011','baptism',        '1977-05-20','B-1977-062','18','7','["Eduardo Cruz","Maria Santos"]'),
  ('09182000001','baptism',        '2010-02-20','B-2010-023','6', '1','["Jose Torres","Ana Garcia"]'),
  ('09182000003','baptism',        '1955-03-28','B-1955-031','9', '8','["Pedro Bautista","Rosa Flores"]'),
  ('09181000001','first_communion','1995-05-12','FC-1995-018','5','7','null'),
  ('09181000002','first_communion','2000-05-07','FC-2000-022','6','3','null'),
  ('09181000005','first_communion','1978-05-14','FC-1978-009','2','11','null'),
  ('09181000007','first_communion','1992-05-09','FC-1992-015','4','6','null'),
  ('09182000001','first_communion','2018-06-03','FC-2018-031','8','2','null'),
  ('09181000001','confirmation',   '2001-04-07','C-2001-041','10','5','null'),
  ('09181000003','confirmation',   '1988-04-03','C-1988-019','5', '8','null'),
  ('09181000007','confirmation',   '1996-03-30','C-1996-033','8', '2','null'),
  ('09181000009','confirmation',   '1980-04-12','C-1980-027','7', '4','null'),
  ('09181000011','confirmation',   '1993-04-17','C-1993-039','9', '6','null'),
  ('09181000001','marriage',       '2010-02-14','M-2010-003','1','3','null'),
  ('09181000005','marriage',       '1995-06-17','M-1995-007','2','7','null'),
  ('09181000007','marriage',       '2005-11-26','M-2005-011','3','2','null'),
  ('09181000009','marriage',       '1990-08-05','M-1990-004','1','5','null'),
  ('09181000015','marriage',       '2003-04-19','M-2003-008','2','1','null'),
  ('09181000010','death_burial',   '2026-06-03','D-2606-001','1','1','null'),
  ('09182000009','death_burial',   '2026-07-08','D-2607-002','1','2','null'),
  ('09181000006','baptism',        '2026-04-14','B-2604-001','1','1','["Demo Godfather","Demo Godmother"]'),
  ('09181000008','first_communion','2026-05-10','FC-2605-001','1','1','null'),
  ('09181000012','baptism',        '2026-06-21','B-2606-001','1','2','["Carlos Santos","Maria Cruz"]'),
  ('09181000014','baptism',        '2026-07-13','B-2607-001','1','3','["Jose Flores","Ana Reyes"]'),
  ('09181000016','baptism',        '2026-07-20','B-2607-002','2','1','["Ramon Aquino","Luz Torres"]'),
  ('09181000017','first_communion','2026-07-05','FC-2607-001','1','1','null'),
  ('09181000019','confirmation',   '2026-07-26','C-2607-001','1','1','null'),
  ('09181000013','baptism',        '2026-08-03','B-2608-001','1','1','["Eduardo Garcia","Carmen Ramos"]'),
  ('09181000015','first_communion','2026-08-17','FC-2608-001','1','2','null'),
  ('09181000018','baptism',        '2026-08-24','B-2608-002','2','1','["Pedro Cruz","Rosa Santos"]'),
  ('09181000020','baptism',        '2026-09-07','B-2609-001','1','1','["Manuel Torres","Ana Bautista"]'),
  ('09182000007','marriage',       '2026-08-16','M-2608-001','1','1','null'),
  ('09182000011','baptism',        '2026-09-14','B-2609-002','1','2','["Jose Mendoza","Maria Garcia"]')
) AS v(contact_number, type, date_admin, register_number, page_num, line_num, godparents)
INNER JOIN parishioners p ON p.contact_number = v.contact_number
WHERE NOT EXISTS (
  SELECT 1 FROM sacramental_records sr
  WHERE sr.parishioner_id = p.id
    AND sr.type = v.type
    AND sr.register_number = v.register_number
);

-- ============================================================
-- STEP 5: BOOKINGS (45 records)
-- reference_number generated as 'BK-' + first 12 chars of MD5 hash
-- ============================================================
INSERT INTO bookings (
  parishioner_id, booking_type, scheduled_date, scheduled_time,
  status, service_fee, reference_number, notes, reminder_sent,
  created_at, updated_at
)
SELECT
  p.id,
  v.booking_type,
  v.sched_date::date,
  v.sched_time::time,
  v.status,
  v.fee::numeric,
  'BK-' || UPPER(SUBSTRING(MD5(v.contact_number || v.booking_type || v.sched_date), 1, 12)),
  '[DEMO] Demo booking — safe to delete.',
  TRUE,
  (v.sched_date::date - INTERVAL '7 days')::timestamp,
  v.sched_date::timestamp
FROM (VALUES
  -- April 2026
  ('09181000001','baptism',           '2026-04-05','09:00','completed', 500),
  ('09181000002','mass_intention',    '2026-04-08','06:00','completed', 200),
  ('09181000003','house_blessing',    '2026-04-12','10:00','completed', 300),
  ('09181000004','pre_baptismal',     '2026-04-15','14:00','cancelled', 150),
  ('09181000005','wedding',           '2026-04-19','10:00','completed', 3000),
  -- May 2026
  ('09181000006','confirmation_catechesis','2026-05-02','08:00','completed',250),
  ('09181000007','baptism',           '2026-05-11','09:00','completed', 500),
  ('09181000008','mass_intention',    '2026-05-14','06:00','completed', 200),
  ('09181000009','sick_call',         '2026-05-18','15:00','completed', 0),
  ('09181000010','car_blessing',      '2026-05-22','09:00','cancelled', 200),
  ('09181000011','funeral_mass',      '2026-05-25','08:00','completed', 1500),
  -- June 2026
  ('09181000012','wedding',           '2026-06-07','10:00','completed', 3000),
  ('09181000013','house_blessing',    '2026-06-13','10:00','completed', 300),
  ('09181000014','baptism',           '2026-06-21','09:00','completed', 500),
  ('09181000015','pre_marriage',      '2026-06-27','14:00','completed', 500),
  ('09181000016','mass_intention',    '2026-06-28','06:00','completed', 200),
  -- July 2026
  ('09181000017','business_blessing', '2026-07-04','09:00','completed', 400),
  ('09181000018','baptism',           '2026-07-11','09:00','completed', 500),
  ('09181000019','mass_intention',    '2026-07-14','06:00','completed', 200),
  ('09181000020','funeral_mass',      '2026-07-18','08:00','completed', 1500),
  ('09181000001','house_blessing',    '2026-07-22','10:00','completed', 300),
  ('09181000002','car_blessing',      '2026-07-25','09:00','completed', 200),
  ('09181000003','pre_baptismal',     '2026-07-26','14:00','completed', 150),
  ('09181000004','wedding',           '2026-07-12','10:00','completed', 3000),
  -- August 2026
  ('09181000005','baptism',           '2026-08-02','09:00','completed', 500),
  ('09181000006','mass_intention',    '2026-08-06','06:00','completed', 200),
  ('09181000007','confirmation_catechesis','2026-08-09','08:00','completed',250),
  ('09181000008','sick_call',         '2026-08-16','15:00','completed', 0),
  ('09181000009','house_blessing',    '2026-08-18','10:00','completed', 300),
  ('09181000010','funeral_mass',      '2026-08-23','08:00','completed', 1500),
  ('09181000011','wedding',           '2026-08-30','10:00','completed', 3000),
  ('09181000012','baptism',           '2026-08-17','09:00','confirmed', 500),
  -- September 2026
  ('09181000013','baptism',           '2026-09-07','09:00','confirmed', 500),
  ('09181000014','mass_intention',    '2026-09-09','06:00','confirmed', 200),
  ('09181000015','house_blessing',    '2026-09-13','10:00','confirmed', 300),
  ('09181000016','pre_baptismal',     '2026-09-20','14:00','pending',   150),
  ('09181000017','wedding',           '2026-09-27','10:00','pending',   3000),
  ('09181000018','car_blessing',      '2026-09-21','09:00','pending',   200),
  -- Upcoming October 2026
  ('09181000019','baptism',           '2026-10-04','09:00','confirmed', 500),
  ('09181000020','wedding',           '2026-10-10','10:00','pending',   3000),
  ('09181000001','house_blessing',    '2026-10-11','10:00','pending',   300),
  ('09181000002','pre_marriage',      '2026-10-18','14:00','pending',   500),
  ('09181000003','mass_intention',    '2026-10-05','06:00','confirmed', 200),
  ('09181000004','funeral_mass',      '2026-10-12','08:00','confirmed', 1500)
) AS v(contact_number, booking_type, sched_date, sched_time, status, fee)
INNER JOIN parishioners p ON p.contact_number = v.contact_number
WHERE NOT EXISTS (
  SELECT 1 FROM bookings b
  WHERE b.parishioner_id = p.id
    AND b.booking_type = v.booking_type
    AND b.scheduled_date = v.sched_date::date
);

-- ============================================================
-- STEP 6: PAYMENTS (33 records)
-- No real PayMongo IDs — demo only
-- ============================================================
INSERT INTO payments (
  parishioner_id, booking_id, amount, payment_method, transaction_type,
  status, paid_at, verified_at, notes, created_at, updated_at
)
SELECT
  b.parishioner_id,
  b.id,
  b.service_fee,
  v.method,
  'debit',
  v.status,
  CASE WHEN v.status = 'paid'
       THEN (b.scheduled_date + INTERVAL '1 day')::timestamp
       ELSE NULL END,
  CASE WHEN v.status = 'paid'
       THEN (b.scheduled_date + INTERVAL '1 day')::timestamp
       ELSE NULL END,
  '[DEMO] Demo payment — no real PayMongo transaction.',
  b.created_at,
  b.created_at
FROM (VALUES
  ('09181000001','baptism',                '2026-04-05','cash',  'paid'),
  ('09181000002','mass_intention',         '2026-04-08','gcash', 'paid'),
  ('09181000003','house_blessing',         '2026-04-12','maya',  'paid'),
  ('09181000005','wedding',                '2026-04-19','cash',  'paid'),
  ('09181000006','confirmation_catechesis','2026-05-02','bank',  'paid'),
  ('09181000007','baptism',                '2026-05-11','gcash', 'paid'),
  ('09181000008','mass_intention',         '2026-05-14','maya',  'paid'),
  ('09181000011','funeral_mass',           '2026-05-25','cash',  'paid'),
  ('09181000012','wedding',                '2026-06-07','gcash', 'paid'),
  ('09181000013','house_blessing',         '2026-06-13','cash',  'paid'),
  ('09181000014','baptism',                '2026-06-21','maya',  'paid'),
  ('09181000015','pre_marriage',           '2026-06-27','gcash', 'paid'),
  ('09181000016','mass_intention',         '2026-06-28','cash',  'paid'),
  ('09181000017','business_blessing',      '2026-07-04','gcash', 'paid'),
  ('09181000018','baptism',                '2026-07-11','maya',  'paid'),
  ('09181000019','mass_intention',         '2026-07-14','cash',  'paid'),
  ('09181000020','funeral_mass',           '2026-07-18','cash',  'paid'),
  ('09181000001','house_blessing',         '2026-07-22','gcash', 'paid'),
  ('09181000002','car_blessing',           '2026-07-25','maya',  'paid'),
  ('09181000003','pre_baptismal',          '2026-07-26','cash',  'paid'),
  ('09181000004','wedding',                '2026-07-12','gcash', 'paid'),
  ('09181000005','baptism',                '2026-08-02','cash',  'paid'),
  ('09181000006','mass_intention',         '2026-08-06','maya',  'paid'),
  ('09181000007','confirmation_catechesis','2026-08-09','gcash', 'paid'),
  ('09181000009','house_blessing',         '2026-08-18','cash',  'paid'),
  ('09181000010','funeral_mass',           '2026-08-23','cash',  'paid'),
  ('09181000011','wedding',                '2026-08-30','gcash', 'paid'),
  -- Pending
  ('09181000012','baptism',                '2026-08-17','gcash', 'pending'),
  ('09181000013','baptism',                '2026-09-07','cash',  'pending'),
  ('09181000014','mass_intention',         '2026-09-09','maya',  'pending'),
  ('09181000015','house_blessing',         '2026-09-13','gcash', 'pending'),
  -- Failed (cancelled bookings)
  ('09181000004','pre_baptismal',          '2026-04-15','gcash', 'failed'),
  ('09181000010','car_blessing',           '2026-05-22','maya',  'failed')
) AS v(contact_number, booking_type, sched_date, method, status)
INNER JOIN parishioners p ON p.contact_number = v.contact_number
INNER JOIN bookings b
  ON b.parishioner_id = p.id
  AND b.booking_type = v.booking_type
  AND b.scheduled_date = v.sched_date::date
WHERE b.service_fee > 0
  AND NOT EXISTS (
    SELECT 1 FROM payments pm WHERE pm.booking_id = b.id
  );

-- ============================================================
-- STEP 7: CERTIFICATES (25 records)
-- certificate_number generated as 'CERT-YYYY-' + sequential per year
-- ============================================================
INSERT INTO certificates (
  parishioner_id, type, certificate_number, issued_date,
  purpose, status, notes, created_at, updated_at
)
SELECT
  p.id,
  v.cert_type,
  'CERT-' || EXTRACT(YEAR FROM v.issued_date::date)::text || '-D'
    || LPAD(ROW_NUMBER() OVER (ORDER BY v.issued_date::date)::text, 4, '0'),
  v.issued_date::date,
  v.purpose,
  v.status,
  '[DEMO] Demo certificate — safe to delete.',
  v.issued_date::timestamp,
  v.issued_date::timestamp
FROM (VALUES
  ('09181000001','baptism',        '2026-03-10','School enrollment requirement',  'released'),
  ('09181000002','baptism',        '2026-03-15','Confirmation requirement',        'released'),
  ('09181000003','membership',     '2026-04-05','Personal record keeping',         'issued'),
  ('09181000004','baptism',        '2026-04-12','Employment requirement',          'released'),
  ('09181000005','marriage',       '2026-04-20','Legal documentation',             'released'),
  ('09181000006','membership',     '2026-05-08','Parish membership proof',         'issued'),
  ('09181000007','first_communion','2026-05-15','Confirmation requirement',        'issued'),
  ('09181000008','baptism',        '2026-05-22','NBI clearance requirement',       'released'),
  ('09181000009','confirmation',   '2026-06-01','Marriage application',            'released'),
  ('09181000010','baptism',        '2026-06-10','Travel abroad / passport',        'issued'),
  ('09181000011','membership',     '2026-06-18','PhilHealth requirement',          'released'),
  ('09181000012','baptism',        '2026-07-04','University admission',            'released'),
  ('09181000013','first_communion','2026-07-11','Scholarship application',         'issued'),
  ('09181000014','baptism',        '2026-07-18','Barangay clearance support',      'released'),
  ('09181000015','no_impediment',  '2026-07-25','Marriage license requirement',    'issued'),
  ('09181000016','confirmation',   '2026-08-02','Government ID application',       'released'),
  ('09181000017','membership',     '2026-08-09','Insurance beneficiary',           'issued'),
  ('09181000018','baptism',        '2026-08-16','SSS requirement',                 'issued'),
  ('09181000019','marriage',       '2026-08-23','Legal documentation',             'draft'),
  ('09181000020','baptism',        '2026-09-01','Personal record keeping',         'draft'),
  ('09182000001','baptism',        '2026-09-05','School enrollment requirement',   'draft'),
  ('09181000001','confirmation',   '2026-09-07','Confirmation as godparent',       'draft'),
  ('09181000002','membership',     '2026-05-28','Parish ministry application',     'released'),
  ('09181000003','first_communion','2026-06-25','Catechism record',                'released'),
  ('09181000005','no_impediment',  '2026-04-28','Wedding preparation',             'released')
) AS v(contact_number, cert_type, issued_date, purpose, status)
INNER JOIN parishioners p ON p.contact_number = v.contact_number
WHERE NOT EXISTS (
  SELECT 1 FROM certificates c
  WHERE c.parishioner_id = p.id
    AND c.type = v.cert_type
    AND c.issued_date = v.issued_date::date
);

-- ============================================================
-- STEP 8: LEDGER ENTRIES (60 records)
-- ============================================================
INSERT INTO ledger_entries (type, category, description, amount, entry_date, reference_number, created_at, updated_at)
SELECT v.etype, v.category, v.description, v.amount::numeric, v.entry_date::date, v.ref, NOW(), NOW()
FROM (VALUES
  ('credit','Collection',     'Sunday Collection — Apr 5, 2026',        11200.00,'2026-04-05','COL-2604-05'),
  ('credit','Collection',     'Divine Mercy Collection — Apr 12',       15000.00,'2026-04-12','COL-2604-12'),
  ('credit','Collection',     'Sunday Collection — Apr 19, 2026',       12400.00,'2026-04-19','COL-2604-19'),
  ('credit','Collection',     'Sunday Collection — Apr 26, 2026',       11800.00,'2026-04-26','COL-2604-26'),
  ('credit','Baptism Fee',    'Group Baptism — Apr 14',                   3000.00,'2026-04-14','BAP-2604-14'),
  ('credit','Wedding Fee',    'Wedding — Bautista & Cruz',                8000.00,'2026-04-19','WED-2604-19'),
  ('credit','Mass Stipend',   'Weekday Stipends — Apr Week 1',            2100.00,'2026-04-08','MS-2604-01'),
  ('credit','Grant',          'Diocese Subsidy — Q2 2026',              25000.00,'2026-04-30','GRANT-2604-30'),
  ('debit', 'Utilities',      'Meralco Bill — April 2026',                7100.00,'2026-04-10','UTIL-2604-10'),
  ('debit', 'Salary',         'Parish Staff Honorarium — April 2026',   18500.00,'2026-04-30','SAL-2604-30'),
  ('debit', 'Maintenance',    'Flooring Repair — Sacristy',               6500.00,'2026-04-20','MAINT-2604-20'),
  ('credit','Collection',     'Sunday Collection — May 3, 2026',         12100.00,'2026-05-03','COL-2605-03'),
  ('credit','Collection',     'Sunday Collection — May 10, 2026',        11700.00,'2026-05-10','COL-2605-10'),
  ('credit','Collection',     'Sunday Collection — May 24 (Feast)',      21000.00,'2026-05-24','COL-2605-24'),
  ('credit','Wedding Fee',    'Wedding — Garcia & Santos',                 8000.00,'2026-05-16','WED-2605-16'),
  ('credit','Baptism Fee',    'Baptism — Villanueva, Sofia Rose',          1500.00,'2026-05-17','BAP-2605-17'),
  ('credit','Seminar Fee',    'Confirmation Catechesis — 20 confirmands',  4000.00,'2026-05-20','SEM-2605-20'),
  ('credit','Donation',       'Mothers Day Donation Drive',                5500.00,'2026-05-10','DON-2605-10'),
  ('debit', 'Utilities',      'Meralco Bill — May 2026',                  7800.00,'2026-05-10','UTIL-2605-10'),
  ('debit', 'Salary',         'Parish Staff Honorarium — May 2026',      18500.00,'2026-05-31','SAL-2605-31'),
  ('debit', 'Events',         'Parish Anniversary Celebration',           12500.00,'2026-05-22','EVT-2605-22'),
  ('debit', 'Charitable',     'School Supplies Distribution',              7500.00,'2026-05-28','CHAR-2605-28'),
  ('credit','Collection',     'Sunday Collection — Jun 7, 2026',          11900.00,'2026-06-07','COL-2606-07'),
  ('credit','Collection',     'Sunday Collection — Jun 14, 2026',         12300.00,'2026-06-14','COL-2606-14'),
  ('credit','Collection',     'Sunday Collection — Jun 21, 2026',         10800.00,'2026-06-21','COL-2606-21'),
  ('credit','Baptism Fee',    'Group Baptism — June 21 (3 children)',       4500.00,'2026-06-21','BAP-2606-21'),
  ('credit','Wedding Fee',    'Wedding — Reyes & Mendez',                   8000.00,'2026-06-07','WED-2606-07'),
  ('credit','Burial Fee',     'Funeral Mass — Dela Cruz, Nestor',           3500.00,'2026-06-30','BUR-2606-30'),
  ('debit', 'Utilities',      'Meralco Bill — June 2026',                   8100.00,'2026-06-10','UTIL-2606-10'),
  ('debit', 'Salary',         'Parish Staff Honorarium — June 2026',       18500.00,'2026-06-30','SAL-2606-30'),
  ('debit', 'Insurance',      'Parish Property Insurance — Annual',         24000.00,'2026-06-15','INS-2606-15'),
  ('credit','Collection',     'Sunday Collection — Jul 5, 2026',            12450.00,'2026-07-05','COL-2607-05'),
  ('credit','Collection',     'Sunday Collection — Jul 12, 2026',           11800.00,'2026-07-12','COL-2607-12'),
  ('credit','Collection',     'Sunday Collection — Jul 19, 2026',           13200.00,'2026-07-19','COL-2607-19'),
  ('credit','Baptism Fee',    'Baptism — Lim, Carlos Jr.',                   1500.00,'2026-07-11','BAP-2607-11'),
  ('credit','Wedding Fee',    'Wedding — Reyes & Cruz',                       8000.00,'2026-07-12','WED-2607-12'),
  ('credit','Burial Fee',     'Funeral Mass — Mendoza, Rodrigo',              3500.00,'2026-07-18','BUR-2607-18'),
  ('credit','Mass Stipend',   'Weekday Stipends — Jul Week 1',                2200.00,'2026-07-07','MS-2607-01'),
  ('credit','Seminar Fee',    'Pre-Baptismal Seminar — 12 attendees',         1800.00,'2026-07-10','SEM-2607-10'),
  ('debit', 'Utilities',      'Meralco Bill — July 2026',                     7400.00,'2026-07-10','UTIL-2607-10'),
  ('debit', 'Salary',         'Parish Staff Honorarium — July 2026',         18500.00,'2026-07-31','SAL-2607-31'),
  ('debit', 'Events',         'Medical Mission — Southville 5',               8500.00,'2026-07-21','EVT-2607-21'),
  ('credit','Collection',     'Sunday Collection — Aug 2, 2026',             12500.00,'2026-08-02','COL-2608-02'),
  ('credit','Collection',     'Sunday Collection — Aug 9, 2026',             11200.00,'2026-08-09','COL-2608-09'),
  ('credit','Collection',     'Sunday Collection — Aug 16, 2026',            13800.00,'2026-08-16','COL-2608-16'),
  ('credit','Collection',     'Sunday Collection — Aug 23, 2026',            12100.00,'2026-08-23','COL-2608-23'),
  ('credit','Baptism Fee',    'Group Baptism — Aug 17 (3 children)',           4500.00,'2026-08-17','BAP-2608-17'),
  ('credit','Wedding Fee',    'Wedding — Aquino & Torres',                     8000.00,'2026-08-30','WED-2608-30'),
  ('credit','Mass Stipend',   'Weekday Stipends — Aug Week 1',                 2100.00,'2026-08-06','MS-2608-01'),
  ('credit','Certificate Fee','Certificate Fees — Aug 2026',                   2600.00,'2026-08-21','CERT-2608-21'),
  ('credit','House Blessing', 'House Blessing — 4 households',                 4800.00,'2026-08-22','HB-2608-22'),
  ('credit','Donation',       'Anonymous Benefactor Donation',                  5000.00,'2026-08-05','DON-2608-05'),
  ('debit', 'Utilities',      'Meralco Bill — August 2026',                    7600.00,'2026-08-10','UTIL-2608-10'),
  ('debit', 'Salary',         'Parish Staff Honorarium — Aug 2026',           18500.00,'2026-08-31','SAL-2608-31'),
  ('debit', 'Sacramentals',   'Candles & Liturgical Items — Aug',               2800.00,'2026-08-08','SACR-2608-08'),
  ('credit','Collection',     'Sunday Collection — Sep 7, 2026',              13200.00,'2026-09-07','COL-2609-07'),
  ('credit','Baptism Fee',    'Baptism — Sep 7, 2026',                          1500.00,'2026-09-07','BAP-2609-07'),
  ('credit','Mass Stipend',   'Weekday Stipends — Sep Week 1',                  2100.00,'2026-09-05','MS-2609-01'),
  ('debit', 'Utilities',      'Meralco Bill — September 2026',                  7500.00,'2026-09-10','UTIL-2609-10'),
  ('debit', 'Salary',         'Parish Staff Honorarium — Sep 2026',            18500.00,'2026-09-30','SAL-2609-30')
) AS v(etype, category, description, amount, entry_date, ref)
WHERE NOT EXISTS (
  SELECT 1 FROM ledger_entries le WHERE le.reference_number = v.ref
);

-- ============================================================
-- STEP 9: EVENTS (8 records)
-- ============================================================
INSERT INTO events (title, description, location, event_start, event_end, category, status, is_featured, created_at, updated_at)
SELECT v.title, v.description, v.location,
       v.event_start::timestamp, v.event_end::timestamp,
       v.category, 'published', v.featured::boolean, NOW(), NOW()
FROM (VALUES
  ('Feast of Mary Help of Christians 2026',
   '<p>Join us for the <strong>Patronal Feast</strong> of our parish. Solemn Mass at 9:00 AM followed by parish gathering and cultural presentation.</p>',
   'Mary Help of Christians Parish Church',
   '2026-09-21 09:00','2026-09-21 17:00','sacrament','true'),
  ('Parish Youth Ministry General Assembly',
   '<p>All youth parishioners aged 13–35 are invited to our General Assembly. Topics: officer elections, activities calendar, community service projects.</p>',
   'Parish Hall, Mary Help of Christians Parish',
   '2026-09-14 14:00','2026-09-14 17:00','youth','false'),
  ('Medical & Dental Mission — Southville 5',
   '<p>FREE Medical and Dental Mission. Services: general consultation, dental extraction, blood pressure monitoring, free medicines distribution.</p>',
   'Southville 5 Covered Court, Brgy. Marinig',
   '2026-09-28 08:00','2026-09-28 16:00','outreach','true'),
  ('Couples for Christ Monthly Meeting',
   '<p>Regular CFC community meeting focusing on marriage enrichment and family prayer.</p>',
   'Parish Hall, Mary Help of Christians Parish',
   '2026-09-17 19:00','2026-09-17 21:30','community','false'),
  ('First Friday Mass & Eucharistic Adoration',
   '<p>Special votive Mass at 6:00 PM followed by Eucharistic Adoration 7:00–9:00 PM.</p>',
   'Mary Help of Christians Parish Church',
   '2026-09-05 18:00','2026-09-05 21:00','general','false'),
  ('Summer Youth Leadership Camp 2026',
   '<p>Three-day Summer Leadership Camp. Theme: "Lead with Faith, Serve with Love".</p>',
   'Camp Crame, Quezon City',
   '2026-04-10 07:00','2026-04-12 18:00','youth','false'),
  ('Lenten Reconciliation Service 2026',
   '<p>Special Penitential Service. Over 200 parishioners received the Sacrament of Reconciliation.</p>',
   'Mary Help of Christians Parish Church',
   '2026-03-27 19:00','2026-03-27 22:00','sacrament','false'),
  ('Simbang Gabi 2025 — Nine-Day Novena of Masses',
   '<p>Traditional Simbang Gabi, December 16–24, 2025 at 4:00 AM daily. Special Filipino breakfast served after each Mass.</p>',
   'Mary Help of Christians Parish Church',
   '2025-12-16 04:00','2025-12-24 06:00','sacrament','false')
) AS v(title, description, location, event_start, event_end, category, featured)
WHERE NOT EXISTS (SELECT 1 FROM events e WHERE e.title = v.title);

-- ============================================================
-- STEP 10: ANNOUNCEMENTS (8 records)
-- ============================================================
INSERT INTO announcements (title, content, category, is_published, published_at, expires_at, created_at, updated_at)
SELECT v.title, v.content, 'general', TRUE,
       v.pub_at::timestamp, v.exp_at::timestamp, NOW(), NOW()
FROM (VALUES
  ('Mass Schedule Updates — September 2026',
   '<p>Updated Mass schedule effective September 1, 2026:</p><ul><li><strong>Monday–Saturday:</strong> 6:00 AM</li><li><strong>Saturday (Anticipated):</strong> 6:00 PM</li><li><strong>Sunday:</strong> 6:00 AM, 8:00 AM, 10:00 AM, 6:00 PM</li></ul>',
   '2026-09-01','2026-11-01'),
  ('Pre-Baptismal Seminar — September 20, 2026',
   '<p><strong>Date:</strong> September 20, 2026 (Saturday), 2:00 PM – 5:00 PM at the Parish Hall. Bring: Birth Certificate, Marriage Certificate, Baptismal & Confirmation Certificates.</p>',
   '2026-09-01','2026-09-21'),
  ('Registration Open: Confirmation 2026–2027',
   '<p>Accepting applications for Confirmation Catechesis. Requirements: Baptismal Certificate, First Communion Certificate, 1×1 photo, ₱200 registration fee. Deadline: October 15, 2026.</p>',
   '2026-08-25','2026-10-15'),
  ('Cemetery Blessing & Memorial Mass — November 1',
   '<p>All Saints Day Mass at 8:00 AM. Cemetery Blessing: Southville Cemetery 10:00 AM, Niugan Cemetery 11:30 AM.</p>',
   '2026-09-01','2026-11-02'),
  ('Parish Office Closed — September 8',
   '<p>Parish Office closed on September 8, 2026 (Nativity of the BVM). Solemn Mass at 9:00 AM. Normal hours resume September 9.</p>',
   '2026-09-05','2026-09-09'),
  ('Marriage Banns — Torres & Mendez',
   '<p>First Reading of Banns: Juan Carlo Torres and Ana Maria Mendez. Solemnization: October 12, 2026, 10:00 AM at Mary Help of Christians Parish.</p>',
   '2026-08-25','2026-10-15'),
  ('Volunteers Needed — Outreach Ministry',
   '<p>Volunteers needed for Medical and Dental Mission on September 28, 2026 at Southville 5. Doctors, nurses, students, and logistics helpers welcome.</p>',
   '2026-09-01','2026-09-29'),
  ('Lenten Season Confession Schedule 2026',
   '<p>Confessions every Friday of Lent: 4:00–6:00 PM. Holy Saturday April 4: 8:00 AM–12:00 NN. Communal Penance Service: March 27 at 7:00 PM.</p>',
   '2026-02-15','2026-04-06')
) AS v(title, content, pub_at, exp_at)
WHERE NOT EXISTS (SELECT 1 FROM announcements a WHERE a.title = v.title);

-- ============================================================
-- STEP 11: LIVESTREAMS (5 records)
-- ============================================================
INSERT INTO livestreams (title, description, youtube_url, youtube_id, type, scheduled_at, is_active, is_featured, created_at, updated_at)
SELECT v.title, v.description, v.yt_url, v.yt_id, v.ltype,
       v.sched::timestamp, TRUE, v.featured::boolean, NOW(), NOW()
FROM (VALUES
  ('Sunday Mass — September 7, 2026',
   'Live Holy Eucharist — 23rd Sunday in Ordinary Time.',
   'https://www.youtube.com/watch?v=dQw4w9WgXcQ','dQw4w9WgXcQ',
   'recorded','2026-09-07 09:00','false'),
  ('Sunday Mass — August 31, 2026',
   'Live Holy Eucharist — 22nd Sunday in Ordinary Time.',
   'https://www.youtube.com/watch?v=jNQXAC9IVRw','jNQXAC9IVRw',
   'recorded','2026-08-31 09:00','false'),
  ('Parish Anniversary Solemn Mass 2026',
   'Solemn Mass for the Feast of Mary Help of Christians. May 24, 2026.',
   'https://www.youtube.com/watch?v=9bZkp7q19f0','9bZkp7q19f0',
   'recorded','2026-05-24 09:00','true'),
  ('Lenten Recollection 2026',
   'Special Lenten recollection and communal penance service.',
   'https://www.youtube.com/watch?v=kXYiU_JCYtU','kXYiU_JCYtU',
   'recorded','2026-03-27 19:00','false'),
  ('Sunday Mass — September 14, 2026 (UPCOMING)',
   'Join us live for the Holy Eucharist — 24th Sunday in Ordinary Time at 9:00 AM.',
   'https://www.youtube.com/watch?v=tPEE9ZwImy0','tPEE9ZwImy0',
   'upcoming','2026-09-14 09:00','true')
) AS v(title, description, yt_url, yt_id, ltype, sched, featured)
WHERE NOT EXISTS (SELECT 1 FROM livestreams ls WHERE ls.title = v.title);

-- ============================================================
-- VERIFY: Row counts
-- ============================================================
SELECT 'families (demo)'         AS "table", COUNT(*) AS total FROM families          WHERE notes   LIKE '[DEMO]%'
UNION ALL
SELECT 'parishioners (demo)',               COUNT(*)          FROM parishioners       WHERE notes   LIKE '[DEMO]%'
UNION ALL
SELECT 'users (demo)',                       COUNT(*)          FROM users              WHERE email   LIKE 'demo.parishioner%@example.com'
UNION ALL
SELECT 'sacramental_records (demo)',         COUNT(*)          FROM sacramental_records WHERE notes  LIKE '[DEMO]%'
UNION ALL
SELECT 'bookings (demo)',                    COUNT(*)          FROM bookings           WHERE notes   LIKE '[DEMO]%'
UNION ALL
SELECT 'payments (demo)',                    COUNT(*)          FROM payments           WHERE notes   LIKE '[DEMO]%'
UNION ALL
SELECT 'certificates (demo)',                COUNT(*)          FROM certificates       WHERE notes   LIKE '[DEMO]%'
UNION ALL
SELECT 'ledger_entries (demo)',              COUNT(*)          FROM ledger_entries     WHERE reference_number SIMILAR TO '%(2604|2605|2606|2607|2608|2609)%'
UNION ALL
SELECT 'events (total)',                     COUNT(*)          FROM events
UNION ALL
SELECT 'announcements (total)',              COUNT(*)          FROM announcements
UNION ALL
SELECT 'livestreams (total)',                COUNT(*)          FROM livestreams;

COMMIT;
