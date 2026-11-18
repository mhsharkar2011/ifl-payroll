-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: ifl_payroll
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `timestamp` timestamp NOT NULL,
  `status` enum('check_in','check_out','break_in','break_out','overtime_in','overtime_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_by` int NOT NULL DEFAULT '0',
  `device_serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`employee_id`,`timestamp`,`status`),
  KEY `attendances_employee_id_timestamp_index` (`employee_id`,`timestamp`),
  KEY `attendances_status_index` (`status`),
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=261 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
INSERT INTO `attendances` VALUES (230,43,'2026-09-28 03:15:30','check_in',2,'MB460',NULL,NULL,NULL,'Imported from ZKTeco MB460 export','2026-09-30 23:28:37','2026-09-30 23:28:37'),(231,48,'2026-09-28 12:30:45','check_out',2,'MB460',NULL,NULL,NULL,'Imported from ZKTeco MB460 export','2026-09-30 23:28:37','2026-09-30 23:28:37'),(232,49,'2026-09-28 03:20:10','check_in',1,'MB460',NULL,NULL,NULL,'Imported from ZKTeco MB460 export','2026-09-30 23:28:37','2026-09-30 23:28:37'),(233,49,'2026-09-28 12:35:00','check_out',1,'MB460',NULL,NULL,NULL,'Imported from ZKTeco MB460 export','2026-09-30 23:28:37','2026-09-30 23:28:37'),(234,42,'2026-10-01 03:48:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(236,43,'2026-10-01 02:06:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(238,44,'2026-10-01 03:25:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(239,45,'2026-10-01 02:10:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(241,46,'2026-10-01 03:55:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(242,47,'2026-10-01 03:33:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(243,48,'2026-10-01 02:22:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(245,49,'2026-10-01 03:21:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(246,50,'2026-10-01 03:41:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(248,51,'2026-10-01 04:28:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(249,52,'2026-10-01 02:36:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(251,53,'2026-10-01 02:31:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(252,54,'2026-10-01 04:32:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(254,55,'2026-10-01 03:37:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(256,56,'2026-10-01 03:56:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(258,57,'2026-10-01 04:10:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45'),(259,58,'2026-10-01 04:40:00','check_in',1,'MOCK-ZK-001','192.168.1.100',NULL,NULL,NULL,'2026-09-30 23:28:45','2026-09-30 23:28:45');
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `last_action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_message` text COLLATE utf8mb4_unicode_ci,
  `last_action_at` timestamp NULL DEFAULT NULL,
  `restored_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_created_by_foreign` (`created_by`),
  KEY `backups_status_index` (`status`),
  KEY `backups_created_at_index` (`created_at`),
  CONSTRAINT `backups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
INSERT INTO `backups` VALUES (1,'backup_2026_09_30_112319.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112319.sql',39222,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:23:19','2026-09-30 05:23:19'),(2,'backup_2026_09_30_112319.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112319.sql',38682,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:23:19','2026-09-30 05:23:19'),(3,'backup_2026_09_30_112319.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112319.sql',37719,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:23:19','2026-09-30 05:23:19'),(4,'backup_2026_09_30_112319.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112319.sql',36756,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:23:19','2026-09-30 05:23:20'),(5,'backup_2026_09_30_112320.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112320.sql',35793,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:23:20','2026-09-30 05:23:20'),(6,'backup_2026_09_30_112320.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112320.sql',34828,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:23:20','2026-09-30 05:23:20'),(7,'backup_2026_09_30_112603.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_09_30_112603.sql',34451,'auto','completed',NULL,NULL,NULL,NULL,'Daily automatic backup',NULL,'2026-09-30 05:26:03','2026-09-30 05:26:04'),(8,'backup_2026_10_01_030944.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_10_01_030944.sql',39808,'manual','completed','backup','Backup completed','2026-09-30 21:09:44',NULL,NULL,2,'2026-09-30 21:09:44','2026-09-30 21:09:44'),(9,'backup_2026_10_03_083452.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_10_03_083452.sql',39442,'manual','completed','backup','Backup completed','2026-10-03 02:34:52',NULL,NULL,2,'2026-10-03 02:34:52','2026-10-03 02:34:52'),(10,'backup_2026_10_03_094909.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_10_03_094909.sql',40572,'manual','completed','backup','Backup completed','2026-10-03 03:49:09',NULL,NULL,2,'2026-10-03 03:49:09','2026-10-03 03:49:09'),(11,'backup_2026_10_03_101631.sql','local','C:\\laragon\\www\\ifl-payroll\\storage\\app/backups\\backup_2026_10_03_101631.sql',40899,'manual','completed','backup','Backup completed','2026-10-03 04:16:31',NULL,NULL,2,'2026-10-03 04:16:31','2026-10-03 04:16:31');
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
INSERT INTO `cache_locks` VALUES ('ifl-payroll-cache-framework/schedule-21f06110628a1dc614b3511b1f489d2bfb21fe8f0314','Nz9GmsBtCJ9p0Sho',1790828040),('ifl-payroll-cache-framework/schedule-fe32735e0a0bf60f821b0793c7a6d6347a54c3a40314','PYrbQCpOtlZqggen',1790828040);
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_name_unique` (`name`),
  UNIQUE KEY `departments_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'MANAGEMENT',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(2,'COMMERCIAL',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(3,'ACCOUNTS AND FINANCE','HR',NULL,1,'2026-09-30 21:06:34','2026-09-30 22:40:01',NULL),(4,'IT',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(5,'PROCUREMENT',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(6,'ADMIN',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `designations_name_unique` (`name`),
  UNIQUE KEY `designations_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
INSERT INTO `designations` VALUES (1,'CHIEF OPERATING OFFICER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(2,'MANAGER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(3,'ASSISTANCE MANAGER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(4,'DEPUTY MANAGER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(5,'SENIOR INTERNAL OFFICER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(6,'ASSISTANT MANAGER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(7,'EXECUTIVE',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(8,'ASSISTANT OFFICER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(9,'SENIOR EXECUTIVE',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(10,'OFFICE ASSISTANCE',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(11,'CLEANER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(12,'DRIVER',NULL,NULL,1,'2026-09-30 21:06:34','2026-09-30 21:06:34',NULL),(13,'ADMIN','AD','AD',1,'2026-09-30 21:06:34','2026-09-30 21:21:53',NULL);
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL DEFAULT '4370',
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` int NOT NULL DEFAULT '0',
  `timeout` int NOT NULL DEFAULT '30',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `devices_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
INSERT INTO `devices` VALUES (1,'Head Office','192.168.1.100',4370,NULL,'MB460',1324,30,1,'2026-09-30 02:46:54',NULL,'2026-09-30 02:46:46','2026-09-30 02:46:54');
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `designation_id` bigint unsigned DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL,
  `status` enum('active','inactive','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `device_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joining_date` date NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_id_unique` (`employee_id`),
  UNIQUE KEY `employees_email_unique` (`email`),
  UNIQUE KEY `employees_device_user_id_unique` (`device_user_id`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_department_id_foreign` (`department_id`),
  KEY `employees_designation_id_foreign` (`designation_id`),
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (42,8,'150010','EFTEKHAR MAHABUB','SUNNY','sunny@mail.com','employee-avatars/fOp3bDMvNdoIv1WB4OFckwgcVU1CkO4bjMQKa2Ob.jpg','01733172007',1,1,44000.00,'active','150010','2023-12-02','1990-03-02','Mirpur, Dhaka',NULL,'2026-09-29 22:28:13','2026-10-03 03:47:30'),(43,7,'150011','MD. ASADUZZAMAN','BITOL','bitol@mail.com','employee-avatars/EwZieV7EZLCBjdngdrfJzV2cZAIJshePxN2bFavk.jpg','01733172008',2,2,44000.00,'active','150011','2023-12-03','1990-03-03','Mirpur, Dhaka',NULL,'2026-09-29 22:28:13','2026-10-03 03:47:42'),(44,6,'150012','MD. NUR','ALAM','alam@mail.com','employee-avatars/hs67RfkD5UPalO3LSC0fCjUfw1ypF5cke4GGnIf4.png','01733172009',3,2,44000.00,'active','150012','2023-12-04','1990-03-04','Mirpur, Dhaka',NULL,'2026-09-29 22:28:13','2026-10-03 03:47:53'),(45,24,'150013','MIR MASUM','ALI','ali@mail.com','employee-avatars/lRwayQq4MLdMGFSu9Vw5S45bWPUsnABrV96zOwGd.jpg','01733172010',2,3,44000.00,'active','150013','2023-12-05','1990-03-05','Mirpur, Dhaka',NULL,'2026-09-29 22:28:13','2026-10-03 03:48:02'),(46,25,'150014','MD. RUBBI AL','MAMUN','mamun@mail.com','employee-avatars/FkTA6HZp1NuwX8BzXfNxJQ8SNdq00iWB03id9FUv.jpg','01733172011',2,4,44000.00,'active','150014','2023-12-06','1990-03-06','Mirpur, Dhaka',NULL,'2026-09-29 22:28:13','2026-10-03 03:48:12'),(47,26,'150015','DOLENA','AKTER','akter@mail.com','employee-avatars/Vwry2s5eqyZst4Qif3J9NbS3WEba23yY7iiQvt8e.jpg','01733172012',3,5,44000.00,'active','150015','2023-12-07','1990-03-07','Mirpur, Dhaka',NULL,'2026-09-29 22:28:13','2026-10-03 03:48:45'),(48,27,'150016','MD. MONIR HOSSAIN','SHARKAR','sharkar@mail.com','employee-avatars/v1R3xH0AZjKJpYUT780nyczs8WtHMHpPJkWwRBW4.jpg','01733172013',4,6,44000.00,'active','150016','2023-12-08','1990-03-08','Mirpur, Dhaka',NULL,'2026-09-29 22:28:14','2026-10-03 03:35:10'),(49,28,'150017','MD RAIHAN','MIA','mia@mail.com','employee-avatars/3rZ5pwJkpnNrkD93TvkrdG3j1apU1FS4JpApt1lN.jpg','01733172014',5,7,44000.00,'active','150017','2023-12-09','1990-03-09','Mirpur, Dhaka',NULL,'2026-09-29 22:28:14','2026-10-03 03:42:35'),(50,29,'150018','MD. KAWSAR','HOSSAIN','hossain@mail.com','employee-avatars/T1MfTrF1rhgP9JWmLfW7K7I705kgtnq6BKVFNCi8.jpg','01733172015',2,8,44000.00,'active','150018','2023-12-10','1990-03-10','Mirpur, Dhaka',NULL,'2026-09-29 22:28:14','2026-10-03 03:43:15'),(51,30,'150019','RAKIBUL','HASAN','hasan@mail.com','employee-avatars/zeCwMZV92SvRczkan8ax2m1hnYkQEoyS31NFXWiL.png','01733172016',3,7,44000.00,'active','150019','2023-12-11','1990-03-11','Mirpur, Dhaka',NULL,'2026-09-29 22:28:14','2026-10-03 03:46:42'),(52,31,'100020','SHAHRIER','MAKSUD','maksud@mail.com','employee-avatars/t6pUsPdfIL0Ll4nScX7gNMlyMyJlTorNbUusiw8D.jpg','01733172017',2,9,44000.00,'active','100020','2023-12-12','1990-03-12','Mirpur, Dhaka',NULL,'2026-09-29 22:28:14','2026-10-03 03:47:08'),(53,32,'150022','MD. SHAWKAT','AKBAR','akbar@mail.com','employee-avatars/vCmQdeTZHYaqXKrQOzOD3KqiqncbRdoMH2LUCuKN.jpg','01733172019',6,10,44000.00,'active','150022','2023-12-14','1990-03-14','Mirpur, Dhaka',NULL,'2026-09-29 22:28:14','2026-10-03 03:47:20'),(54,33,'150023','MST.HALIMA','AKTER','halima@mail.com','employee-avatars/AIpI61PaH05X3PomK85fnqRkhkTRBvZqqGWWEZHr.jpg','01733172020',6,11,44000.00,'active','150023','2023-12-15','1990-03-15','Mirpur, Dhaka',NULL,'2026-09-29 22:28:15','2026-10-03 03:42:45'),(55,34,'150024','MD. MONIRUL','ISLAM','islam@mail.com','employee-avatars/NUCFQGYkiGrOdUNm9d6ihw6zOLEjfu37stsh9TuF.jpg','01733172021',6,12,44000.00,'active','150024','2023-12-16','1990-03-16','Mirpur, Dhaka',NULL,'2026-09-29 22:28:15','2026-10-03 03:43:00'),(56,35,'150025','MD.','LATIF','latif@mail.com','employee-avatars/NcUbTFpMCJhpuyp3tj9SA2OEKsvyQLsV4HmGYcOx.jpg','01733172022',6,12,44000.00,'active','150025','2023-12-17','1990-03-17','Mirpur, Dhaka',NULL,'2026-09-29 22:28:15','2026-10-03 04:16:24'),(57,36,'150026','MD.','RIPON','ripon@mail.com','employee-avatars/WfNp8aTgpCnoYUfNxhUkYXzoY0NKLB7JGAm9m6yr.jpg','01733172023',6,12,44000.00,'active','150026','2023-12-18','1990-03-18','Mirpur, Dhaka',NULL,'2026-09-29 22:28:15','2026-10-03 03:34:50'),(58,37,'150027','SABINA','AMEEN','ameen@mail.com',NULL,'01733172024',6,13,44000.00,'active','150027','2023-12-19','1990-03-19','Mirpur, Dhaka',NULL,'2026-09-29 22:28:15','2026-09-29 22:28:15');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (15,'default','{\"uuid\":\"ecd93640-3a13-4f03-a2e8-71d9e03b388a\",\"displayName\":\"App\\\\Jobs\\\\SyncAttendanceJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SyncAttendanceJob\",\"command\":\"O:26:\\\"App\\\\Jobs\\\\SyncAttendanceJob\\\":0:{}\",\"batchId\":null},\"createdAt\":1790824440,\"delay\":null}',0,NULL,1790824440,1790824440),(16,'default','{\"uuid\":\"d5f52f00-102b-47f2-ad59-2f4d77b15f95\",\"displayName\":\"App\\\\Jobs\\\\CreateBackupJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CreateBackupJob\",\"command\":\"O:24:\\\"App\\\\Jobs\\\\CreateBackupJob\\\":2:{s:4:\\\"type\\\";s:4:\\\"auto\\\";s:5:\\\"notes\\\";s:22:\\\"Daily automatic backup\\\";}\",\"batchId\":null},\"createdAt\":1790824440,\"delay\":null}',0,NULL,1790824440,1790824440);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_30_115412_create_permission_tables',1),(5,'2026_08_30_125800_create_employees_table',1),(6,'2026_08_30_125843_create_attendances_table',1),(7,'2026_08_30_125913_create_payrolls_table',1),(8,'2026_09_26_055029_create_devices_table',1),(9,'2026_09_28_045528_add_unique_index_to_attendances_table',2),(10,'2026_09_30_100534_create_backups_table',3),(11,'2026_09_30_160756_add_status_columns_to_backups_table',4),(12,'2026_09_30_170322_add_settings_columns_to_users_table',4),(13,'2026_09_30_171741_add_avatar_to_users_table',4),(14,'2026_09_30_173920_create_departments_table',4),(15,'2026_09_30_173937_create_designations_table',4),(16,'2026_09_30_173957_update_employees_use_department_designation_fk',4),(17,'2026_10_03_092517_add_avatar_to_employees_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',2),(3,'App\\Models\\User',3),(3,'App\\Models\\User',4),(3,'App\\Models\\User',5),(3,'App\\Models\\User',6),(3,'App\\Models\\User',7),(3,'App\\Models\\User',8),(3,'App\\Models\\User',9),(3,'App\\Models\\User',12),(3,'App\\Models\\User',17),(3,'App\\Models\\User',20),(3,'App\\Models\\User',21),(3,'App\\Models\\User',22),(3,'App\\Models\\User',23),(3,'App\\Models\\User',24),(3,'App\\Models\\User',25),(3,'App\\Models\\User',26),(3,'App\\Models\\User',27),(3,'App\\Models\\User',28),(3,'App\\Models\\User',29),(3,'App\\Models\\User',30),(3,'App\\Models\\User',31),(3,'App\\Models\\User',32),(3,'App\\Models\\User',33),(3,'App\\Models\\User',34),(3,'App\\Models\\User',35),(3,'App\\Models\\User',36),(3,'App\\Models\\User',37);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payrolls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `month` int NOT NULL,
  `year` int NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `overtime_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_earnings` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deductions` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','approved','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `generated_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payrolls_employee_id_month_year_unique` (`employee_id`,`month`,`year`),
  KEY `payrolls_status_index` (`status`),
  CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_dashboard','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(2,'view_employees','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(3,'create_employees','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(4,'edit_employees','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(5,'delete_employees','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(6,'view_attendance','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(7,'create_attendance','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(8,'edit_attendance','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(9,'delete_attendance','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(10,'sync_attendance','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(11,'view_payroll','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(12,'create_payroll','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(13,'edit_payroll','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(14,'delete_payroll','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(15,'process_payroll','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(16,'manage_devices','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(17,'view_reports','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(18,'export_reports','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(19,'manage_settings','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(20,'manage_roles','web','2026-09-27 05:39:44','2026-09-27 05:39:44');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(1,2),(2,2),(3,2),(4,2),(6,2),(7,2),(8,2),(10,2),(11,2),(12,2),(17,2),(18,2),(1,3),(6,3),(7,3),(11,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(2,'HR','web','2026-09-27 05:39:44','2026-09-27 05:39:44'),(3,'Employee','web','2026-09-27 05:39:44','2026-09-27 05:39:44');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('aTrwrKgVoxr2yFzuV6TxLOGeBFjGFljoGQz2gGqf',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJrdjFCZjc3bU14UmRwcjU2OFZob0dvUkR6WUxuZlhXWjk1VDdaWUk4IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MiwicGFzc3dvcmRfaGFzaF93ZWIiOiIwZWRlZDM4ZjA4Y2VhNTU2YWYwZjNmZjNmODY3MzAzODU2MjRiZGRkNTU4NDk4OWZlN2IyOGVjNDMxOTVlOGZlIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2lmbC1wYXlyb2xsLnRlc3RcL2FkbWluXC9iYWNrdXBzXC80XC9kb3dubG9hZCIsInJvdXRlIjoiYWRtaW4uYmFja3Vwcy5kb3dubG9hZCJ9fQ==',1791022596);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Dhaka',
  `language` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `theme` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `notify_email` tinyint(1) NOT NULL DEFAULT '1',
  `notify_push` tinyint(1) NOT NULL DEFAULT '0',
  `notify_weekly` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,'2026-09-27 05:39:44','$2y$12$0taiDDPUzmqSDkxoqSieX.REXN/Juvmzkdhm7OMfNLnTakLMGSIlO','fmmdHnW3cy','2026-09-27 05:39:44','2026-09-27 05:39:44'),(2,'Super Admin','admin@mail.com','avatars/L6OGcwVZD6CglMMTi7HilrYdtoyU60AjasYUGChl.jpg',NULL,'Asia/Dhaka','en','light',1,0,1,'2026-09-27 05:39:44','$2y$12$U3ACMee5NpsHGmsXSMYbPOMR3FnPnnm/NBkreaxX0FvCRyMH4vdxO',NULL,'2026-09-27 05:39:44','2026-10-03 02:43:03'),(3,'Demo Employee','employee@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,'2026-09-27 05:39:45','$2y$12$SjgPAmoS.vUrjgvB.lVczu.aRIlE5abhK5LuIedHKuBMSJMo1JlYq',NULL,'2026-09-27 05:39:45','2026-09-27 05:39:45'),(4,'Monir Hossain','monir@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$cwKXhV7nvCtBkY1FAq2GLefH6f6nt4jInw2S4ufQqO13NmE2N3FRa',NULL,'2026-09-27 05:43:03','2026-09-27 05:43:03'),(5,'Monir Hossain','john@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$bVMCVy.qr.ExaUmSY8apm.U2P/MacpcrkhNgftqXNTi3YYcVYAMsW',NULL,'2026-09-27 05:43:04','2026-09-27 05:43:04'),(6,'MD. NUR ALAM','alam@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$2Wyce4UmWcGmFp2bXdkVEOHNrSjlLo6l2R5kUIQIz3IcRLeohYqi2',NULL,'2026-09-27 05:43:04','2026-10-03 03:47:53'),(7,'MD. ASADUZZAMAN BITOL','bitol@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$ECG.OYznyb9SN1ALFlLzH.1y1QadMmPiD/oFL1DiEHvFxprB.XiEu',NULL,'2026-09-27 05:43:04','2026-10-03 03:47:42'),(8,'EFTEKHAR MAHABUB SUNNY','sunny@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$Dt5U.oI42BbECJnR/DNR5Oayd74MRsssIiOtoG1NQC//6SPc6RZEy',NULL,'2026-09-27 05:43:04','2026-10-03 03:47:30'),(9,'Monir Hossain','masum@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$e2e2Z1nxdNu.XmS/opXxieZ5WXqlbS9OZHvtJj9mQAWSQSHMiJOoG',NULL,'2026-09-27 05:43:04','2026-09-27 05:43:04'),(12,'Nur Alam','alam@ifl-bd.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$FLf9Leg8sViaRxm4he3nFer1khraIF7ugez/oBrYp/ZYJoBEFP2aG',NULL,'2026-09-29 22:07:50','2026-09-29 22:07:50'),(17,'Monir Hossain','monir1@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$0N9.g.WAqZeVg5mez6kcDu.ye0PoZKwHFJyyQpXCqhZNMfVT7RGg2',NULL,'2026-09-29 22:23:54','2026-09-29 22:23:54'),(20,'Monir Hossain','monir2@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$HgvLu2zVlq9ckmelFDsL9e.AuJabr.WW6N1.FsxStRopA.ZLpZPdu',NULL,'2026-09-29 22:27:20','2026-09-29 22:27:20'),(21,'Monir Hossain','monir3@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$U6d9WRXb7qXe/rBWmpaOhePw6WL2qpdIhK9YEEmOY.AVhQnO/479S',NULL,'2026-09-29 22:27:20','2026-09-29 22:27:20'),(22,'Monir Hossain','monir4@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$wA/JV2CDZ/sdF6ih208deemtVe7vsLsuU1ZzB.r/uWlrPf6toaQJC',NULL,'2026-09-29 22:27:20','2026-09-29 22:27:20'),(23,'Monir Hossain','monir5@example.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$83DQrT2QFgoLBj57QxgEce9PaPTCGGQFbEfzeT7agIy.TclYPMPoa',NULL,'2026-09-29 22:27:21','2026-09-29 22:27:21'),(24,'MIR MASUM ALI','ali@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$Vj/eRkTesVN8WNpr/3wj4ewcuCFa7t8weBmNW9TgpsAcxw5tc0CLy',NULL,'2026-09-29 22:28:13','2026-09-29 22:28:13'),(25,'MD. RUBBI AL MAMUN','mamun@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$3DOBbJtxPcugoqFmPI4/mekc/xU1nTJVLZjVmAuko95u06Um/zdAq',NULL,'2026-09-29 22:28:13','2026-09-29 22:28:13'),(26,'DOLENA AKTER','akter@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$BQZAEr.qi3Iz9lJUgXl63Oaatq34rr365Qv6FJVYO9cbBZIlq0McS',NULL,'2026-09-29 22:28:13','2026-09-29 22:28:13'),(27,'MD. MONIR HOSSAIN SHARKAR','sharkar@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$UlJ/zJ1t2R0qhIs4uzxy/uQeFbGFjGqJ.nUFHxedl82VuFDJXcw8a',NULL,'2026-09-29 22:28:14','2026-09-29 22:28:14'),(28,'MD RAIHAN MIA','mia@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$ek8V/WiG8OSBzkyDMAt7BuDSjBmSZu3bUwhqyBX7pce3rXK.sgHnW',NULL,'2026-09-29 22:28:14','2026-09-29 22:28:14'),(29,'MD. KAWSAR HOSSAIN','hossain@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$T5J8RwlAykH3SJFqjhlVOO1ietRBYk4GbBL92yaJRUg0YoDJO9sRq',NULL,'2026-09-29 22:28:14','2026-09-29 22:28:14'),(30,'RAKIBUL HASAN','hasan@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$RDGGZtJPioOsXjamAjFAge8iWia8FBsa2iGNnRXTZrceVqR2hPu4a',NULL,'2026-09-29 22:28:14','2026-09-29 22:28:14'),(31,'SHAHRIER MAKSUD','maksud@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$/fHjIIGbmYfz9TM2dWlbauLuslRpO/oibkHJw6M.5eOKV8DvzogLa',NULL,'2026-09-29 22:28:14','2026-09-29 22:28:14'),(32,'MD. SHAWKAT AKBAR','akbar@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$t/bEbPchR8qAbLLLxG1XCekAgQgdeOADzv7156Kqx8HPFqZBpOIQq',NULL,'2026-09-29 22:28:14','2026-09-29 22:28:14'),(33,'MST.HALIMA AKTER','halima@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$OQC/.3ifNiBjvJjMpJrX8.tvzQbkA0ZFH2ziKE.mkmIvBKR61msBy',NULL,'2026-09-29 22:28:15','2026-09-29 22:28:15'),(34,'MD. MONIRUL ISLAM','islam@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$UXvU9v5rjGchaGgtz/bti.p2jkBa5zvUZ5oSBHQ2qfkjEL4YLgA7S',NULL,'2026-09-29 22:28:15','2026-09-29 22:28:15'),(35,'MD. LATIF','latif@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$pqn6XQLQHfN2cdJbx7h8Z.9usj2GAZjtSmz6gLWyBptdVFpDF2Zau',NULL,'2026-09-29 22:28:15','2026-09-29 22:28:15'),(36,'MD. RIPON','ripon@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$UFMNxN.i8sdEy.FoCX574u.Fac0YXtAsyUvEW5Vy9p4LcehbHanOO',NULL,'2026-09-29 22:28:15','2026-09-29 22:28:15'),(37,'SABINA AMEEN','ameen@mail.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$YaNHiYqf5n4fYUwjWJnJ9ueDwP4ZLMlxDqkrum5alANZmmzrP1aNa',NULL,'2026-09-29 22:28:15','2026-09-29 22:28:15'),(38,'Grady Porter','heweticev@mailinator.com',NULL,NULL,'Asia/Dhaka','en','light',1,0,1,NULL,'$2y$12$vunossvnz3MSp.xkHJ2MQeRvYWhzS3MHXiLxqnh/sYpXlAZn0VSBK',NULL,'2026-10-03 02:41:19','2026-10-03 02:41:19');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ifl_payroll'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-03 16:20:13
