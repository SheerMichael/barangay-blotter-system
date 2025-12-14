-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: Blotter_System
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blotter_complainants`
--

DROP TABLE IF EXISTS `blotter_complainants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotter_complainants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blotter_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_blotter_complainant` (`blotter_id`,`resident_id`),
  KEY `resident_id` (`resident_id`),
  CONSTRAINT `blotter_complainants_ibfk_1` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blotter_complainants_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotter_complainants`
--

LOCK TABLES `blotter_complainants` WRITE;
/*!40000 ALTER TABLE `blotter_complainants` DISABLE KEYS */;
INSERT INTO `blotter_complainants` VALUES (6,9,7),(11,11,6),(10,11,7),(12,12,7),(18,13,5),(17,13,7),(16,13,9),(19,14,27),(20,15,32),(21,16,30),(22,17,32),(23,18,35),(24,19,33),(25,20,29),(26,21,28),(27,22,34),(28,23,33),(29,24,29),(30,25,28),(31,26,29),(33,28,32),(34,29,26),(35,30,38),(36,31,38),(37,32,35),(38,33,25),(39,34,34),(40,35,26),(41,36,30),(42,37,27),(43,38,29),(44,39,31),(45,40,26),(46,41,36),(47,42,32),(48,43,25),(49,44,34),(50,45,27),(51,46,26),(52,47,36),(53,48,27),(54,49,28),(55,50,28),(56,51,37),(58,53,32),(59,54,33),(60,55,35),(61,56,25),(62,57,36),(63,58,33),(64,59,38),(65,60,38),(66,61,29);
/*!40000 ALTER TABLE `blotter_complainants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotter_respondents`
--

DROP TABLE IF EXISTS `blotter_respondents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotter_respondents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blotter_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_blotter_respondent` (`blotter_id`,`resident_id`),
  KEY `resident_id` (`resident_id`),
  CONSTRAINT `blotter_respondents_ibfk_1` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blotter_respondents_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotter_respondents`
--

LOCK TABLES `blotter_respondents` WRITE;
/*!40000 ALTER TABLE `blotter_respondents` DISABLE KEYS */;
INSERT INTO `blotter_respondents` VALUES (5,9,2),(7,10,7),(9,11,3),(8,11,5),(10,12,6),(13,13,3),(14,13,6),(15,14,29),(16,15,36),(17,16,26),(18,17,35),(19,18,28),(20,19,34),(21,20,35),(22,21,28),(23,22,35),(24,23,31),(25,24,29),(27,26,25),(28,27,38),(29,28,37),(30,29,28),(31,30,35),(32,31,25),(33,32,27),(34,33,25),(35,34,33),(36,35,37),(37,36,33),(38,37,32),(39,38,31),(40,39,36),(41,40,38),(42,41,38),(43,42,37),(44,43,35),(45,44,29),(46,45,37),(47,46,29),(48,47,31),(49,48,38),(50,49,27),(51,50,33),(54,53,35),(56,55,27),(57,56,37),(58,57,27),(59,58,26),(60,59,31),(61,60,26),(62,61,33);
/*!40000 ALTER TABLE `blotter_respondents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotters`
--

DROP TABLE IF EXISTS `blotters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_no` varchar(50) DEFAULT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time DEFAULT NULL,
  `incident_location` varchar(255) NOT NULL,
  `incident_type` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `status` enum('Pending','Scheduled','Resolved','Endorsed to Police') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_no` (`case_no`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotters`
--

LOCK TABLES `blotters` WRITE;
/*!40000 ALTER TABLE `blotters` DISABLE KEYS */;
INSERT INTO `blotters` VALUES (9,'CASE-2025-00009','2025-10-31',NULL,'ayala','theft','djgsdbadbauchdauodasd','Pending','','2025-10-27 05:51:39','2025-10-27 12:51:39'),(10,'CASE-2025-00010','2025-10-21',NULL,'recodo','violence','dgggggggaagagagagga','Scheduled','','2025-10-27 06:27:27','2025-10-27 13:27:45'),(11,'CASE-2025-00011','2025-10-15',NULL,'wmsu','tariffs','ihornfouihdic;asjd;aocdjopcd','Pending','','2025-10-27 06:41:43','2025-10-27 13:41:43'),(12,'CASE-2025-00012','2025-10-09',NULL,'recodo','theft','ninakaw minifan ko','Scheduled','','2025-10-27 20:38:01','2025-10-28 03:38:01'),(13,'CASE-2025-00013','2025-10-08',NULL,'ayala','amputation','pinutol daliri namin/ attempted murder','Endorsed to Police','','2025-10-27 20:40:41','2025-10-28 03:40:55'),(14,'CASE-2024-00014','2024-12-03','11:17:00','Purok 5','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2024-12-03.','Endorsed to Police','Additional remarks for this case.','2024-12-03 03:17:00','2025-11-10 10:01:10'),(15,'CASE-2024-00015','2024-11-22','14:42:00','Purok 2','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2024-11-22.','Endorsed to Police','Additional remarks for this case.','2024-11-22 06:42:00','2025-11-10 10:01:10'),(16,'CASE-2024-00016','2024-11-16','13:46:00','Main Road','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2024-11-16.','Resolved','','2024-11-16 05:46:00','2025-11-10 10:01:10'),(17,'CASE-2024-00017','2024-12-08','07:55:00','Purok 6','Assault','Sample incident details for testing purposes. This is a simulated case filed on 2024-12-08.','Resolved','Additional remarks for this case.','2024-12-07 23:55:00','2025-11-10 10:01:10'),(18,'CASE-2024-00018','2024-11-22','16:32:00','Purok 5','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2024-11-22.','Pending','','2024-11-22 08:32:00','2025-11-10 10:01:10'),(19,'CASE-2024-00019','2024-11-30','21:05:00','Basketball Court','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2024-11-30.','Scheduled','','2024-11-30 13:05:00','2025-11-10 10:01:10'),(20,'CASE-2024-00020','2024-12-19','09:07:00','Purok 5','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2024-12-19.','Endorsed to Police','','2024-12-19 01:07:00','2025-11-10 10:01:10'),(21,'CASE-2024-00021','2024-12-26','18:06:00','Basketball Court','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2024-12-26.','Pending','','2024-12-26 10:06:00','2025-11-10 10:01:10'),(22,'CASE-2025-00022','2025-01-18','12:46:00','Purok 6','Vandalism','Sample incident details for testing purposes. This is a simulated case filed on 2025-01-18.','Endorsed to Police','Additional remarks for this case.','2025-01-18 04:46:00','2025-11-10 10:01:10'),(23,'CASE-2025-00023','2025-01-29','15:38:00','Purok 4','Theft','Sample incident details for testing purposes. This is a simulated case filed on 2025-01-29.','Endorsed to Police','','2025-01-29 07:38:00','2025-11-10 10:01:10'),(24,'CASE-2025-00024','2025-01-29','07:10:00','Basketball Court','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-01-29.','Pending','Additional remarks for this case.','2025-01-28 23:10:00','2025-11-10 10:01:10'),(25,'CASE-2025-00025','2025-01-15','18:25:00','Barangay Hall','Assault','Sample incident details for testing purposes. This is a simulated case filed on 2025-01-15.','Pending','','2025-01-15 10:25:00','2025-11-10 10:01:10'),(26,'CASE-2025-00026','2025-02-01','19:36:00','Purok 6','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2025-02-01.','Resolved','','2025-02-01 11:36:00','2025-11-10 10:01:10'),(27,'CASE-2025-00027','2025-02-18','17:49:00','Barangay Hall','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2025-02-18.','Resolved','','2025-02-18 09:49:00','2025-11-10 10:01:10'),(28,'CASE-2025-00028','2025-03-01','06:25:00','Barangay Hall','Vandalism','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-01.','Resolved','','2025-02-28 22:25:00','2025-11-10 10:01:10'),(29,'CASE-2025-00029','2025-03-03','15:53:00','Purok 4','Assault','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-03.','Resolved','','2025-03-03 07:53:00','2025-11-10 10:01:10'),(30,'CASE-2025-00030','2025-03-26','22:59:00','Main Road','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-26.','Resolved','Additional remarks for this case.','2025-03-26 14:59:00','2025-11-10 10:01:10'),(31,'CASE-2025-00031','2025-03-15','20:17:00','Barangay Hall','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-15.','Endorsed to Police','','2025-03-15 12:17:00','2025-11-10 10:01:10'),(32,'CASE-2025-00032','2025-04-02','15:33:00','Purok 1','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-04-02.','Scheduled','','2025-04-02 07:33:00','2025-11-10 10:01:10'),(33,'CASE-2025-00033','2025-03-16','21:18:00','Purok 5','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-16.','Endorsed to Police','','2025-03-16 13:18:00','2025-11-10 10:01:10'),(34,'CASE-2025-00034','2025-03-18','12:28:00','Purok 3','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-18.','Pending','Additional remarks for this case.','2025-03-18 04:28:00','2025-11-10 10:01:10'),(35,'CASE-2025-00035','2025-03-18','11:03:00','Purok 5','Theft','Sample incident details for testing purposes. This is a simulated case filed on 2025-03-18.','Endorsed to Police','Additional remarks for this case.','2025-03-18 03:03:00','2025-11-10 10:01:10'),(36,'CASE-2025-00036','2025-04-19','16:59:00','Purok 2','Assault','Sample incident details for testing purposes. This is a simulated case filed on 2025-04-19.','Pending','Additional remarks for this case.','2025-04-19 08:59:00','2025-11-10 10:01:10'),(37,'CASE-2025-00037','2025-05-04','15:35:00','Purok 6','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-05-04.','Pending','','2025-05-04 07:35:00','2025-11-10 10:01:10'),(38,'CASE-2025-00038','2025-04-18','14:06:00','Purok 5','Assault','Sample incident details for testing purposes. This is a simulated case filed on 2025-04-18.','Resolved','Additional remarks for this case.','2025-04-18 06:06:00','2025-11-10 10:01:10'),(39,'CASE-2025-00039','2025-05-08','20:58:00','Purok 6','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-05-08.','Resolved','','2025-05-08 12:58:00','2025-11-10 10:01:10'),(40,'CASE-2025-00040','2025-04-23','12:10:00','Purok 4','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-04-23.','Scheduled','','2025-04-23 04:10:00','2025-11-10 10:01:10'),(41,'CASE-2025-00041','2025-04-28','19:02:00','Purok 2','Theft','Sample incident details for testing purposes. This is a simulated case filed on 2025-04-28.','Pending','Additional remarks for this case.','2025-04-28 11:02:00','2025-11-10 10:01:10'),(42,'CASE-2025-00042','2025-05-20','13:00:00','Purok 4','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-05-20.','Scheduled','','2025-05-20 05:00:00','2025-11-10 10:01:10'),(43,'CASE-2025-00043','2025-05-21','19:36:00','Main Road','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-05-21.','Pending','','2025-05-21 11:36:00','2025-11-10 10:01:10'),(44,'CASE-2025-00044','2025-05-23','21:56:00','Barangay Hall','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-05-23.','Pending','Additional remarks for this case.','2025-05-23 13:56:00','2025-11-10 10:01:10'),(45,'CASE-2025-00045','2025-06-21','20:06:00','Purok 3','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2025-06-21.','Endorsed to Police','Additional remarks for this case.','2025-06-21 12:06:00','2025-11-10 10:01:10'),(46,'CASE-2025-00046','2025-07-01','13:17:00','Purok 2','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-07-01.','Pending','Additional remarks for this case.','2025-07-01 05:17:00','2025-11-10 10:01:10'),(47,'CASE-2025-00047','2025-06-12','12:19:00','Purok 4','Vandalism','Sample incident details for testing purposes. This is a simulated case filed on 2025-06-12.','Scheduled','','2025-06-12 04:19:00','2025-11-10 10:01:10'),(48,'CASE-2025-00048','2025-06-20','16:33:00','Purok 6','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-06-20.','Endorsed to Police','Additional remarks for this case.','2025-06-20 08:33:00','2025-11-10 10:01:10'),(49,'CASE-2025-00049','2025-07-14','07:16:00','Purok 5','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2025-07-14.','Resolved','','2025-07-13 23:16:00','2025-11-10 10:01:10'),(50,'CASE-2025-00050','2025-08-10','16:58:00','Purok 6','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-08-10.','Endorsed to Police','','2025-08-10 08:58:00','2025-11-10 10:01:10'),(51,'CASE-2025-00051','2025-08-22','20:40:00','Main Road','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-08-22.','Scheduled','','2025-08-22 12:40:00','2025-11-10 10:01:10'),(52,'CASE-2025-00052','2025-09-06','20:19:00','Barangay Hall','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2025-09-06.','Pending','Additional remarks for this case.','2025-09-06 12:19:00','2025-11-10 10:01:10'),(53,'CASE-2025-00053','2025-10-08','14:33:00','Barangay Hall','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2025-10-08.','Scheduled','Additional remarks for this case.','2025-10-08 06:33:00','2025-11-10 10:01:10'),(54,'CASE-2025-00054','2025-10-01','12:17:00','Barangay Hall','Theft','Sample incident details for testing purposes. This is a simulated case filed on 2025-10-01.','Pending','Additional remarks for this case.','2025-10-01 04:17:00','2025-11-10 10:01:10'),(55,'CASE-2025-00055','2025-09-23','07:37:00','Purok 2','Trespassing','Sample incident details for testing purposes. This is a simulated case filed on 2025-09-23.','Pending','Additional remarks for this case.','2025-09-22 23:37:00','2025-11-10 10:01:10'),(56,'CASE-2025-00056','2025-09-25','20:50:00','Purok 5','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2025-09-25.','Resolved','Additional remarks for this case.','2025-09-25 12:50:00','2025-11-10 10:01:10'),(57,'CASE-2025-00057','2025-10-18','17:19:00','Purok 3','Theft','Sample incident details for testing purposes. This is a simulated case filed on 2025-10-18.','Pending','','2025-10-18 09:19:00','2025-11-10 10:01:10'),(58,'CASE-2025-00058','2025-11-02','06:12:00','Main Road','Harassment','Sample incident details for testing purposes. This is a simulated case filed on 2025-11-02.','Scheduled','Additional remarks for this case.','2025-11-01 22:12:00','2025-11-10 12:35:41'),(59,'CASE-2025-00059','2025-10-24','06:32:00','Purok 1','Noise Complaint','Sample incident details for testing purposes. This is a simulated case filed on 2025-10-24.','Scheduled','Additional remarks for this case.','2025-10-23 22:32:00','2025-11-10 10:01:10'),(60,'CASE-2025-00060','2025-10-24','12:41:00','Purok 2','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-10-24.','Scheduled','','2025-10-24 04:41:00','2025-11-10 10:01:10'),(61,'CASE-2025-00061','2025-11-07','06:54:00','Purok 2','Property Dispute','Sample incident details for testing purposes. This is a simulated case filed on 2025-11-07.','Endorsed to Police','','2025-11-06 22:54:00','2025-11-20 05:12:46');
/*!40000 ALTER TABLE `blotters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `residents`
--

DROP TABLE IF EXISTS `residents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `residents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `house_address` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `residents`
--

LOCK TABLES `residents` WRITE;
/*!40000 ALTER TABLE `residents` DISABLE KEYS */;
INSERT INTO `residents` VALUES (2,'kevin','librero',20,'Male','Ayala','123456','sheerlibrero@gmail.com'),(3,'dasd','dasd',123,'Female','dad','23',NULL),(4,'thin','yuryur',45,'Male','tydtyd','21211254',NULL),(5,'Donald','Trump',69,'Male','America','420',NULL),(6,'sir jaydee','teacher',15,'Male','secret','123',NULL),(7,'sheer','librero',21,'Male','Ayala','1',NULL),(9,'Dave','Libra',21,'Female','ayala','2',NULL),(25,'Juan','Dela Cruz',35,'Male','Purok 1, Barangay Centro','09171234567',NULL),(26,'Maria','Santos',28,'Female','Purok 2, Barangay Centro','09181234568',NULL),(27,'Pedro','Reyes',42,'Male','Purok 3, Barangay Centro','09191234569',NULL),(28,'Ana','Garcia',31,'Female','Purok 1, Barangay Centro','09201234570',NULL),(29,'Jose','Martinez',45,'Male','Purok 4, Barangay Centro','09211234571',NULL),(30,'Carmen','Lopez',38,'Female','Purok 2, Barangay Centro','09221234572',NULL),(31,'Ricardo','Fernandez',29,'Male','Purok 5, Barangay Centro','09231234573',NULL),(32,'Rosa','Gonzalez',33,'Female','Purok 3, Barangay Centro','09241234574',NULL),(33,'Miguel','Rodriguez',41,'Male','Purok 6, Barangay Centro','09251234575',NULL),(34,'Sofia','Hernandez',27,'Female','Purok 4, Barangay Centro','09261234576',NULL),(35,'Antonio','Diaz',36,'Male','Purok 7, Barangay Centro','09271234577',NULL),(36,'Elena','Torres',30,'Female','Purok 5, Barangay Centro','09281234578',NULL),(37,'Carlos','Ramirez',39,'Male','Purok 8, Barangay Centro','09291234579',NULL),(38,'Isabel','Flores',26,'Female','Purok 6, Barangay Centro','09301234580',NULL),(40,'ninoy','ninoy',21,'Male','123','',NULL),(41,'Sheer and Dave','librero',123,'Other','Makati','123456',NULL),(42,'sheer','librerz',22,'Male','ddddd','22132131','sheerlibrero@gmail.com');
/*!40000 ALTER TABLE `residents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'sheer','$2y$10$iqiUNPrYVTtflGqmEIjQLeUrfd7URzodt/Yylf5Ws2AtHww1HMrG.','sheerlibrero@gmail.com','2025-12-14 12:58:42');
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

-- Dump completed on 2025-12-14 21:06:29
