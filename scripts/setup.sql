CREATE DATABASE IF NOT EXISTS `ypf_energia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ypf_energia`;

CREATE TABLE IF NOT EXISTS `historial_consumo` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dispositivo_id` VARCHAR(50) NOT NULL DEFAULT 'EcoSmart_01',
  `voltaje` FLOAT NOT NULL,
  `corriente` FLOAT NOT NULL,
  `potencia_activa` FLOAT NOT NULL,
  `energia_total_kwh` FLOAT NOT NULL,
  `consumo_fantasma` TINYINT(1) DEFAULT 0,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla actualizada para 3 relés
CREATE TABLE IF NOT EXISTS `control_reles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dispositivo_id` VARCHAR(50) UNIQUE NOT NULL DEFAULT 'EcoSmart_01',
  `rele_1` TINYINT(1) DEFAULT 0,
  `rele_2` TINYINT(1) DEFAULT 0,
  `rele_3` TINYINT(1) DEFAULT 0,
  `ultima_modificacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Registro inicial de estado
INSERT INTO `control_reles` (`dispositivo_id`, `rele_1`, `rele_2`, `rele_3`) 
VALUES ('EcoSmart_01', 0, 0, 0)
ON DUPLICATE KEY UPDATE `dispositivo_id`=`dispositivo_id`;