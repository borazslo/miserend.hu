-- 2024
INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2024, '2024-09-01', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Első tanítási nap';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2024, '2024-10-27', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Őszi óraátállítás';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2024, '2024-03-31', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Tavaszi óraátállítás';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2024, '2024-06-30', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Utolsó tanítási nap';

-- 2025
INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2025, '2025-09-01', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Első tanítási nap';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2025, '2025-10-26', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Őszi óraátállítás';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2025, '2025-03-30', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Tavaszi óraátállítás';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2025, '2025-06-30', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Utolsó tanítási nap';

-- 2026
INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2026, '2026-08-31', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Első tanítási nap';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2026, '2026-10-25', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Őszi óraátállítás';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2026, '2026-03-29', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Tavaszi óraátállítás';

INSERT INTO cal_period_years (period_id, start_year, start_date, end_date, created_at, updated_at)
SELECT id, 2026, '2026-06-30', NULL, '2025-07-08', '2025-07-08' FROM cal_periods WHERE name = 'Utolsó tanítási nap';
