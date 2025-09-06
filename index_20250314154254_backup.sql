-- MySQL dump 10.13  Distrib 5.7.26, for Win64 (x86_64)
--
-- Host: localhost    Database: index
-- ------------------------------------------------------
-- Server version	5.7.26

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_time` datetime NOT NULL,
  `inspector` varchar(50) NOT NULL,
  `inspected_unit` varchar(100) NOT NULL,
  `inspection_details` text NOT NULL,
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspections`
--

LOCK TABLES `inspections` WRITE;
/*!40000 ALTER TABLE `inspections` DISABLE KEYS */;
INSERT INTO `inspections` VALUES (1,'2025-03-13 11:31:00','陈陈陈','水厂','1.检查捕鱼器具是否完善\r\n2.检查救生装具的完整性\r\n3.检查渔民是否经过岗前培训','立即整改','2025-03-13 03:31:22','2025-03-13 11:33:57'),(2,'2025-03-13 11:32:00','赵赵赵','测试','1.检查捕鱼器具是否完善\r\n2.检查救生装具的完整性\r\n3.检查渔民是否经过岗前培训','立即整改','2025-03-13 03:33:03','2025-03-13 11:33:37'),(3,'2025-03-13 11:39:00','王王王','电厂','1.检查捕鱼器具是否完善\r\n2.检查救生装具的完整性\r\n3.检查渔民是否经过岗前培训','立即整改','2025-03-13 03:40:15','2025-03-13 11:33:14'),(4,'2025-03-13 19:23:00','王日天','渔场','1.检查捕鱼器具是否完善\r\n2.检查救生装具的完整性\r\n3.检查渔民是否经过岗前培训','立即整改','2025-03-13 11:25:00','2025-03-13 11:25:00'),(5,'2025-03-14 15:31:00','粟粟粟','垃圾厂','1.XXXXXXXXXXXXXXXXXXXXXXXXX\r\n2.XXXXXXXXXXXXXXXXXXXXXXXXXX\r\n3.XXXXXXXXXXXXXXXXXXXXXXXXXXXXX\r\n4.XXXXXXXXXXXXXXXXXXXXXXXXXXX','撒撒大声地','2025-03-14 07:31:51','2025-03-14 07:31:51'),(6,'2025-03-14 15:31:00','测试','垃圾厂','实打实大撒大声地','手打','2025-03-14 07:32:18','2025-03-14 07:32:18'),(7,'2025-03-14 15:40:00','王大海','填埋场','1.填埋场垃圾过多 臭味熏天 需要立即整改\r\n2.对周边的生态造成了破坏 \r\n3.对周边百姓居住造成了影响','马上整改','2025-03-14 07:41:39','2025-03-14 07:41:39');
/*!40000 ALTER TABLE `inspections` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-14 15:42:55
