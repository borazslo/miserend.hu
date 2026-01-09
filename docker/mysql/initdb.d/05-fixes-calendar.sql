# TODO automate

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


INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable) VALUES ('Január 1', 3, null, null, null, 0, '2025-06-30', '2025-06-30', '01-01', '01-01', 1);
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable) VALUES ('Szilveszter', 3, null, null, null, 0, '2025-06-30', '2025-06-30', '12-31', '12-31', 1);
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable) VALUES ('Tavaszi óraátállítás', 1, null, null, null, 0, '2025-06-30', '2025-06-30', null, null, 0);
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable) VALUES ('Őszi óraátállítás', 1, null, null, null, 0, '2025-06-30', '2025-06-30', null, null, 0);
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable) VALUES ('Első tanítási nap', 3, null, null, null, 0, '2025-06-30', '2025-06-30', null, null, 1);
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable) VALUES ('Utolsó tanítási nap', 3, null, null, null, 0, '2025-06-30', '2025-06-30', null, null, 1);

INSERT INTO miserend.cal_periods (
    name, weight, start_period_id, end_period_id,
    all_inclusive, multi_day, created_at, updated_at,
    start_month_day, end_month_day, selectable
)
SELECT
    'Tél', 2,
    sp.id AS start_period_id,
    ep.id AS end_period_id,
    0, 1, '2025-06-30', '2025-06-30',
    NULL, NULL, 1
FROM
    miserend.cal_periods sp
JOIN
    miserend.cal_periods ep ON ep.name = 'Tavaszi óraátállítás'
WHERE
    sp.name = 'Őszi óraátállítás';

INSERT INTO miserend.cal_periods (
    name, weight, start_period_id, end_period_id,
    all_inclusive, multi_day, created_at, updated_at,
    start_month_day, end_month_day, selectable
)
SELECT
    'Nyár', 2,
    sp.id, ep.id,
    0, 1, '2025-06-30', '2025-06-30',
    NULL, NULL, 1
FROM
    miserend.cal_periods sp
JOIN
    miserend.cal_periods ep ON ep.name = 'Őszi óraátállítás'
WHERE
    sp.name = 'Tavaszi óraátállítás';

INSERT INTO miserend.cal_periods (
    name, weight, start_period_id, end_period_id,
    all_inclusive, multi_day, created_at, updated_at,
    start_month_day, end_month_day, selectable
)
SELECT
    'Iskolaidő', 3,
    sp.id, ep.id,
    1, 1, '2025-06-30', '2025-06-30',
    NULL, NULL, 1
FROM
    miserend.cal_periods sp
JOIN
    miserend.cal_periods ep ON ep.name = 'Utolsó tanítási nap'
WHERE
    sp.name = 'Első tanítási nap';

INSERT INTO miserend.cal_periods (
    name, weight, start_period_id, end_period_id,
    all_inclusive, multi_day, created_at, updated_at,
    start_month_day, end_month_day, selectable
)
SELECT
    'Egész évben', 0,
    sp.id, ep.id,
    1, 1, '2025-06-30', '2025-06-30',
    NULL, NULL,1
FROM
    miserend.cal_periods sp
JOIN
    miserend.cal_periods ep ON ep.name = 'Szilveszter'
WHERE
    sp.name = 'Január 1';

SET @i = 0;
UPDATE cal_periods
    JOIN (
        SELECT id,
               CASE (@i := @i + 1) % 10
                   WHEN 0 THEN '#1E88E5' -- kék
                   WHEN 1 THEN '#43A047' -- zöld
                   WHEN 2 THEN '#E53935' -- piros
                   WHEN 3 THEN '#8E24AA' -- lila
                   WHEN 4 THEN '#FDD835' -- sárga
                   WHEN 5 THEN '#FB8C00' -- narancs
                   WHEN 6 THEN '#00ACC1' -- cián
                   WHEN 7 THEN '#6D4C41' -- barna
                   WHEN 8 THEN '#3949AB' -- indigó
                   ELSE '#00897B'        -- teal
                   END AS new_color
        FROM cal_periods, (SELECT @i := -1) t
    ) colors ON colors.id = cal_periods.id
SET cal_periods.color = colors.new_color;


INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable, special_type)
VALUES ('Karácsony', 4, null, null, 1, 1, '2025-06-30', '2025-06-30', '12-24', '12-26', 1, 'CHRISTMAS');
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable, special_type)
VALUES ('Nagycsütörtök', 4, null, null, 0, 0, '2025-06-30', '2025-06-30', null, null, 0, null);
INSERT INTO miserend.cal_periods (name, weight, start_period_id, end_period_id, all_inclusive, multi_day, created_at, updated_at, start_month_day, end_month_day, selectable, special_type)
VALUES ('Húsvétvasárnap', 4, null, null, 0, 0, '2025-06-30', '2025-06-30', null, null, 0, null);


INSERT INTO miserend.cal_periods (
    name, weight, start_period_id, end_period_id,
    all_inclusive, multi_day, created_at, updated_at,
    start_month_day, end_month_day, selectable, special_type
)
SELECT
    'Szent Három nap', 2,
    sp.id AS start_period_id,
    ep.id AS end_period_id,
    0, 1, '2025-06-30', '2025-06-30',
    NULL, NULL, 1, 'EASTER'
FROM
    miserend.cal_periods sp
        JOIN
    miserend.cal_periods ep ON ep.name = 'Húsvétvasárnap'
WHERE
    sp.name = 'Nagycsütörtök';
