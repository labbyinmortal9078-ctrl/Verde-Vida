-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: vivero4
-- ------------------------------------------------------
-- Server version	9.7.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'a1a0bf0b-434d-11f1-9666-d85ed365258e:1-173';

--
-- Table structure for table `ciclo_crecimiento`
--

DROP TABLE IF EXISTS `ciclo_crecimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ciclo_crecimiento` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ID_especie` int NOT NULL,
  `tiempo_germinacion` int DEFAULT NULL,
  `tiempo_maduracion` int DEFAULT NULL,
  `altura_maxima` decimal(8,2) DEFAULT NULL,
  `diametro_maximo` decimal(8,2) DEFAULT NULL,
  `epoca_siembra` varchar(100) DEFAULT NULL,
  `epoca_floracion` varchar(100) DEFAULT NULL,
  `propagacion` enum('semillas','esqueje','división','injerto','bulbo') DEFAULT NULL,
  `vida_util` int DEFAULT NULL,
  `hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `ID_especie` (`ID_especie`),
  CONSTRAINT `ciclo_crecimiento_ibfk_1` FOREIGN KEY (`ID_especie`) REFERENCES `especies` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciclo_crecimiento`
--

LOCK TABLES `ciclo_crecimiento` WRITE;
/*!40000 ALTER TABLE `ciclo_crecimiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `ciclo_crecimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text,
  `frecuencia` enum('frecuente','ocasional') DEFAULT NULL,
  `segmento` enum('minorista','mayorista') DEFAULT NULL,
  `detalles` text,
  `hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuidados`
--

DROP TABLE IF EXISTS `cuidados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuidados` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `frecuencia_riego` varchar(100) DEFAULT NULL,
  `luz_solar` enum('sombra','sombra parcial','sol directo','luz indirecta') DEFAULT NULL,
  `temperatura_minima` decimal(5,2) DEFAULT NULL,
  `temperatura_maxima` decimal(5,2) DEFAULT NULL,
  `humedad_ideal` enum('baja','media','alta') DEFAULT NULL,
  `ph_minimo` decimal(3,1) DEFAULT NULL,
  `ph_maximo` decimal(3,1) DEFAULT NULL,
  `fertilizacion` text,
  `poda` text,
  `hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuidados`
--

LOCK TABLES `cuidados` WRITE;
/*!40000 ALTER TABLE `cuidados` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuidados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuidados_especie`
--

DROP TABLE IF EXISTS `cuidados_especie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuidados_especie` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ID_especie` int NOT NULL,
  `ID_cuidados` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_especie` (`ID_especie`),
  KEY `ID_cuidados` (`ID_cuidados`),
  CONSTRAINT `cuidados_especie_ibfk_1` FOREIGN KEY (`ID_especie`) REFERENCES `especies` (`ID`),
  CONSTRAINT `cuidados_especie_ibfk_2` FOREIGN KEY (`ID_cuidados`) REFERENCES `cuidados` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuidados_especie`
--

LOCK TABLES `cuidados_especie` WRITE;
/*!40000 ALTER TABLE `cuidados_especie` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuidados_especie` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ventas` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ID_venta` int NOT NULL,
  `ID_inventario` int NOT NULL,
  `cantidad` int NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_venta` (`ID_venta`),
  KEY `ID_inventario` (`ID_inventario`),
  CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`ID_venta`) REFERENCES `ventas` (`ID`),
  CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`ID_inventario`) REFERENCES `inventario` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especies`
--

DROP TABLE IF EXISTS `especies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especies` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre_comun` varchar(255) NOT NULL,
  `nombre_cientifico` varchar(255) NOT NULL,
  `familia` varchar(100) DEFAULT NULL,
  `origen` varchar(100) DEFAULT NULL,
  `tipo_planta` enum('arbol','arbusto','hierba','suculenta','trepadora','bulbo','cactus','palmera') DEFAULT NULL,
  `descripcion` text,
  `dificultad_cultivo` enum('baja','media','alta') DEFAULT NULL,
  `hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especies`
--

LOCK TABLES `especies` WRITE;
/*!40000 ALTER TABLE `especies` DISABLE KEYS */;
INSERT INTO `especies` VALUES (2,'Flor','Diente_de_leon','Cilarods','Africa','arbusto','Planta poco comun de crecimiento ','baja','2026-05-20 20:36:55'),(3,'Sven Batke','The Conversation*','The Conversation*','Africa','suculenta','Se necesitaron otros 2.000 millones de años para que los primeros organismos unicelulares aparecieran en el océano, incluida la primera alga Grypania spiralis, que tenía aproximadamente el tamaño de una moneda de 50 centavos de dólar.','alta','2026-05-21 18:28:37'),(4,'Sven Batke','Diente','The Conversation*','Africa','arbol','Nightcore - Emergency (Pegboard Nerds) - (Lyrics)','alta','2026-05-22 01:19:00'),(5,'Sven Batke','The Conversation*','Cilarods','Africa','trepadora','asdasd','alta','2026-05-22 16:00:19'),(6,'flor cadáver','La Amorphophallus titanum ','sin Familia','Selva','arbol','La flor más grande: La Amorphophallus titanum (flor cadáver) ','alta','2026-05-22 16:36:35'),(7,'Sven Batke','The Conversation*','sin Familia','Selva','arbusto','Es un arbusto que mas quieres saber ? ','media','2026-05-22 21:01:39');
/*!40000 ALTER TABLE `especies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ID_especie` int DEFAULT NULL,
  `cantidad_disponible` int DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL,
  `precio_costo` decimal(10,2) DEFAULT NULL,
  `estado` enum('semilla','germinando','plántula','joven','madura','floracion') DEFAULT NULL,
  `calidad` enum('excelente','regular','mala') DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `notas` text,
  `hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `ID_especie` (`ID_especie`),
  CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`ID_especie`) REFERENCES `especies` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
INSERT INTO `inventario` VALUES (2,2,90,50000.00,36000.00,'floracion','excelente','2026-05-20','3','Llego de narnia','2026-05-20 20:36:55'),(3,3,10,16000.00,3500.00,'floracion','regular','2026-05-21','3','Se necesitaron otros 2.000 millones de años para que los primeros organismos unicelulares aparecieran en el océano, incluida la primera alga Grypania spiralis, que tenía aproximadamente el tamaño de una moneda de 50 centavos de dólar.','2026-05-21 18:28:37'),(4,4,89,78999.00,98889.00,'floracion','excelente','2026-05-21','3','Nightcore - Emergency (Pegboard Nerds) - (Lyrics)','2026-05-22 01:19:00'),(5,5,9,98999.00,9889.00,'floracion','excelente','2026-05-22','3','gdg','2026-05-22 16:00:19'),(6,6,1,36000.00,1500.00,'joven','regular','2026-05-22','4','La flor más grande: La Amorphophallus titanum (flor cadáver) puede superar los \\(3\\text{ metros}\\) de altura, pesar más de \\(75\\text{ kg}\\) y desprender un fuerte olor a carne podrida para atraer polinizadores','2026-05-22 16:36:35'),(7,7,1,96000.00,16000.00,'madura','excelente','2026-05-22','3','La planta esta en el almacen 3 ','2026-05-22 21:01:39');
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulos`
--

DROP TABLE IF EXISTS `modulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modulos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `orden` int DEFAULT '0',
  `activo` tinyint DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulos`
--

LOCK TABLES `modulos` WRITE;
/*!40000 ALTER TABLE `modulos` DISABLE KEYS */;
INSERT INTO `modulos` VALUES (1,'dashboard','Panel principal de control','fa-tachometer-alt','main.php',1,1),(2,'usuarios','Administrar usuarios del sistema','fa-users','admin_usuarios.php',2,1),(3,'plantas','Ver listado de plantas','fa-seedling','ver_plantas.php',3,1),(4,'registrar_planta','Registrar nuevas plantas','fa-plus-circle','registrar_plantas.php',4,1),(5,'eliminar_planta','Eliminar plantas del sistema','fa-trash','eliminar_planta.php',5,1),(6,'exportar','Exportar datos a Excel','fa-file-excel','exportar_excel.php',6,1),(7,'perfil','Editar perfil de usuario','fa-user-edit','main.php',7,1);
/*!40000 ALTER TABLE `modulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `puede_ver` tinyint DEFAULT '0',
  `puede_crear` tinyint DEFAULT '0',
  `puede_editar` tinyint DEFAULT '0',
  `puede_eliminar` tinyint DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_permiso` (`rol`,`modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (57,'administrador','dashboard',1,1,1,1),(58,'administrador','usuarios',1,1,1,1),(59,'administrador','plantas',1,1,1,1),(60,'administrador','registrar_planta',1,1,1,1),(61,'administrador','eliminar_planta',1,1,1,1),(62,'administrador','exportar',1,1,1,1),(63,'administrador','perfil',1,1,1,1),(64,'jardinero','dashboard',1,0,0,0),(65,'jardinero','usuarios',0,0,0,0),(66,'jardinero','plantas',1,1,1,0),(67,'jardinero','registrar_planta',1,1,1,0),(68,'jardinero','eliminar_planta',0,0,0,0),(69,'jardinero','exportar',1,1,0,0),(70,'jardinero','perfil',1,1,1,0),(71,'empleado','dashboard',1,0,0,0),(72,'empleado','usuarios',0,0,0,0),(73,'empleado','plantas',1,0,0,0),(74,'empleado','registrar_planta',1,1,0,0),(75,'empleado','eliminar_planta',0,0,0,0),(76,'empleado','exportar',1,0,0,0),(77,'empleado','perfil',1,0,0,0),(78,'usuario','dashboard',1,0,0,0),(79,'usuario','usuarios',0,0,0,0),(80,'usuario','plantas',1,0,0,0),(81,'usuario','registrar_planta',0,0,0,0),(82,'usuario','eliminar_planta',0,0,0,0),(83,'usuario','exportar',0,0,0,0),(84,'usuario','perfil',1,0,0,0);
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contraseña` text NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text,
  `rol` enum('usuario','administrador','jardinero','empleado') NOT NULL DEFAULT 'usuario',
  `fecha_contratacion` date DEFAULT NULL,
  `hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(64) DEFAULT NULL,
  `ven_token` datetime DEFAULT NULL,
  `email_verificado` tinyint(1) NOT NULL DEFAULT '0',
  `token_verificacion` varchar(100) DEFAULT NULL,
  `fecha_verificacion` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_baja` datetime DEFAULT NULL,
  `motivo_baja` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (6,'juni','sosi','sosi204@gmail.com','907890','9983412','m102 c11','usuario','2020-05-26','2026-05-20 19:48:53',NULL,NULL,0,NULL,NULL,1,NULL,NULL),(7,'Vanzs','junior','sosapatricio2025@gmail.com','$2y$10$MuLy5.ydI7uxVRFhNRebQ.BGlxmLxAUEowqE2ZlTi9z8smhV2QArS','9983412','m102 c11','administrador','2026-05-20','2026-05-20 21:42:55',NULL,NULL,1,NULL,'2026-05-20 18:43:14',1,NULL,NULL),(9,'javier','patri','sosapatricio2125@gmail.com','$2y$10$lb5xn8JGPTWVS3cKbWnCuu4t5fAHcC/0Nb2jEb.tjawB3G9lu/NVm','9983412','m102 c11','usuario','2026-05-21','2026-05-21 17:55:07',NULL,NULL,0,'323858a56ed5e7750d0ec29e32bea97baf32bd28f2cd4e309fc6497144986b95',NULL,1,NULL,NULL),(10,'sosisa','javitoar','sosapatricio2015@gmail.com','$2y$10$C0C5hx8v.h61HVn5dxg5DO1U7oiXFWcsr2BM5Wd88ebbfS0wNpjF6','9983412','m102 c11','usuario','2026-05-21','2026-05-21 18:25:32',NULL,NULL,1,NULL,'2026-05-21 15:26:25',1,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ID_cliente` int NOT NULL,
  `ID_usuario` int NOT NULL,
  `fecha_venta` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','completada','cancelada') DEFAULT NULL,
  `metodo_pago` enum('efectivo','tarjeta','transferencia') DEFAULT NULL,
  `direccion_entrega` text,
  `nota_venta` text,
  PRIMARY KEY (`ID`),
  KEY `ID_cliente` (`ID_cliente`),
  KEY `ID_usuario` (`ID_usuario`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`ID_cliente`) REFERENCES `clientes` (`ID`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-02 19:35:28
