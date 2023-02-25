-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2023 at 11:39 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `engagement_logs` (
  `eid` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` varchar(50) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `engagement_time` int(5) NOT NULL DEFAULT 0,
  `last_visited_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE `visitor_logs` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` varchar(50) NOT NULL DEFAULT '000',
  `page_url` varchar(255) DEFAULT NULL,
  `referrer_url` varchar(255) DEFAULT NULL,
  `user_ip_address` varchar(50) DEFAULT NULL,
  `user_geo_location` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `device` varchar(10) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`vid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `retantion_logs` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` varchar(50) NOT NULL DEFAULT current_timestamp(),
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

