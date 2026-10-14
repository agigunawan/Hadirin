-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: gateway01.ap-southeast-1.prod.aws.tidbcloud.com    Database: hadir_in
-- ------------------------------------------------------
-- Server version	8.0.11-TiDB-v8.5.3-serverless

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
-- Table structure for table `app_configs`
--

DROP TABLE IF EXISTS `app_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(50) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `config_value` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `app_configs_config_key_unique` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci AUTO_INCREMENT=30001;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_configs`
--

LOCK TABLES `app_configs` WRITE;
/*!40000 ALTER TABLE `app_configs` DISABLE KEYS */;
INSERT INTO `app_configs` VALUES (1,'INFO_PIMPINAN','{\"name\": \"dr. Resi Arisandi, M.H.\", \"nip\": \"197505122002121002\", \"position\": \"Kepala Balai Besar Kekarantinaan Kesehatan Tanjung Priok\", \"signature_url\": null}','2026-10-14 10:42:04','2026-10-14 10:42:04');
/*!40000 ALTER TABLE `app_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendees`
--

DROP TABLE IF EXISTS `attendees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendees` (
  `id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `event_id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `agency` varchar(150) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT '1',
  `gender` varchar(20) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `nip_or_id` varchar(20) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bank_name` varchar(50) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bank_account_no` varchar(30) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `bank_account_name` varchar(100) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `coordinates` varchar(100) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `signature_data` mediumtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `attendees_event_id_foreign` (`event_id`),
  UNIQUE KEY `uq_attendee_event_nip` (`event_id`,`nip_or_id`),
  KEY `idx_attendees_event_created` (`event_id`,`created_at`),
  CONSTRAINT `attendees_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendees`
--

LOCK TABLES `attendees` WRITE;
/*!40000 ALTER TABLE `attendees` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(50) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Guest',
  `action` varchar(50) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `detail` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_audit_logs_time_action` (`timestamp`,`action`),
  KEY `idx_audit_logs_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  `key` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `attendee_id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rating` varchar(20) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `feedback` text COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `evaluations_attendee_id_foreign` (`attendee_id`),
  UNIQUE KEY `evaluations_attendee_id_unique` (`attendee_id`),
  CONSTRAINT `evaluations_attendee_id_foreign` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluations`
--

LOCK TABLES `evaluations` WRITE;
/*!40000 ALTER TABLE `evaluations` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `room_id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `event_date` date NOT NULL,
  `start_minutes` smallint unsigned NOT NULL,
  `end_minutes` smallint unsigned NOT NULL,
  `work_unit` varchar(100) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'PENDING',
  `rejection_note` text COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `qr_token` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `config_fields` json DEFAULT NULL,
  `pimpinan_name` varchar(150) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `pimpinan_nip` varchar(50) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `pimpinan_position` varchar(150) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `pimpinan_date_text` varchar(150) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `pimpinan_signature` mediumtext COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `events_room_id_foreign` (`room_id`),
  KEY `events_created_by_foreign` (`created_by`),
  KEY `idx_events_collision` (`room_id`,`event_date`,`start_minutes`,`end_minutes`),
  KEY `idx_events_date_status` (`event_date`,`status`),
  UNIQUE KEY `events_qr_token_unique` (`qr_token`),
  CONSTRAINT `events_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES ('a2f7f7cf-86d3-42eb-90aa-2253163a3219','Rapat PNBP','a2fa1245-6165-41fc-a1c1-7b01d83053c8','2026-10-15',540,720,'Tim Kerja Kekarantinaan & Surveilans Epidemiologi','APPROVED',NULL,'5393ca2d-7aaf-4394-b555-1d115e066fd4',NULL,NULL,NULL,NULL,NULL,NULL,'a2fa1249-a516-4292-a254-624c8759dc97','2026-10-14 10:42:04','2026-10-14 10:42:04'),('a2fa124b-773b-4712-94e3-f66805d48718','Rapat Koordinasi Kesiapsiagaan Kekarantinaan Kesehatan Pelabuhan','a2fa1245-6165-41fc-a1c1-7b01d83053c8','2026-10-14',540,720,'Tim Kerja Kekarantinaan & Surveilans Epidemiologi','APPROVED',NULL,'bbkk-priok-demo-2026',NULL,NULL,NULL,NULL,NULL,NULL,'a2fa1249-a516-4292-a254-624c8759dc97','2026-10-14 10:42:04','2026-10-14 10:42:04');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `connection` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `queue` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  `id` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  `queue` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
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
  `migration` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci AUTO_INCREMENT=127294;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_13_000002_create_rooms_table',1),(5,'2026_10_13_000003_create_events_table',1),(6,'2026_10_13_000004_create_attendees_table',1),(7,'2026_10_13_000005_create_evaluations_table',1),(8,'2026_10_13_000006_create_audit_logs_table',1),(9,'2026_10_13_000007_create_app_configs_table',1),(10,'2026_10_13_000008_make_attendees_fields_nullable',1),(11,'2026_10_13_000009_add_pimpinan_fields_to_events_table',1),(12,'2026_10_13_000010_add_pimpinan_date_text_to_events_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `capacity` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `rooms_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES ('a2fa1245-6165-41fc-a1c1-7b01d83053c8','Ruang Rapat Utama (Lantai 2)',40,1,'2026-10-14 10:42:00','2026-10-14 10:42:00'),('a2fa1245-a8b5-4b38-9eba-5b319388afa0','Ruang Rapat Malahayati',25,1,'2026-10-14 10:42:00','2026-10-14 10:42:00'),('a2fa1245-e9c3-4614-9bca-d64a1a589121','Ruang Rapat Samudera',20,1,'2026-10-14 10:42:00','2026-10-14 10:42:00'),('a2fa1247-49e2-40ed-9c65-dcbcdd1a0000','Aula Serbaguna Gedung BBKK Priok',120,1,'2026-10-14 10:42:01','2026-10-14 10:42:01'),('a2fa1247-8e7b-4580-8fa8-2135c368984d','Ruang Rapat Tim Kerja Pengendalian Karantina',15,1,'2026-10-14 10:42:02','2026-10-14 10:42:02');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` varchar(30) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'ADMIN_TIM_KERJA',
  `work_unit` varchar(100) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('a2fa1248-472b-4451-9097-9e83588c85c2','superadmin','Super Admin BBKK Priok','superadmin@bbkkpriok.go.id','$2y$12$lce7rZIstbVe79gPUoYHRel4d9e8e9Oyp0kZvDILFbDiZY3chIH66','SUPER_ADMIN','Tata Usaha / Pengelola Sistem',NULL,'2026-10-14 10:42:02','2026-10-14 10:42:02'),('a2fa1249-0a42-4efe-9ea2-16d892681308','admin','Administrator BBKK Priok','admin@bbkkpriok.go.id','$2y$12$/WiDS6Kwj.n3usZKrxKqAu1TJvryEg7lJiwp.zUfuk1wj1TMgGYTm','SUPER_ADMIN','Tata Usaha / Pengelola Sistem',NULL,'2026-10-14 10:42:03','2026-10-14 10:42:03'),('a2fa1249-a516-4292-a254-624c8759dc97','timker1','Admin Tim Kerja Karantina & Surveilans','timker1@bbkkpriok.go.id','$2y$12$j1jSXOiSxfGxNrMxPQchsurzc/FltfvxtRpXnaKjTIYbYKmuPdO4G','ADMIN_TIM_KERJA','Tim Kerja Kekarantinaan & Surveilans Epidemiologi',NULL,'2026-10-14 10:42:03','2026-10-14 10:42:03'),('a2fa124a-89b9-4aa4-a20d-2f40f34cd274','timker2','Admin Tim Kerja Pengendalian Risiko Lingkungan','timker2@bbkkpriok.go.id','$2y$12$1HtM0k9tZSFSKp7Absh3aOOHt7Vr3LFgJWphmcFXg4IlBU1VGfHD2','ADMIN_TIM_KERJA','Tim Kerja Pengendalian Risiko Lingkungan',NULL,'2026-10-14 10:42:03','2026-10-14 10:42:03');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-14 10:44:06
