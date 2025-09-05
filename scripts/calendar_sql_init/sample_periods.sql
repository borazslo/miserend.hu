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