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
