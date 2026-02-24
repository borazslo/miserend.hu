
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET FOREIGN_KEY_CHECKS=0;

--
-- A tábla adatainak kiíratása `cal_periods`
--

INSERT INTO `cal_periods` (`id`, `name`, `weight`, `start_month_day`, `end_month_day`, `start_period_id`, `end_period_id`, `all_inclusive`, `multi_day`, `created_at`, `updated_at`, `selectable`, `color`, `special_type`) VALUES
(1, 'Szűz Mária, Isten anyja (Újév)', 15, '01-01', '01-01', NULL, NULL, NULL, 0, '2025-06-30', '2025-06-30', 1, '#1E88E5', NULL),
(2, 'Szilveszter', 15, '12-31', '12-31', NULL, NULL, NULL, 0, '2025-06-30', '2025-06-30', 1, '#43A047', NULL),
(3, 'Tavaszi óraátállítás', 11, NULL, NULL, NULL, NULL, NULL, 0, '2025-06-30', '2025-06-30', 0, '#E53935', NULL),
(4, 'Őszi óraátállítás', 11, NULL, NULL, NULL, NULL, NULL, 0, '2025-06-30', '2025-06-30', 0, '#8E24AA', NULL),
(5, 'Első tanítási nap', 11, NULL, NULL, NULL, NULL, NULL, 0, '2025-06-30', '2025-06-30', 0, '#FDD835', NULL),
(6, 'Utolsó tanítási nap', 11, NULL, NULL, NULL, NULL, NULL, 0, '2025-06-30', '2025-06-30', 0, '#FB8C00', NULL),
(7, 'Tél', 4, NULL, NULL, 4, 3, 0, 1, '2025-06-30', '2025-06-30', 1, '#6EC6FF', NULL),
(8, 'Nyár', 5, NULL, NULL, 3, 4, 0, 1, '2025-06-30', '2025-06-30', 1, '#FFD480', NULL),
(10, 'Egész évben', 1, NULL, NULL, 1, 2, 1, 1, '2025-06-30', '2025-06-30', 1, '#807e7e', NULL),
(13, 'Nyári szünet', 3, NULL, NULL, 6, 5, 0, 1, '2025-10-11', '2025-10-11', 1, '#FFCC66', NULL),
(14, 'Advent I. vasárnapja', 15, NULL, NULL, NULL, NULL, NULL, 0, '2025-11-15', '2025-11-15', 0, NULL, NULL),
(15, 'Advent', 12, NULL, NULL, 14, 16, NULL, 1, '2025-11-15', '2025-11-15', 1, '#6A0DAD', NULL),
(16, 'Szenteste', 15, '12-24', '12-24', NULL, NULL, 1, 0, '2025-11-15', '2025-11-15', 0, NULL, NULL),
(17, 'Ősz', 6, NULL, NULL, 5, 14, NULL, 1, '2025-11-15', '2025-11-15', 1, '#FFB266', NULL),
(18, 'Tavasz', 6, NULL, NULL, 3, 6, NULL, 1, '2025-11-15', '2025-10-11', 1, '#98E89F', NULL),
(19, 'Május', 10, '05-01', '05-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#B5FF85', NULL),
(20, 'Október', 10, '10-01', '10-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#FF944D', NULL),
(21, 'Hamvazószerda', 15, NULL, NULL, NULL, NULL, NULL, 0, '2025-11-07', '2025-11-21', 1, '#4B0082', NULL),
(22, 'Karácsony', 13, '12-24', '12-26', NULL, NULL, 1, 1, '2025-06-30', '2025-06-30', 1, '#FFE650', 'CHRISTMAS'),
(23, 'Nagycsütörtök', 15, NULL, NULL, NULL, NULL, 0, 0, '2025-06-30', '2025-06-30', 0, '#DAA520', NULL),
(24, 'Húsvéthétfő', 15, NULL, NULL, NULL, NULL, 0, 0, '2025-06-30', '2025-06-30', 0, '#FFD700', NULL),
(25, 'Szent Három nap', 13, NULL, NULL, 23, 24, 1, 1, '2025-06-30', '2025-06-30', 1, '#800020', 'EASTER'),
(26, 'Nagyböjt', 12, NULL, NULL, 21, 23, NULL, 1, '2025-11-15', '2025-11-15', 1, '#5B0A91', NULL),
(27, 'Tanítási idő', 2, NULL, NULL, 5, 6, NULL, 1, '2025-11-15', '2025-11-15', 1, '#6699FF', NULL),
(28, 'Augusztus', 10, '08-01', '08-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#FFB347', NULL),
(29, 'December', 10, '12-01', '12-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#A7C8FF', NULL),
(30, 'November', 10, '11-01', '11-30', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#C6A1FF', NULL),
(31, 'Szeptember', 10, '09-01', '09-30', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#FFB266', NULL),
(32, 'Április', 10, '04-01', '04-30', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#A8F5A8', NULL),
(33, 'Január', 10, '01-01', '01-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#6EC6FF', NULL),
(34, 'Február', 10, '02-01', '02-28', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#8CC6FF', NULL),
(35, 'Március', 10, '03-01', '03-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#98E89F', NULL),
(36, 'Június', 10, '06-01', '06-30', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#FFE680', NULL),
(37, 'Július', 10, '07-01', '07-31', NULL, NULL, 1, 1, '2025-11-15', '2025-11-15', 1, '#FFD480', NULL),
(38, 'Pünkösdhétfő', 15, NULL, NULL, NULL, NULL, NULL, 0, '2026-01-01', '2026-01-01', 1, NULL, NULL),
(39, 'Úrnapja', 15, NULL, NULL, NULL, NULL, NULL, 0, '2026-01-01', '2026-01-01', 1, NULL, NULL),
(40, 'Mindenszentek', 15, '11-01', '11-01', NULL, NULL, NULL, 0, '2026-01-01', '2026-01-01', 1, NULL, NULL),
(41, 'Nagyboldogasszony', 15, '08-15', '08-15', NULL, NULL, NULL, 0, '2026-01-01', '2026-01-01', 1, NULL, NULL),
(42, 'Vízkereszt', 15, '01-06', '01-06', NULL, NULL, NULL, 0, '2026-01-01', '2026-01-01', 1, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;

UNLOCK TABLES;
COMMIT;