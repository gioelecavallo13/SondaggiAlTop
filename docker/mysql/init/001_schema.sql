-- Schema completo piattaforma sondaggi
-- Compatibile con MySQL 8+ / MariaDB 10.4+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `sondaggi_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `sondaggi_db`;

-- Pulizia in ordine dipendenze
DROP TABLE IF EXISTS `dettaglio_risposte`;
DROP TABLE IF EXISTS `survey_submit_attempts`;
DROP TABLE IF EXISTS `risposte`;
DROP TABLE IF EXISTS `opzioni`;
DROP TABLE IF EXISTS `domande`;
DROP TABLE IF EXISTS `sondaggi`;
DROP TABLE IF EXISTS `contatti`;
DROP TABLE IF EXISTS `utenti`;

CREATE TABLE `utenti` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `data_creazione` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_utenti_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sondaggi` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titolo` VARCHAR(255) NOT NULL,
  `descrizione` TEXT NULL,
  `autore_id` INT UNSIGNED NOT NULL,
  `is_pubblico` TINYINT(1) NOT NULL DEFAULT 1,
  `data_creazione` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sondaggi_autore_data` (`autore_id`, `data_creazione`),
  CONSTRAINT `fk_sondaggi_autore`
    FOREIGN KEY (`autore_id`) REFERENCES `utenti` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `domande` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sondaggio_id` INT UNSIGNED NOT NULL,
  `testo` VARCHAR(500) NOT NULL,
  `tipo` ENUM('singola', 'multipla') NOT NULL,
  `ordine` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_domande_sondaggio_ordine` (`sondaggio_id`, `ordine`),
  CONSTRAINT `fk_domande_sondaggio`
    FOREIGN KEY (`sondaggio_id`) REFERENCES `sondaggi` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `opzioni` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domanda_id` INT UNSIGNED NOT NULL,
  `testo` VARCHAR(255) NOT NULL,
  `ordine` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_opzioni_domanda_ordine` (`domanda_id`, `ordine`),
  CONSTRAINT `fk_opzioni_domanda`
    FOREIGN KEY (`domanda_id`) REFERENCES `domande` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `risposte` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `utente_id` INT UNSIGNED NULL,
  `sondaggio_id` INT UNSIGNED NOT NULL,
  `client_id` CHAR(36) NULL,
  `session_fingerprint` CHAR(64) NULL,
  `ip_hash` CHAR(64) NULL,
  `data_compilazione` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_risposte_sondaggio_utente` (`sondaggio_id`, `utente_id`),
  KEY `idx_risposte_sondaggio_data` (`sondaggio_id`, `data_compilazione`),
  KEY `idx_risposte_utente` (`utente_id`),
  KEY `idx_risposte_client` (`sondaggio_id`, `client_id`),
  KEY `idx_risposte_fingerprint` (`sondaggio_id`, `session_fingerprint`),
  CONSTRAINT `fk_risposte_utente`
    FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_risposte_sondaggio`
    FOREIGN KEY (`sondaggio_id`) REFERENCES `sondaggi` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `survey_submit_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sondaggio_id` INT UNSIGNED NOT NULL,
  `ip_hash` CHAR(64) NOT NULL,
  `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempts_sondaggio_ip_time` (`sondaggio_id`, `ip_hash`, `attempted_at`),
  CONSTRAINT `fk_survey_submit_attempts_sondaggio`
    FOREIGN KEY (`sondaggio_id`) REFERENCES `sondaggi` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `dettaglio_risposte` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `risposta_id` BIGINT UNSIGNED NOT NULL,
  `domanda_id` INT UNSIGNED NOT NULL,
  `opzione_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dettaglio_domanda_opzione` (`domanda_id`, `opzione_id`),
  UNIQUE KEY `uk_risposta_domanda_opzione` (`risposta_id`, `domanda_id`, `opzione_id`),
  CONSTRAINT `fk_dettaglio_risposta`
    FOREIGN KEY (`risposta_id`) REFERENCES `risposte` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_dettaglio_domanda`
    FOREIGN KEY (`domanda_id`) REFERENCES `domande` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_dettaglio_opzione`
    FOREIGN KEY (`opzione_id`) REFERENCES `opzioni` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contatti` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `messaggio` TEXT NOT NULL,
  `data_invio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contatti_data` (`data_invio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed iniziale (utente demo, password: password123)
INSERT INTO `utenti` (`nome`, `email`, `password_hash`) VALUES
('Admin Demo', 'admin@example.com', '$2y$10$6ZzQhUjM9Wn3qW9EcEwt7u5b7IfgI3M57xXWqv5l0L1jE3iQH4L6S');

COMMIT;
