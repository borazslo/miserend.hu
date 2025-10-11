CREATE DATABASE IF NOT EXISTS miserend CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
USE miserend;

CREATE TABLE miserend.cal_periods (
                                      id INT PRIMARY KEY AUTO_INCREMENT,
                                      name VARCHAR(255) NOT NULL,
                                      weight INT NOT NULL,
                                      start_month_day VARCHAR(5) NULL,
                                      end_month_day VARCHAR(5) NULL,
                                      start_period_id INT NULL,
                                      end_period_id INT NULL,
                                      all_inclusive BOOLEAN NULL,
                                      multi_day BOOLEAN NOT NULL,
                                      created_at DATE NOT NULL,
                                      updated_at DATE NOT NULL,
                                      selectable BOOLEAN DEFAULT TRUE,
                                      color VARCHAR(255),
                                      FOREIGN KEY (start_period_id) REFERENCES miserend.cal_periods(id),
                                      FOREIGN KEY (end_period_id) REFERENCES miserend.cal_periods(id)
) CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE miserend.cal_generated_periods (
                                                id INT PRIMARY KEY AUTO_INCREMENT,
                                                period_id INT NOT NULL,
                                                name VARCHAR(255) NOT NULL,
                                                weight INT NOT NULL,
                                                start_date DATE NOT NULL,
                                                end_date DATE NOT NULL,
                                                created_at DATE NOT NULL,
                                                updated_at DATE NOT NULL,
                                                color VARCHAR(255),
                                                FOREIGN KEY (period_id) REFERENCES miserend.cal_periods(id)
) CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE miserend.cal_period_years (
                                           id INT PRIMARY KEY AUTO_INCREMENT,
                                           period_id INT NOT NULL,
                                           start_year INT(4) NOT NULL,
                                           start_date DATE DEFAULT NULL,
                                           end_date DATE DEFAULT NULL,
                                           created_at DATE NOT NULL,
                                           updated_at DATE NOT NULL,
                                           FOREIGN KEY (period_id) REFERENCES miserend.cal_periods(id)
) CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE miserend.cal_masses (
                                     id INT PRIMARY KEY AUTO_INCREMENT,
                                     church_id INT NOT NULL,
                                     period_id INT,
                                     title VARCHAR(255) NOT NULL,
                                     types TEXT,
                                     rite VARCHAR(50) NOT NULL,
                                     start_date VARCHAR(50) NOT NULL,
                                     duration JSON,
                                     rrule JSON,
                                     experiod JSON,
                                     exdate JSON,
                                     lang VARCHAR(3) NOT NULL,
                                     comment TEXT,
                                     created_at DATE NOT NULL,
                                     updated_at DATE NOT NULL,
                                     FOREIGN KEY (period_id) REFERENCES miserend.cal_periods(id)
) CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE miserend.cal_suggestion_packages (
                                                  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                  church_id BIGINT UNSIGNED,
                                                  sender_name VARCHAR(255),
                                                  sender_email VARCHAR(255),
                                                  sender_user_id BIGINT UNSIGNED,
                                                  state ENUM('ACCEPTED', 'REJECTED', 'PENDING') DEFAULT 'PENDING',
                                                  created_at TIMESTAMP NULL DEFAULT NULL,
                                                  updated_at TIMESTAMP NULL DEFAULT NULL
) CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE miserend.cal_suggestions (
                                          id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                          package_id BIGINT UNSIGNED NOT NULL,
                                          period_id BIGINT UNSIGNED,
                                          mass_id BIGINT UNSIGNED,
                                          mass_state ENUM('NEW', 'DELETED', 'MODIFIED') NOT NULL,
                                          changes JSON,
                                          created_at TIMESTAMP NULL DEFAULT NULL,
                                          updated_at TIMESTAMP NULL DEFAULT NULL,
                                          FOREIGN KEY (package_id) REFERENCES miserend.cal_suggestion_packages(id) ON DELETE CASCADE
) CHARACTER SET utf8 COLLATE utf8_hungarian_ci;
