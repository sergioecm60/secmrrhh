-- SECMRRHH - Script de Instalación Limpia
-- Versión: 2.2
-- Este script crea la base de datos, las tablas, carga los datos iniciales y define las relaciones.
-- Corregido para unificar el modelo de datos de 'areas' a una relación directa con 'empresas'.

-- ---
-- PASO 1: Creación de la Base de Datos y Usuario
-- (Ejecutar como usuario root o con privilegios suficientes)
-- ---

CREATE DATABASE IF NOT EXISTS `secmrrhh` CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;

USE `secmrrhh`;

-- ---
-- PASO 1.5: Eliminación de Tablas Existentes (para una reinstalación limpia)
-- ---

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `vacaciones_calculos`;
DROP TABLE IF EXISTS `vacaciones_balance`;
DROP TABLE IF EXISTS `ausencias`;
DROP TABLE IF EXISTS `novedades`;
DROP TABLE IF EXISTS `documentacion_empleado`;
DROP TABLE IF EXISTS `documento_tipos`;
DROP TABLE IF EXISTS `datos_confidenciales`;
DROP TABLE IF EXISTS `auditoria`;
DROP TABLE IF EXISTS `notificaciones`; 
DROP TABLE IF EXISTS `personal`;
DROP TABLE IF EXISTS `categorias_convenio`;
DROP TABLE IF EXISTS `convenios`;
DROP TABLE IF EXISTS `sindicatos`;
DROP TABLE IF EXISTS `obras_sociales`;
DROP TABLE IF EXISTS `art`;
DROP TABLE IF EXISTS `bancos`;
DROP TABLE IF EXISTS `modalidades_contrato`; 
DROP TABLE IF EXISTS `configuracion_salarial`;
DROP TABLE IF EXISTS `areas`;
DROP TABLE IF EXISTS `funciones`;
DROP TABLE IF EXISTS `sucursales`;
DROP TABLE IF EXISTS `empresas`;
DROP TABLE IF EXISTS `provincias`;
DROP TABLE IF EXISTS `paises`;
DROP TABLE IF EXISTS `conceptos_salariales`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS=1;

-- ---
-- PASO 2: Creación de la Estructura de Tablas
-- ---

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-03:00";

CREATE TABLE `empresas` (
  `id_emp` int NOT NULL AUTO_INCREMENT,
  `denominacion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `cuit` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `art_proveedor` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `art_vigencia` date DEFAULT NULL,
  `art_coeficiente` decimal(5,3) DEFAULT NULL COMMENT 'Coeficiente de ART como porcentaje. Ej: 1.400 para 1.40%',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_emp`), UNIQUE KEY `cuit` (`cuit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `sucursales` (
  `id_sucursal` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int DEFAULT NULL,
  `denominacion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `direccion` text COLLATE utf8mb4_spanish_ci,
  `localidad` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `cod_postal` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefonos` text COLLATE utf8mb4_spanish_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sucursal`), KEY `id_empresa` (`id_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `areas` (
  `id_area` int NOT NULL AUTO_INCREMENT,
  `denominacion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `funciones` (
  `id_funcion` int NOT NULL AUTO_INCREMENT,
  `denominacion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `codigo_afip_actividad` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `codigo_afip_puesto` varchar(10) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_funcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
 
CREATE TABLE `paises` (
    `id_pais` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `codigo_iso` CHAR(2) NOT NULL UNIQUE,
    `nombre_oficial` VARCHAR(150) NULL,
    `continente` VARCHAR(50) NULL,
    `activo` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_codigo_iso` (`codigo_iso`),
    INDEX `idx_nombre` (`nombre`),
    INDEX `idx_continente` (`continente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `provincias` (
  `id_provincia` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_pais` int DEFAULT NULL,
  PRIMARY KEY (`id_provincia`), KEY `id_pais` (`id_pais`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nombre_completo` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `rol` enum('admin','usuario') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'usuario',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `id_sucursal` int DEFAULT NULL,
  PRIMARY KEY (`id_usuario`), UNIQUE KEY `username` (`username`), KEY `fk_usuario_sucursal` (`id_sucursal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `personal` (
  `id_personal` int NOT NULL AUTO_INCREMENT,
  `legajo` int NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `sexo` enum('M','F') COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `documento` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `cuil` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `redes_sociales` text COLLATE utf8mb4_spanish_ci,
  `nacimiento` date DEFAULT NULL,
  `estado_civil` enum('soltero','casado','divorciado','viudo','conviviente') COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `edad` int DEFAULT NULL,
  `ingreso` date DEFAULT NULL,
  `antiguedad` int DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_spanish_ci,
  `localidad` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `domicilio_real` text COLLATE utf8mb4_spanish_ci,
  `id_pais` int DEFAULT NULL,
  `id_provincia` int DEFAULT NULL,
  `nacionalidad` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono_fijo` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono_celular` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `legajo_interno` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `cuit_empresa` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_sindicato` int DEFAULT NULL,
  `cbu_o_alias` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_banco` int DEFAULT NULL,
  `sueldo_basico` decimal(10,2) DEFAULT NULL,
  `forma_pago` enum('acreditacion','efectivo','cheque') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_sucursal` int DEFAULT NULL,
  `id_area` int DEFAULT NULL,
  `id_funcion` int DEFAULT NULL,
  `id_puesto` int DEFAULT NULL,
  `id_convenio` int DEFAULT NULL,
  `id_categoria_convenio` int DEFAULT NULL,
  `id_modalidad_contrato` int DEFAULT NULL,
  `jornada` enum('completa','parcial','reducida') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_obra_social` int DEFAULT NULL,
  `id_art` int DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido','licencia') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_personal`), UNIQUE KEY `legajo` (`legajo`), KEY `id_sucursal` (`id_sucursal`), KEY `id_area` (`id_area`), KEY `id_funcion` (`id_funcion`), KEY `fk_personal_pais` (`id_pais`), KEY `fk_personal_provincia` (`id_provincia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `documento_tipos` (
  `id_tipo_documento` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_documento`), UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `documentacion_empleado` (
  `id_documento` int NOT NULL AUTO_INCREMENT,
  `id_personal` int NOT NULL,
  `id_tipo_documento` int NOT NULL,
  `nombre_archivo_original` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ruta_archivo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_documento`), KEY `id_personal` (`id_personal`), KEY `fk_doc_tipo` (`id_tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `novedades` (
  `id_novedad` int NOT NULL AUTO_INCREMENT,
  `id_personal` int NOT NULL,
  `tipo` enum('Medico','Enfermedad','Maternidad','Vacaciones','Licencia especial','Otro') COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `fecha_solicitud` date NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `ruta_adjunto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre_adjunto_original` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada') COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `registrado_por` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_novedad`), KEY `fk_novedad_personal` (`id_personal`), KEY `fk_novedad_usuario` (`registrado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ausencias` (
  `id_ausencia` int NOT NULL AUTO_INCREMENT,
  `id_novedad` int NOT NULL,
  `id_personal` int NOT NULL,
  `fecha` date NOT NULL,
  `tipo_dia` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Ej: Reposo, Vacaciones, Licencia',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ausencia`), UNIQUE KEY `unique_ausencia_dia` (`id_personal`,`fecha`), KEY `fk_ausencia_novedad` (`id_novedad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `vacaciones_balance` (
  `id_balance` int(11) NOT NULL AUTO_INCREMENT,
  `id_personal` int(11) NOT NULL,
  `periodo_anio` year(4) NOT NULL,
  `dias_corresponden` int(11) NOT NULL DEFAULT 0,
  `dias_tomados` int(11) NOT NULL DEFAULT 0,
  `dias_pendientes` int(11) NOT NULL DEFAULT 0,
  `fecha_calculo` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_balance`), UNIQUE KEY `unique_personal_periodo` (`id_personal`,`periodo_anio`), KEY `idx_vacaciones_balance_personal` (`id_personal`), KEY `idx_vacaciones_balance_periodo` (`periodo_anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vacaciones_calculos` (
  `id_calculo` int(11) NOT NULL AUTO_INCREMENT,
  `id_personal` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_calculo` date NOT NULL,
  `antiguedad_anios` int(11) NOT NULL,
  `dias_vacaciones` int(11) NOT NULL,
  `periodo_anio` year(4) NOT NULL,
  `calculado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_calculo`), KEY `idx_vacaciones_calculos_personal` (`id_personal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `auditoria` (
  `id_auditoria` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tabla_afectada` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_registro` int DEFAULT NULL,
  `detalles` text COLLATE utf8mb4_spanish_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_spanish_ci,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`), KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `notificaciones` (
  `id_notificacion` int NOT NULL AUTO_INCREMENT,
  `id_usuario_destino` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_spanish_ci NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notificacion`), KEY `id_usuario_destino` (`id_usuario_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `art` (
  `id_art` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cuit` varchar(13) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nro_poliza` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `vigencia_desde` date DEFAULT NULL,
  `vigencia_hasta` date DEFAULT NULL,
  `responsable_contacto` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_art`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `bancos` (
  `id_banco` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cuit` varchar(13) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `codigo_sucursal` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `codigo_bcra` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `responsable_contacto` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `horarios_atencion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_banco`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `obras_sociales` (
  `id_obra_social` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `abreviatura` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cuit` varchar(13) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nro_inscripcion_sssalud` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `responsable_contacto` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_spanish_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_obra_social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `sindicatos` (
  `id_sindicato` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cuit` varchar(13) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nro_inscripcion_mtess` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_obra_social` int DEFAULT NULL,
  `responsable_contacto` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sindicato`), KEY `fk_sindicato_obrasocial` (`id_obra_social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `convenios` (
  `id_convenio` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `abreviatura` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `numero` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ambito` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_sindicato` int DEFAULT NULL,
  `fecha_vigencia` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_convenio`), KEY `fk_convenio_sindicato` (`id_sindicato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `categorias_convenio` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `id_convenio` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nivel` int DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `sueldo_basico` decimal(12,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_categoria`), KEY `id_convenio` (`id_convenio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `modalidades_contrato` (
  `id_modalidad` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `datos_confidenciales` (
  `id_confidencial` int NOT NULL AUTO_INCREMENT,
  `id_personal` int NOT NULL,
  `sueldo_z` decimal(12,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_confidencial`), UNIQUE KEY `id_personal_unique` (`id_personal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `conceptos_salariales` (
  `id_concepto` int NOT NULL AUTO_INCREMENT,
  `id_convenio` int DEFAULT NULL COMMENT 'FK a convenios. NULL para conceptos globales/generales',
  `descripcion` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` enum('remunerativo','no_remunerativo','aporte','contribucion') COLLATE utf8mb4_spanish_ci NOT NULL COMMENT 'Remunerativo/No Remunerativo=Haber, Aporte=Empleado, Contribucion=Empleador',
  `base_calculo` enum('remunerativo','no_remunerativo','fijo') COLLATE utf8mb4_spanish_ci NOT NULL,
  `valor_porcentual` decimal(5,2) DEFAULT NULL,
  `valor_fijo` decimal(10,2) DEFAULT NULL,
  `codigo_recibo` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_concepto`),
  KEY `fk_concepto_convenio` (`id_convenio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ---
-- PASO 2.5: Lógica de Negocio (Procedimientos y Triggers)
-- ---

DELIMITER $$

-- Procedimiento que calcula los días de vacaciones según la LCT y actualiza el balance.
DROP PROCEDURE IF EXISTS `CalcularVacacionesLCT`$$
CREATE PROCEDURE `CalcularVacacionesLCT`(
    IN p_id_personal INT,
    IN p_fecha_calculo DATE,
    IN p_calculado_por INT,
    OUT p_dias_corresponden INT
)
BEGIN
    DECLARE v_fecha_ingreso DATE;
    DECLARE v_antiguedad_anios INT;
    DECLARE v_periodo_anio YEAR;
    DECLARE v_dias_trabajados INT;
    
    SELECT `ingreso` INTO v_fecha_ingreso FROM `personal` WHERE `id_personal` = p_id_personal;
    
    SET v_antiguedad_anios = TIMESTAMPDIFF(YEAR, v_fecha_ingreso, p_fecha_calculo);
    SET v_dias_trabajados = TIMESTAMPDIFF(DAY, v_fecha_ingreso, p_fecha_calculo);
    SET v_periodo_anio = YEAR(p_fecha_calculo);
    
    -- Aplicar escala de LCT Art. 150
    IF v_antiguedad_anios < 1 THEN
        -- LCT: 1 día de vacaciones por cada 20 días de trabajo efectivo.
        SET p_dias_corresponden = FLOOR(v_dias_trabajados / 20);
    ELSEIF v_antiguedad_anios >= 1 AND v_antiguedad_anios < 5 THEN
        SET p_dias_corresponden = 14;
    ELSEIF v_antiguedad_anios >= 5 AND v_antiguedad_anios < 10 THEN
        SET p_dias_corresponden = 21;
    ELSEIF v_antiguedad_anios >= 10 AND v_antiguedad_anios < 20 THEN
        SET p_dias_corresponden = 28;
    ELSE
        SET p_dias_corresponden = 35;
    END IF;
    
    -- Registrar el cálculo para auditoría
    INSERT INTO `vacaciones_calculos` 
    (`id_personal`, `fecha_ingreso`, `fecha_calculo`, `antiguedad_anios`, `dias_vacaciones`, `periodo_anio`, `calculado_por`)
    VALUES 
    (p_id_personal, v_fecha_ingreso, p_fecha_calculo, v_antiguedad_anios, p_dias_corresponden, v_periodo_anio, p_calculado_por);
    
    -- Actualizar o insertar el balance del empleado para el período
    INSERT INTO `vacaciones_balance` (`id_personal`, `periodo_anio`, `dias_corresponden`, `dias_pendientes`, `fecha_calculo`)
    VALUES (p_id_personal, v_periodo_anio, p_dias_corresponden, p_dias_corresponden, p_fecha_calculo)
    ON DUPLICATE KEY UPDATE 
        `dias_corresponden` = VALUES(`dias_corresponden`),
        `fecha_calculo` = VALUES(`fecha_calculo`),
        `dias_pendientes` = VALUES(`dias_corresponden`) - `dias_tomados`;
END$$

-- Trigger que se dispara DESPUÉS de INSERTAR una novedad.
DROP TRIGGER IF EXISTS `after_novedad_vacaciones_insert`$$
CREATE TRIGGER `after_novedad_vacaciones_insert` AFTER INSERT ON `novedades` FOR EACH ROW BEGIN
    DECLARE v_dias_vacaciones INT;
    DECLARE v_periodo YEAR;
    IF NEW.tipo = 'Vacaciones' AND NEW.estado = 'Aprobada' THEN
        SET v_periodo = YEAR(NEW.fecha_desde);
        SET v_dias_vacaciones = DATEDIFF(NEW.fecha_hasta, NEW.fecha_desde) + 1;
        UPDATE `vacaciones_balance` SET `dias_tomados` = `dias_tomados` + v_dias_vacaciones, `dias_pendientes` = `dias_corresponden` - `dias_tomados` WHERE `id_personal` = NEW.id_personal AND `periodo_anio` = v_periodo;
    END IF;
END$$

DELIMITER ;

-- ---
-- PASO 3: Carga de Datos Iniciales (Catálogos)
-- ---

INSERT INTO `paises` (nombre, codigo_iso, nombre_oficial, continente) VALUES
('Argentina', 'AR', 'República Argentina', 'América'),
('Afganistán', 'AF', 'República Islámica de Afganistán', 'Asia'),
('Albania', 'AL', 'República de Albania', 'Europa'),
('Alemania', 'DE', 'República Federal de Alemania', 'Europa'),
('Andorra', 'AD', 'Principado de Andorra', 'Europa'),
('Angola', 'AO', 'República de Angola', 'África'),
('Antigua y Barbuda', 'AG', 'Antigua y Barbuda', 'América'),
('Arabia Saudí', 'SA', 'Reino de Arabia Saudí', 'Asia'),
('Argelia', 'DZ', 'República Argelina Democrática y Popular', 'África'),
('Armenia', 'AM', 'República de Armenia', 'Asia'),
('Australia', 'AU', 'Mancomunidad de Australia', 'Oceanía'),
('Austria', 'AT', 'República de Austria', 'Europa'),
('Azerbaiyán', 'AZ', 'República de Azerbaiyán', 'Asia'),
('Bahamas', 'BS', 'Mancomunidad de las Bahamas', 'América'),
('Bangladés', 'BD', 'República Popular de Bangladés', 'Asia'),
('Barbados', 'BB', 'Barbados', 'América'),
('Baréin', 'BH', 'Reino de Baréin', 'Asia'),
('Bélgica', 'BE', 'Reino de Bélgica', 'Europa'),
('Belice', 'BZ', 'Belice', 'América'),
('Benín', 'BJ', 'República de Benín', 'África'),
('Bielorrusia', 'BY', 'República de Bielorrusia', 'Europa'),
('Birmania', 'MM', 'República de la Unión de Myanmar', 'Asia'),
('Bolivia', 'BO', 'Estado Plurinacional de Bolivia', 'América'),
('Bosnia y Herzegovina', 'BA', 'Bosnia y Herzegovina', 'Europa'),
('Botsuana', 'BW', 'República de Botsuana', 'África'),
('Brasil', 'BR', 'República Federativa del Brasil', 'América'),
('Brunéi', 'BN', 'Estado de Brunéi Darussalam', 'Asia'),
('Bulgaria', 'BG', 'República de Bulgaria', 'Europa'),
('Burkina Faso', 'BF', 'Burkina Faso', 'África'),
('Burundi', 'BI', 'República de Burundi', 'África'),
('Bután', 'BT', 'Reino de Bután', 'Asia'),
('Cabo Verde', 'CV', 'República de Cabo Verde', 'África'),
('Camboya', 'KH', 'Reino de Camboya', 'Asia'),
('Camerún', 'CM', 'República de Camerún', 'África'),
('Canadá', 'CA', 'Canadá', 'América'),
('Catar', 'QA', 'Estado de Catar', 'Asia'),
('Chad', 'TD', 'República del Chad', 'África'),
('Chile', 'CL', 'República de Chile', 'América'),
('China', 'CN', 'República Popular China', 'Asia'),
('Chipre', 'CY', 'República de Chipre', 'Asia'),
('Ciudad del Vaticano', 'VA', 'Estado de la Ciudad del Vaticano', 'Europa'),
('Colombia', 'CO', 'República de Colombia', 'América'),
('Comoras', 'KM', 'Unión de las Comoras', 'África'),
('Congo', 'CG', 'República del Congo', 'África'),
('Corea del Norte', 'KP', 'República Popular Democrática de Corea', 'Asia'),
('Corea del Sur', 'KR', 'República de Corea', 'Asia'),
('Costa de Marfil', 'CI', 'República de Costa de Marfil', 'África'),
('Costa Rica', 'CR', 'República de Costa Rica', 'América'),
('Croacia', 'HR', 'República de Croacia', 'Europa'),
('Cuba', 'CU', 'República de Cuba', 'América'),
('Dinamarca', 'DK', 'Reino de Dinamarca', 'Europa'),
('Dominica', 'DM', 'Mancomunidad de Dominica', 'América'),
('Ecuador', 'EC', 'República del Ecuador', 'América'),
('Egipto', 'EG', 'República Árabe de Egipto', 'África'),
('El Salvador', 'SV', 'República de El Salvador', 'América'),
('Emiratos Árabes Unidos', 'AE', 'Emiratos Árabes Unidos', 'Asia'),
('Eritrea', 'ER', 'Estado de Eritrea', 'África'),
('Eslovaquia', 'SK', 'República Eslovaca', 'Europa'),
('Eslovenia', 'SI', 'República de Eslovenia', 'Europa'),
('España', 'ES', 'Reino de España', 'Europa'),
('Estados Unidos', 'US', 'Estados Unidos de América', 'América'),
('Estonia', 'EE', 'República de Estonia', 'Europa'),
('Eswatini', 'SZ', 'Reino de Eswatini', 'África'),
('Etiopía', 'ET', 'República Democrática Federal de Etiopía', 'África'),
('Filipinas', 'PH', 'República de Filipinas', 'Asia'),
('Finlandia', 'FI', 'República de Finlandia', 'Europa'),
('Fiyi', 'FJ', 'República de Fiyi', 'Oceanía'),
('Francia', 'FR', 'República Francesa', 'Europa'),
('Gabón', 'GA', 'República Gabonesa', 'África'),
('Gambia', 'GM', 'República de Gambia', 'África'),
('Georgia', 'GE', 'Georgia', 'Asia'),
('Ghana', 'GH', 'República de Ghana', 'África'),
('Granada', 'GD', 'Granada', 'América'),
('Grecia', 'GR', 'República Helénica', 'Europa'),
('Guatemala', 'GT', 'República de Guatemala', 'América'),
('Guinea', 'GN', 'República de Guinea', 'África'),
('Guinea Ecuatorial', 'GQ', 'República de Guinea Ecuatorial', 'África'),
('Guinea-Bisáu', 'GW', 'República de Guinea-Bisáu', 'África'),
('Guyana', 'GY', 'República Cooperativa de Guyana', 'América'),
('Haití', 'HT', 'República de Haití', 'América'),
('Honduras', 'HN', 'República de Honduras', 'América'),
('Hungría', 'HU', 'Hungría', 'Europa'),
('India', 'IN', 'República de la India', 'Asia'),
('Indonesia', 'ID', 'República de Indonesia', 'Asia'),
('Irak', 'IQ', 'República de Irak', 'Asia'),
('Irán', 'IR', 'República Islámica de Irán', 'Asia'),
('Irlanda', 'IE', 'República de Irlanda', 'Europa'),
('Islandia', 'IS', 'República de Islandia', 'Europa'),
('Islas Cook', 'CK', 'Islas Cook', 'Oceanía'),
('Islas Marshall', 'MH', 'República de las Islas Marshall', 'Oceanía'),
('Islas Salomón', 'SB', 'Islas Salomón', 'Oceanía'),
('Israel', 'IL', 'Estado de Israel', 'Asia'),
('Italia', 'IT', 'República Italiana', 'Europa'),
('Jamaica', 'JM', 'Jamaica', 'América'),
('Japón', 'JP', 'Japón', 'Asia'),
('Jordania', 'JO', 'Reino Hachemita de Jordania', 'Asia'),
('Kazajistán', 'KZ', 'República de Kazajistán', 'Asia'),
('Kenia', 'KE', 'República de Kenia', 'África'),
('Kirguistán', 'KG', 'República Kirguisa', 'Asia'),
('Kiribati', 'KI', 'República de Kiribati', 'Oceanía'),
('Kuwait', 'KW', 'Estado de Kuwait', 'Asia'),
('Laos', 'LA', 'República Democrática Popular Lao', 'Asia'),
('Lesoto', 'LS', 'Reino de Lesoto', 'África'),
('Letonia', 'LV', 'República de Letonia', 'Europa'),
('Líbano', 'LB', 'República Libanesa', 'Asia'),
('Liberia', 'LR', 'República de Liberia', 'África'),
('Libia', 'LY', 'Estado de Libia', 'África'),
('Liechtenstein', 'LI', 'Principado de Liechtenstein', 'Europa'),
('Lituania', 'LT', 'República de Lituania', 'Europa'),
('Luxemburgo', 'LU', 'Gran Ducado de Luxemburgo', 'Europa'),
('Macedonia del Norte', 'MK', 'República de Macedonia del Norte', 'Europa'),
('Madagascar', 'MG', 'República de Madagascar', 'África'),
('Malasia', 'MY', 'Malasia', 'Asia'),
('Malaui', 'MW', 'República de Malaui', 'África'),
('Maldivas', 'MV', 'República de Maldivas', 'Asia'),
('Mali', 'ML', 'República de Mali', 'África'),
('Malta', 'MT', 'República de Malta', 'Europa'),
('Marruecos', 'MA', 'Reino de Marruecos', 'África'),
('Mauricio', 'MU', 'República de Mauricio', 'África'),
('Mauritania', 'MR', 'República Islámica de Mauritania', 'África'),
('México', 'MX', 'Estados Unidos Mexicanos', 'América'),
('Micronesia', 'FM', 'Estados Federados de Micronesia', 'Oceanía'),
('Moldavia', 'MD', 'República de Moldavia', 'Europa'),
('Mónaco', 'MC', 'Principado de Mónaco', 'Europa'),
('Mongolia', 'MN', 'Mongolia', 'Asia'),
('Montenegro', 'ME', 'Montenegro', 'Europa'),
('Mozambique', 'MZ', 'República de Mozambique', 'África'),
('Namibia', 'NA', 'República de Namibia', 'África'),
('Nauru', 'NR', 'República de Nauru', 'Oceanía'),
('Nepal', 'NP', 'República Federal Democrática de Nepal', 'Asia'),
('Nicaragua', 'NI', 'República de Nicaragua', 'América'),
('Níger', 'NE', 'República de Níger', 'África'),
('Nigeria', 'NG', 'República Federal de Nigeria', 'África'),
('Noruega', 'NO', 'Reino de Noruega', 'Europa'),
('Nueva Zelanda', 'NZ', 'Nueva Zelanda', 'Oceanía'),
('Omán', 'OM', 'Sultanato de Omán', 'Asia'),
('Países Bajos', 'NL', 'Reino de los Países Bajos', 'Europa'),
('Pakistán', 'PK', 'República Islámica de Pakistán', 'Asia'),
('Palaos', 'PW', 'República de Palaos', 'Oceanía'),
('Palestina', 'PS', 'Estado de Palestina', 'Asia'),
('Panamá', 'PA', 'República de Panamá', 'América'),
('Papúa Nueva Guinea', 'PG', 'Estado Independiente de Papúa Nueva Guinea', 'Oceanía'),
('Paraguay', 'PY', 'República del Paraguay', 'América'),
('Perú', 'PE', 'República del Perú', 'América'),
('Polonia', 'PL', 'República de Polonia', 'Europa'),
('Portugal', 'PT', 'República Portuguesa', 'Europa'),
('Reino Unido', 'GB', 'Reino Unido de Gran Bretaña e Irlanda del Norte', 'Europa'),
('República Centroafricana', 'CF', 'República Centroafricana', 'África'),
('República Checa', 'CZ', 'República Checa', 'Europa'),
('República Democrática del Congo', 'CD', 'República Democrática del Congo', 'África'),
('República Dominicana', 'DO', 'República Dominicana', 'América'),
('Ruanda', 'RW', 'República de Ruanda', 'África'),
('Rumania', 'RO', 'Rumania', 'Europa'),
('Rusia', 'RU', 'Federación de Rusia', 'Europa'),
('Samoa', 'WS', 'Estado Independiente de Samoa', 'Oceanía'),
('San Cristóbal y Nieves', 'KN', 'Federación de San Cristóbal y Nieves', 'América'),
('San Marino', 'SM', 'República de San Marino', 'Europa'),
('San Vicente y las Granadinas', 'VC', 'San Vicente y las Granadinas', 'América'),
('Santa Lucía', 'LC', 'Santa Lucía', 'América'),
('Santo Tomé y Príncipe', 'ST', 'República Democrática de Santo Tomé y Príncipe', 'África'),
('Senegal', 'SN', 'República de Senegal', 'África'),
('Serbia', 'RS', 'República de Serbia', 'Europa'),
('Seychelles', 'SC', 'República de Seychelles', 'África'),
('Sierra Leona', 'SL', 'República de Sierra Leona', 'África'),
('Singapur', 'SG', 'República de Singapur', 'Asia'),
('Siria', 'SY', 'República Árabe Siria', 'Asia'),
('Somalia', 'SO', 'República Federal de Somalia', 'África'),
('Sri Lanka', 'LK', 'República Socialista Democrática de Sri Lanka', 'Asia'),
('Sudáfrica', 'ZA', 'República de Sudáfrica', 'África'),
('Sudán', 'SD', 'República de Sudán', 'África'),
('Sudán del Sur', 'SS', 'República de Sudán del Sur', 'África'),
('Suecia', 'SE', 'Reino de Suecia', 'Europa'),
('Suiza', 'CH', 'Confederación Suiza', 'Europa'),
('Surinam', 'SR', 'República de Surinam', 'América'),
('Tailandia', 'TH', 'Reino de Tailandia', 'Asia'),
('Taiwán', 'TW', 'República de China', 'Asia'),
('Tanzania', 'TZ', 'República Unida de Tanzania', 'África'),
('Tayikistán', 'TJ', 'República de Tayikistán', 'Asia'),
('Timor Oriental', 'TL', 'República Democrática de Timor Oriental', 'Asia'),
('Togo', 'TG', 'República Togolesa', 'África'),
('Tonga', 'TO', 'Reino de Tonga', 'Oceanía'),
('Trinidad y Tobago', 'TT', 'República de Trinidad y Tobago', 'América'),
('Túnez', 'TN', 'República Tunecina', 'África'),
('Turkmenistán', 'TM', 'Turkmenistán', 'Asia'),
('Turquía', 'TR', 'República de Turquía', 'Asia'),
('Tuvalu', 'TV', 'Tuvalu', 'Oceanía'),
('Ucrania', 'UA', 'Ucrania', 'Europa'),
('Uganda', 'UG', 'República de Uganda', 'África'),
('Uruguay', 'UY', 'República Oriental del Uruguay', 'América'),
('Uzbekistán', 'UZ', 'República de Uzbekistán', 'Asia'),
('Vanuatu', 'VU', 'República de Vanuatu', 'Oceanía'),
('Venezuela', 'VE', 'República Bolivariana de Venezuela', 'América'),
('Vietnam', 'VN', 'República Socialista de Vietnam', 'Asia'),
('Yemen', 'YE', 'República del Yemen', 'Asia'),
('Yibuti', 'DJ', 'República de Yibuti', 'África'),
('Zambia', 'ZM', 'República de Zambia', 'África'),
('Zimbabue', 'ZW', 'República de Zimbabue', 'África');

INSERT INTO `empresas` (`denominacion`, `cuit`, `art_proveedor`, `art_vigencia`, `art_coeficiente`) VALUES
('PEDRAZA VIAJES Y TURISMO S.A.', '30-69687727-7', 'ASOCIART', '2019-03-01', 1.400), 
('HOTEL VILLA MERLO S.A.', '30-70817304-1', 'ASOCIART', '2019-05-01', 3.000),
('PROEMTUR S.A.', '30-68537245-9', 'ASOCIART', '2019-05-01', 4.400), 
('CONAL S A C I F I I', '30-61713990-8', 'ASOCIART', '2019-03-01', 2.320),
('CUNA DE LA BANDERA S.R.L.', '30-70783126-6', NULL, NULL, NULL),
('PUNTA SABIONI S.R.L.', '30-71007437-9', 'ASOCIART', '2019-08-01', 3.000),
('TRAVEL BUS S.R.L.', '30-66847447-7', NULL, NULL, NULL),
('TURISMO INTERLAGOS S.A.', '30-51290083-2', 'ASOCIART', '2019-02-01', 3.000),
('TRIPLE L SA', '30-71501592-3', 'ASOCIART', '2015-10-01', 3.080),
('PEDRAZA ROSARIO S.R.L.', '30-71105404-5', 'ASOCIART', '2019-03-01', 2.160),
('BGNP S.R.L.', '30-71430832-3', 'ASOCIART', '2015-01-01', 5.000),
('HOTEL LYON SAC', '30-54183661-2', 'ASOCIART', '2019-07-01', 2.100);

INSERT INTO `sucursales` (`id_empresa`, `denominacion`, `direccion`, `localidad`, `cod_postal`, `telefonos`) VALUES
(1, 'LA MARGARITA', 'OLAVARRIA S/N (Y MAR DEL PLATA)', 'GRAL RODRIGUEZ', '1968', '50325118'),
(1, 'CENTRAL', 'FLORIDA 537 PISO 23 - GALERIA JARDIN', 'CABA', '1005', '43221868 / 4105-2100'),
(1, 'CHACABUCO', 'SAAVEDRA 58', 'CHACABUCO', '7223', '02351-431007'),
(1, 'CORDOBA', '', 'CAPITAL', '5000', '76547'),
(1, 'ROSARIO', 'CORDOBA 1015 PISO 1 OF 9 - GALERIA VICTOR', 'ROSARIO', '2000', '0341-4115030'),
(1, 'HOSTERIA KALKEN', 'TTE VALENTIN FEILBERG 119', 'EL CALAFATE - SANTA CRUZ', '9405', '02902-491073/687'),
(1, 'PUNTA SABIONI', 'AV. HIPOLITO IRIGOYEN 250', 'SANTIAGO DEL ESTERO - TERMAS DE', '4220', '03858-421185'),
(1, 'CUNA DE LA BANDERA SRL', 'CORDOBA 1015 PISO 1 OF 9 - GALERIA VICTOR', 'ROSARIO', '2000', '0341-4115030'),
(1, 'SUCURSAL CONAL SAICF', 'FLORIDA 537', 'CABA', '1005', '41052100'),
(2, 'VILLA DE MERLO', 'TTE VALENTIN FEILBERG 119', 'EL CALAFATE - SANTA CRUZ', '9405', '02902-491073/687');

INSERT INTO `areas` (`denominacion`) VALUES
('Recepción / Atención al Cliente'),
('Alimentos y Bebidas'),
('Housekeeping / Mantenimiento'),
('Administración'),
('Comercial'),
('Recursos Humanos'),
('Servicios Complementarios'),
('Sistemas / Tecnología'),
('Spa y Bienestar');

INSERT INTO `funciones` (`denominacion`, `codigo_afip_actividad`, `codigo_afip_puesto`) VALUES
('Administración', '034178', '4190'),
('Ayudante de Cocina', '4659', '5122'),
('Bachero/a', '4659', '5122'),
('Bagajista', '005713', '4222'),
('Chofer', '32791', '8322'),
('Chofer (Alternativo)', '00810', '8323'),
('Cocinero/a', '4659', '5122'),
('Conserje', '90541', '9141'),
('Coordinador/a Comercial', '8816', '5113'),
('Lavandero/a', '34169', '9133'),
('Mozo/a', '19082', '5123'),
('Mucama/o', '19169', '9132'),
('Niñera / Kinder', '19169', '5131'),
('Operaciones', '003727', '4221'),
('Peón General', '26239', '9132'),
('Promoción', '028124', '4221'),
('Recepcionista', '28392', '4222'),
('Recepcionista (Pedraza)', '028398', '4221'),
('Salvavidas / Bañero/a', '14001', '5169'),
('Sistemas', '1006', '4221'),
('Personal de Spa', '17439', '3229'),
('Vendedor/a', '29586', '4221'),
('Telefonista / Operador de Centralita', NULL, NULL),
('Guest Relations', NULL, NULL),
('Jefe de Recepción', NULL, NULL),
('Auditor Nocturno', NULL, NULL),
('Bartender', NULL, NULL),
('Sommelier', NULL, NULL),
('Chef Ejecutivo', NULL, NULL),
('Sous Chef', NULL, NULL),
('Jefe de Partida', NULL, NULL),
('Pastelero / Panadero', NULL, NULL),
('Jefe de Banquetes / Eventos', NULL, NULL),
('Encargado de Pisos', NULL, NULL),
('Técnico de Mantenimiento', NULL, NULL),
('Jefe de Mantenimiento', NULL, NULL),
('Personal de Seguridad', NULL, NULL),
('Cajero / Tesorería', NULL, NULL),
('Analista Contable', NULL, NULL),
('Jefe de Contabilidad', NULL, NULL),
('Comprador', NULL, NULL),
('Auditor Interno', NULL, NULL),
('Ejecutivo de Cuentas', NULL, NULL),
('Revenue Manager', NULL, NULL),
('Responsable de Marketing Digital', NULL, NULL),
('Gerente de Ventas', NULL, NULL),
('Asistente de RRHH', NULL, NULL),
('Analista de RRHH', NULL, NULL),
('Responsable de Capacitación', NULL, NULL),
('Jefe de Personal', NULL, NULL),
('Gerente de RRHH', NULL, NULL),
('Guía de Turismo', NULL, NULL),
('Animador Turístico', NULL, NULL),
('Instructor Deportivo', NULL, NULL),
('Desarrollador de Software', NULL, NULL),
('Encargado de Telecomunicaciones', NULL, NULL),
('Jefe de IT', NULL, NULL),
('SUPERVISORA', NULL, NULL),
('2DO VEND', NULL, NULL),
('GERENTE', NULL, NULL),
('CONTROLLER', NULL, NULL),
('RESPONSABLE', NULL, NULL),
('ADM 1', NULL, NULL),
('JEFE COMERCIAL', NULL, NULL),
('PRESIDENTE', NULL, NULL),
('DIRECTOR', NULL, NULL),
('AUXILIAR 2º', NULL, NULL),
('MASTRANZA', NULL, NULL),
('ADM 2', NULL, NULL),
('AUXILIAR 1º', NULL, NULL),
('ABOGADA JR', NULL, NULL),
('GERENTE COMERCIAL', NULL, NULL),
('ATENCION AL CLIENTE', NULL, NULL),
('CAJA', NULL, NULL),
('DISEÑO', NULL, NULL),
('COMMUNITY MANAGER', NULL, NULL);

INSERT INTO `provincias` (`nombre`, `id_pais`) VALUES
('Buenos Aires', 1), ('CABA', 1), ('Catamarca', 1), ('Chaco', 1), ('Chubut', 1), ('Córdoba', 1), ('Corrientes', 1), ('Entre Ríos', 1), ('Formosa', 1), ('Jujuy', 1), ('La Pampa', 1), ('La Rioja', 1), ('Mendoza', 1), ('Misiones', 1), ('Neuquén', 1), ('Río Negro', 1), ('Salta', 1), ('San Juan', 1), ('San Luis', 1), ('Santa Cruz', 1), ('Santa Fe', 1), ('Santiago del Estero', 1), ('Tierra del Fuego', 1), ('Tucumán', 1);

INSERT INTO `documento_tipos` (`nombre`, `descripcion`, `activo`) VALUES
('Copia de DNI', 'Copia digital del Documento Nacional de Identidad', 1),
('Constancia de CUIL', 'Constancia de Código Único de Identificación Laboral', 1),
('Alta Temprana AFIP (F. 931)', 'Formulario de alta temprana en AFIP', 1),
('Contrato de Trabajo', 'Copia del contrato laboral', 1),
('Título / Certificado', 'Títulos académicos o certificados de cursos', 1),
('Receta Médica', 'Recetas médicas para justificar ausencias o tratamientos', 1),
('Parte Médico', 'Informes o partes médicos por enfermedad o accidente', 1),
('Examen Preocupacional', 'Resultados del examen médico preocupacional', 1),
('Cert. Antecedentes Penales', 'Certificado de antecedentes penales', 1),
('Otro', 'Cualquier otro documento no clasificado', 1);

INSERT INTO `art` (`nombre`, `activo`) VALUES
('ASOCIART ART S.A.', 1),
('Prevención ART S.A.', 1),
('Riesgos del Trabajo S.A.', 1),
('Sancor ART S.A.', 1);

INSERT INTO `bancos` (`nombre`, `activo`) VALUES
('Banco Nación', 1),
('Banco Provincia', 1),
('Banco Santander', 1),
('Banco Galicia', 1),
('Banco Ciudad', 1),
('Banco Patagonia', 1);

INSERT INTO `obras_sociales` (`nombre`, `abreviatura`, `activo`) VALUES
('OSDE', 'OSDE', 1),
('Swiss Medical', 'Swiss Medical', 1),
('Medifé', 'Medifé', 1),
('OSECAC', 'OSECAC', 1),
('IAPOS', 'IAPOS', 1);

INSERT INTO `convenios` (`id_convenio`, `nombre`, `numero`) VALUES
(1, 'Gastronómicos', 'CCT 389/04'),
(2, 'Comercio', 'CCT 130/75'),
(3, 'Rurales', 'Ley 26.727'),
(4, 'Fuera de Convenio', NULL);

INSERT INTO `categorias_convenio` (`id_convenio`, `nombre`, `sueldo_basico`) VALUES
(4, 'FUERA DE CONVENIO', NULL),
(2, 'A2 - ADMINIST 1°', 1080738.00),
(2, 'A3 - ADMINIST 2°', 1072597.00),
(2, 'A4 - RECEPC', 1059576.00),
(2, 'A6 - MAESTRANZA', 1050348.00),
(2, 'B3 - 2° VENDEDOR', 1075311.00),
(2, 'C2 - AUXILIAR 1º', 1075311.00),
(2, 'C3 - AUXILIAR 2º', 1065543.00);

INSERT INTO `sindicatos` (`nombre`, `activo`) VALUES
('UTHGRA (Gastronómicos)', 1),
('SEC (Comercio)', 1),
('UATRE (Rurales)', 1);

INSERT INTO `conceptos_salariales` (`id_convenio`, `descripcion`, `tipo`, `base_calculo`, `valor_porcentual`, `valor_fijo`, `codigo_recibo`) VALUES
-- CONCEPTOS GLOBALES (APLICAN A TODOS LOS CONVENIOS)
(NULL, 'SIPA (Jubilación)', 'aporte', 'remunerativo', 11.00, NULL, '4010'),
(NULL, 'Ley 19032 (PAMI)', 'aporte', 'remunerativo', 3.00, NULL, '4020'),
(NULL, 'Obra Social', 'aporte', 'remunerativo', 3.00, NULL, '4050'),
(NULL, 'Adelantos', 'aporte', 'fijo', NULL, NULL, '3003'),
(NULL, 'Embargo Judicial', 'aporte', 'fijo', NULL, NULL, '4090'),
(NULL, 'Retención de Ganancias', 'aporte', 'fijo', NULL, NULL, '6981'),
(NULL, 'Redondeo', 'aporte', 'fijo', NULL, NULL, '9999'),
(NULL, 'Seguro de Vida Obligatorio', 'contribucion', 'fijo', NULL, 24.35, NULL),
(NULL, 'Fondo de Hipoacusia', 'contribucion', 'fijo', NULL, 0.60, NULL),

-- CONCEPTOS COMERCIO (CCT 130/75) - ID Convenio 2
-- Haberes Remunerativos
(2, 'Sueldo Básico', 'remunerativo', 'fijo', NULL, NULL, '2010'),
(2, 'Antigüedad', 'remunerativo', 'remunerativo', 1.00, NULL, '2022'),
(2, 'Presentismo', 'remunerativo', 'remunerativo', 8.33, NULL, '2053'),
(2, 'Comisiones', 'remunerativo', 'fijo', NULL, NULL, '2090'),
(2, 'Horas Médico / Estudios', 'remunerativo', 'fijo', NULL, NULL, '2107'),
(2, 'Días de Enfermedad', 'remunerativo', 'fijo', NULL, NULL, '2108'),
(2, 'Inasistencias Justificadas', 'remunerativo', 'fijo', NULL, NULL, '2110'),
(2, 'Vacaciones No Gozadas', 'remunerativo', 'fijo', NULL, NULL, '2152'),
(2, 'SAC s/ Vacaciones No Gozadas', 'remunerativo', 'fijo', NULL, NULL, '2160'),
(2, 'Licencia por Maternidad', 'remunerativo', 'fijo', NULL, NULL, '2012'),
(2, 'Reconocimiento Empresa Aporte Adicional OS', 'remunerativo', 'fijo', NULL, NULL, '4171'),
-- Haberes No Remunerativos
(2, 'Presentismo No Remunerativo', 'no_remunerativo', 'no_remunerativo', 8.33, NULL, '2400'),
(2, 'Plus Vacacional', 'no_remunerativo', 'fijo', NULL, NULL, '2411'),
(2, 'Incremento No Remunerativo (Acuerdo)', 'no_remunerativo', 'fijo', NULL, NULL, '2510'),
(2, 'Antigüedad No Remunerativo', 'no_remunerativo', 'no_remunerativo', 1.00, NULL, '2511'),
(2, 'SAC Proporcional', 'no_remunerativo', 'fijo', NULL, NULL, '2562'),
(2, 'Evento Mensual', 'no_remunerativo', 'fijo', NULL, NULL, '2618'),
(2, 'Aguinaldo s/ Eventos', 'no_remunerativo', 'fijo', NULL, NULL, '2619'),
-- Aportes (Deducciones)
(2, 'Cuota Adicional Obra Social', 'aporte', 'fijo', NULL, NULL, '4058'),
(2, 'Diferencia Obra Social', 'aporte', 'fijo', NULL, NULL, '4065'),
(2, 'Descuento Diferencia Obra Social', 'aporte', 'fijo', NULL, NULL, '4066'),
(2, 'Aporte Adicional Obra Social', 'aporte', 'fijo', NULL, NULL, '4170'),
(2, 'Cuota Sindical (Bs.As.)', 'aporte', 'remunerativo', 2.00, NULL, '4207'),
(2, 'Cuota Sindical (Rosario)', 'aporte', 'remunerativo', 2.00, NULL, '4209'),
(2, 'Cuota Sindical (CABA)', 'aporte', 'remunerativo', 2.00, NULL, '4210'),
(2, 'FAECYS', 'aporte', 'remunerativo', 0.50, NULL, '4500'),
-- Contribuciones (Empleador)
(2, 'Contribución La Estrella', 'contribucion', 'remunerativo', 3.50, NULL, '5100'),
(2, 'Contribución Adicional Obra Social', 'contribucion', 'fijo', NULL, NULL, '5170'),

-- CONCEPTOS GASTRONÓMICOS (CCT 389/04) - ID Convenio 1
(1, 'Sueldo Básico', 'remunerativo', 'fijo', NULL, NULL, '2010'),
(1, 'Adicional por Antigüedad', 'remunerativo', 'remunerativo', 2.00, NULL, '2022'),
(1, 'Alimentación y Vivienda', 'remunerativo', 'fijo', NULL, NULL, NULL),
(1, 'Cuota Sindical UTHGRA', 'aporte', 'remunerativo', 2.50, NULL, '4210'),
(1, 'Seguro de Vida y Sepelio', 'aporte', 'remunerativo', 2.00, NULL, '4207'),

-- CONCEPTOS RURALES (Ley 26.727) - ID Convenio 3
(3, 'Sueldo Básico', 'remunerativo', 'fijo', NULL, NULL, '2010'),
(3, 'Bonificación por Antigüedad', 'remunerativo', 'remunerativo', 1.00, NULL, '2022'),
(3, 'Cuota Sindical UATRE', 'aporte', 'remunerativo', 2.00, NULL, '4210'),
(3, 'Seguro de Sepelio', 'aporte', 'remunerativo', 1.50, NULL, NULL),
(3, 'Contribución RENATRE', 'contribucion', 'remunerativo', 1.50, NULL, NULL);

INSERT INTO `modalidades_contrato` (`nombre`, `descripcion`, `activo`) VALUES
('Tiempo Indeterminado', 'Contrato estándar sin fecha de finalización.', 1),
('Plazo Fijo', 'Contrato con una fecha de finalización específica (máximo 5 años).', 1),
('Eventual', 'Para cubrir necesidades transitorias de la empresa.', 1),
('De Temporada', 'Para actividades que se cumplen en determinadas épocas del año.', 1),
('Por Obra', 'Contrato cuya duración está atada a la finalización de una obra específica.', 1);

-- Insertar usuario administrador por defecto. La contraseña es "admin". Se recomienda cambiarla inmediatamente.
INSERT INTO `usuarios` (`username`, `password`, `nombre_completo`, `rol`, `estado`) VALUES
('admin', '$2y$10$kaRA59ZbUte.wQ0MT/E6nOjMu7bASUBfJGkQkffvdo5FuVO742DYK', 'Administrador', 'admin', 'activo');

-- ---
-- PASO 4: Definición de Constraints (Relaciones)
-- ---

ALTER TABLE `auditoria` ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
ALTER TABLE `vacaciones_balance` ADD CONSTRAINT `fk_balance_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE;
ALTER TABLE `vacaciones_calculos` ADD CONSTRAINT `fk_calculos_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE;
ALTER TABLE `vacaciones_calculos` ADD CONSTRAINT `fk_calculos_usuario` FOREIGN KEY (`calculado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;
ALTER TABLE `ausencias` ADD CONSTRAINT `fk_ausencia_novedad` FOREIGN KEY (`id_novedad`) REFERENCES `novedades` (`id_novedad`) ON DELETE CASCADE;
ALTER TABLE `convenios` ADD CONSTRAINT `fk_convenio_sindicato` FOREIGN KEY (`id_sindicato`) REFERENCES `sindicatos` (`id_sindicato`) ON DELETE SET NULL;
ALTER TABLE `conceptos_salariales` ADD CONSTRAINT `fk_concepto_convenio` FOREIGN KEY (`id_convenio`) REFERENCES `convenios` (`id_convenio`) ON DELETE CASCADE;
ALTER TABLE `categorias_convenio` ADD CONSTRAINT `fk_categoria_convenio` FOREIGN KEY (`id_convenio`) REFERENCES `convenios` (`id_convenio`) ON DELETE CASCADE;
ALTER TABLE `datos_confidenciales` ADD CONSTRAINT `fk_confidencial_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `documentacion_empleado` ADD CONSTRAINT `documentacion_empleado_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_doc_tipo` FOREIGN KEY (`id_tipo_documento`) REFERENCES `documento_tipos` (`id_tipo_documento`);
ALTER TABLE `notificaciones` ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id_usuario`);
ALTER TABLE `novedades` ADD CONSTRAINT `fk_novedad_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE, ADD CONSTRAINT `fk_novedad_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT;
ALTER TABLE `personal` ADD CONSTRAINT `fk_personal_pais` FOREIGN KEY (`id_pais`) REFERENCES `paises` (`id_pais`) ON DELETE SET NULL, ADD CONSTRAINT `fk_personal_provincia` FOREIGN KEY (`id_provincia`) REFERENCES `provincias` (`id_provincia`) ON DELETE SET NULL, ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`), ADD CONSTRAINT `personal_ibfk_2` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`), ADD CONSTRAINT `personal_ibfk_3` FOREIGN KEY (`id_funcion`) REFERENCES `funciones` (`id_funcion`);
ALTER TABLE `provincias` ADD CONSTRAINT `provincias_ibfk_1` FOREIGN KEY (`id_pais`) REFERENCES `paises` (`id_pais`);
ALTER TABLE `sindicatos` ADD CONSTRAINT `fk_sindicato_obrasocial` FOREIGN KEY (`id_obra_social`) REFERENCES `obras_sociales` (`id_obra_social`) ON DELETE SET NULL;
ALTER TABLE `sucursales` ADD CONSTRAINT `sucursales_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_emp`);
ALTER TABLE `usuarios` ADD CONSTRAINT `fk_usuario_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL;

-- ---
-- PASO 5: Triggers Adicionales
-- ---

DELIMITER $$

-- Trigger que se dispara DESPUÉS de ACTUALIZAR una novedad.
DROP TRIGGER IF EXISTS `after_novedad_vacaciones_update`$$
CREATE TRIGGER `after_novedad_vacaciones_update` AFTER UPDATE ON `novedades` FOR EACH ROW BEGIN
    DECLARE v_dias_vacaciones INT;
    DECLARE v_periodo YEAR;
    IF NEW.tipo = 'Vacaciones' THEN
        SET v_periodo = YEAR(NEW.fecha_desde);
        SET v_dias_vacaciones = DATEDIFF(NEW.fecha_hasta, NEW.fecha_desde) + 1;
        IF OLD.estado != 'Aprobada' AND NEW.estado = 'Aprobada' THEN
            UPDATE `vacaciones_balance` SET `dias_tomados` = `dias_tomados` + v_dias_vacaciones, `dias_pendientes` = `dias_corresponden` - `dias_tomados` WHERE `id_personal` = NEW.id_personal AND `periodo_anio` = v_periodo;
        ELSEIF OLD.estado = 'Aprobada' AND NEW.estado != 'Aprobada' THEN
            UPDATE `vacaciones_balance` SET `dias_tomados` = GREATEST(0, `dias_tomados` - v_dias_vacaciones), `dias_pendientes` = `dias_corresponden` - `dias_tomados` WHERE `id_personal` = NEW.id_personal AND `periodo_anio` = v_periodo;
        END IF;
    END IF;
END$$

DELIMITER ;

COMMIT;