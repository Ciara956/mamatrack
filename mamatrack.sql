-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2026 at 04:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mamatrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appt_date` date NOT NULL,
  `appt_time` time DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('scheduled','attended','missed','rescheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  `token` varchar(255) DEFAULT NULL,
  `rescheduled_from` int(11) DEFAULT NULL,
  `missed_at` datetime DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appt_date`, `appt_time`, `reason`, `status`, `token`, `rescheduled_from`, `missed_at`, `reminder_sent`, `created_by`, `created_at`) VALUES
(1, 1, 2, '2026-11-17', '09:00:00', 'ANC visit', 'missed', '9948f267e3aa79f2056fcd72592c9bb8', NULL, '2026-11-20 20:54:59', 0, NULL, '2026-11-20 20:52:35'),
(2, 5, 2, '2026-11-19', '09:00:00', 'ANC visit', 'missed', 'e08faf4e73a77fc2d3255d2aee91fe83', NULL, '2026-11-20 20:54:59', 0, NULL, '2026-11-20 20:52:35'),
(3, 2, 2, '2026-11-22', '09:00:00', 'ANC visit', 'scheduled', '4ae1399bb70fec548e8956333857e0ec', NULL, NULL, 1, NULL, '2026-11-20 20:52:35'),
(4, 6, 5, '2026-11-20', '09:00:00', 'ANC visit', 'missed', 'fb821a9599f3b7106659b2ef342df381', NULL, '2026-11-21 00:40:04', 1, NULL, '2026-11-20 20:52:35'),
(5, 4, 3, '2026-10-21', '09:00:00', 'ANC visit', 'rescheduled', '78da5502b6d2d05089f576a1247623f2', NULL, '2026-11-20 20:52:35', 0, NULL, '2026-11-20 20:52:35'),
(6, 7, 5, '2026-11-20', '23:57:00', 'ANC visit', 'missed', 'ca36843cf8dcc63c5a13807d166e4da0', NULL, '2026-11-21 00:33:26', 0, 5, '2026-11-20 21:55:54'),
(7, 4, 3, '2026-11-21', '08:00:00', 'ANC visit', 'scheduled', 'afb5b6a308436b71e1bdfd4d775e273e', 5, NULL, 1, 5, '2026-11-20 22:28:23'),
(8, 8, 5, '2026-11-25', '09:00:00', 'Routine ANC follow-up', 'attended', '33bcc96ceed8124c5374219705bae32b', NULL, NULL, 0, 5, '2026-11-21 02:40:51'),
(9, 3, 3, '2026-12-05', '09:00:00', 'Routine ANC follow-up', 'scheduled', '0dd2b776d0451f05a6ed4c448a8d129a', NULL, NULL, 0, 5, '2026-11-21 02:48:23'),
(10, 8, 5, '2026-12-05', '09:00:00', 'Routine ANC follow-up', 'scheduled', '9ccbdc4e1db573c73f9337631d761fbc', NULL, NULL, 0, 5, '2026-11-21 03:36:27');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(150) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `user_name`, `action`, `detail`, `ip`, `created_at`) VALUES
(1, 1, 'System Administrator', 'login', 'admin@mamatrack.local', '::1', '2026-11-20 20:54:59'),
(2, 1, 'System Administrator', 'user_created', 'legacyciara389@gmail.com', '::1', '2026-11-20 21:51:01'),
(3, 1, 'System Administrator', 'logout', '', '::1', '2026-11-20 21:51:54'),
(4, 5, 'Ciara Nansubuga', 'login', 'legacyciara389@gmail.com', '::1', '2026-11-20 21:53:00'),
(5, 5, 'Ciara Nansubuga', 'patient_registered', '#7', '::1', '2026-11-20 21:55:10'),
(6, 5, 'Ciara Nansubuga', 'patient_viewed', '#7', '::1', '2026-11-20 21:55:10'),
(7, 5, 'Ciara Nansubuga', 'appointment_created', '#6 patient #7', '::1', '2026-11-20 21:55:54'),
(8, 5, 'Ciara Nansubuga', 'patient_viewed', '#7', '::1', '2026-11-20 21:55:54'),
(9, 5, 'Ciara Nansubuga', 'patient_viewed', '#7', '::1', '2026-11-20 21:59:48'),
(10, 5, 'Ciara Nansubuga', 'patient_viewed', '#7', '::1', '2026-11-20 22:00:52'),
(11, 5, 'Ciara Nansubuga', 'patient_viewed', '#7', '::1', '2026-11-20 22:01:16'),
(12, 5, 'Ciara Nansubuga', 'patient_viewed', '#7', '::1', '2026-11-20 22:02:17'),
(13, 5, 'Ciara Nansubuga', 'logout', '', '::1', '2026-11-20 22:03:28'),
(14, 5, 'Ciara Nansubuga', 'login', 'legacyciara389@gmail.com', '::1', '2026-11-20 22:03:38'),
(15, 5, 'Ciara Nansubuga', 'appointment_rescheduled', '#5 -> #7', '::1', '2026-11-20 22:28:23'),
(16, 5, 'Ciara Nansubuga', 'patient_viewed', '#4', '::1', '2026-11-20 22:28:23'),
(17, 5, 'Ciara Nansubuga', 'manual_check', 'missed=0 reminders=1', '::1', '2026-11-20 22:29:41'),
(18, 5, 'Ciara Nansubuga', 'manual_check', 'missed=1 reminders=1', '::1', '2026-11-21 00:33:26'),
(19, 5, 'Ciara Nansubuga', 'manual_check', 'missed=1 reminders=0', '::1', '2026-11-21 00:40:04'),
(20, 5, 'Ciara Nansubuga', 'patient_registered', '#8', '::1', '2026-11-21 02:01:28'),
(21, 5, 'Ciara Nansubuga', 'patient_viewed', '#8', '::1', '2026-11-21 02:01:28'),
(22, 5, 'Ciara Nansubuga', 'visit_recorded', 'patient #8 wk 34 risk low', '::1', '2026-11-21 02:40:51'),
(23, 5, 'Ciara Nansubuga', 'patient_viewed', '#8', '::1', '2026-11-21 02:40:51'),
(24, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 02:41:56'),
(25, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 02:46:25'),
(26, 5, 'Ciara Nansubuga', 'visit_recorded', 'patient #3 wk 28 risk high', '::1', '2026-11-21 02:48:23'),
(27, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 02:48:23'),
(28, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 02:54:04'),
(29, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 02:54:08'),
(30, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 02:54:09'),
(31, 5, 'Ciara Nansubuga', 'patient_viewed', '#3', '::1', '2026-11-21 03:04:15'),
(32, 5, 'Ciara Nansubuga', 'patient_viewed', '#4', '::1', '2026-11-21 03:16:44'),
(33, 5, 'Ciara Nansubuga', 'patient_viewed', '#8', '::1', '2026-11-21 03:30:15'),
(34, 5, 'Ciara Nansubuga', 'visit_recorded', 'patient #8 wk 34 risk high', '::1', '2026-11-21 03:36:27'),
(35, 5, 'Ciara Nansubuga', 'patient_viewed', '#8', '::1', '2026-11-21 03:36:27'),
(36, 5, 'Ciara Nansubuga', 'patient_viewed', '#1', '::1', '2026-11-21 06:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `recipient` varchar(150) DEFAULT NULL,
  `channel` varchar(50) DEFAULT NULL,
  `to_addr` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `appointment_id`, `recipient`, `channel`, `to_addr`, `message`, `status`, `response`, `created_at`) VALUES
(1, 1, 'patient:Aisha Namusoke', 'sms', '0772100001', 'Dear Aisha Namusoke, you missed your ANC visit on 17 Nov 2026. Please contact us to reschedule.', 'queued_test', NULL, '2026-11-20 20:54:59'),
(2, 2, 'patient:Prossy Nantume', 'sms', '0772100005', 'Dear Prossy Nantume, you missed your ANC visit on 19 Nov 2026. Please contact us to reschedule.', 'queued_test', NULL, '2026-11-20 20:54:59'),
(3, 4, 'patient:Betty Kyomuhendo', 'sms', '0772100006', 'Reminder: ANC visit tomorrow at 09:00:00.', 'queued_test', NULL, '2026-11-20 20:54:59'),
(4, 7, 'doctor:Dr. Peter Okello', 'sms', '+256700000002', 'MamaTrack: Joan Akello (ANC-2026-0004) ANC visit rescheduled from 21 Oct 2026 to 21 Nov 2026 08:00 by staff (Ciara Nansubuga).', 'queued_test', NULL, '2026-11-20 22:28:23'),
(5, 7, 'patient:Joan Akello', 'sms', '0772100004', 'Dear Joan, your antenatal visit is now on 21 Nov 2026 at 08:00. See you then!', 'queued_test', NULL, '2026-11-20 22:28:23'),
(6, 7, 'patient:Joan Akello', 'sms', '0772100004', 'Reminder: ANC visit tomorrow at 08:00:00.', 'queued_test', NULL, '2026-11-20 22:29:41'),
(7, 6, 'patient:Elena Matovu', 'sms', '+256753142880', 'Dear Elena Matovu, you missed your ANC visit on 20 Nov 2026. Please contact us to reschedule.', 'queued_test', NULL, '2026-11-21 00:33:26'),
(8, 3, 'patient:Mary Atim', 'sms', '0772100002', 'Reminder: ANC visit tomorrow at 09:00:00.', 'queued_test', NULL, '2026-11-21 00:33:26'),
(9, 4, 'patient:Betty Kyomuhendo', 'sms', '0753142880', 'Dear Betty Kyomuhendo, you missed your ANC visit on 20 Nov 2026. Please contact us to reschedule.', 'queued_test', NULL, '2026-11-21 00:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `mrn` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `dob` date NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `village` varchar(150) DEFAULT NULL,
  `district` varchar(150) DEFAULT NULL,
  `next_of_kin` varchar(150) DEFAULT NULL,
  `nok_phone` varchar(30) DEFAULT NULL,
  `lmp` date NOT NULL,
  `edd` date DEFAULT NULL,
  `gravida` int(11) NOT NULL DEFAULT 1,
  `parity` int(11) NOT NULL DEFAULT 0,
  `hiv_status` varchar(20) NOT NULL DEFAULT 'unknown',
  `history` text DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `risk_level` varchar(20) NOT NULL DEFAULT 'unassessed',
  `risk_score` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `mrn`, `name`, `dob`, `phone`, `email`, `village`, `district`, `next_of_kin`, `nok_phone`, `lmp`, `edd`, `gravida`, `parity`, `hiv_status`, `history`, `doctor_id`, `risk_level`, `risk_score`, `created_by`, `created_at`) VALUES
(1, 'ANC-2026-0001', 'Aisha Namusoke', '2002-08-12', '0772100001', NULL, 'Nansana', 'Wakiso', NULL, NULL, '2026-04-22', '2027-01-27', 2, 1, 'negative', NULL, 2, 'high', 5, NULL, '2026-11-20 20:52:35'),
(2, 'ANC-2026-0002', 'Mary Atim', '1997-08-12', '0772100002', NULL, 'Nansana', 'Wakiso', NULL, NULL, '2026-05-20', '2027-02-24', 2, 1, 'negative', NULL, 2, 'low', 0, NULL, '2026-11-20 20:52:35'),
(3, 'ANC-2026-0003', 'Esther Nabirye', '2009-08-12', '0772100003', NULL, 'Nansana', 'Wakiso', NULL, NULL, '2026-05-06', '2027-02-10', 1, 0, 'negative', NULL, 3, 'high', 8, NULL, '2026-11-20 20:52:35'),
(4, 'ANC-2026-0004', 'Joan Akello', '1990-08-12', '0772100004', NULL, 'Nansana', 'Wakiso', NULL, NULL, '2026-04-08', '2027-01-13', 5, 4, 'positive', NULL, 3, 'moderate', 4, NULL, '2026-11-20 20:52:35'),
(5, 'ANC-2026-0005', 'Prossy Nantume', '2000-08-12', '0772100005', NULL, 'Nansana', 'Wakiso', NULL, NULL, '2026-06-17', '2027-03-24', 1, 0, 'negative', NULL, 2, 'low', 0, NULL, '2026-11-20 20:52:35'),
(6, 'ANC-2026-0006', 'Betty Kyomuhendo', '2004-08-12', '0753142880', NULL, 'Nansana', 'Wakiso', NULL, NULL, '2026-09-16', '2027-06-23', 1, 0, 'unknown', NULL, 3, 'low', 0, NULL, '2026-11-20 20:52:35'),
(7, 'ANC-2026-0007', 'Elena Matovu', '2002-03-18', '+256753142880', 'cialegacy@outlook.com', 'Ntinda', 'Kampala', 'Mayanja Dave', '+256756298047', '2026-09-12', '2027-06-19', 1, 0, 'negative', '', 5, 'unassessed', 0, 5, '2026-11-20 21:55:10'),
(8, 'ANC-2026-0008', 'Aisha Nalumu', '2000-02-29', '+256743955016', 'aisha@gmail.com', 'Buziga', 'Kampala', 'Kinyatte Paul', '+256758798047', '2026-03-23', '2026-12-28', 2, 0, 'positive', '', 5, 'high', 5, 5, '2026-11-21 02:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('admin','doctor','nurse') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change` tinyint(1) NOT NULL DEFAULT 0,
  `failed_logins` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `password_hash`, `active`, `must_change`, `failed_logins`, `locked_until`, `created_at`) VALUES
(1, 'System Administrator', 'admin@mamatrack.local', NULL, 'admin', '$2b$10$MDxivFamM90.zo8BFVKVPeC7H2bKAlVk8WoLjt7yM0hgduOfvvjzW', 1, 1, 0, NULL, '2026-11-20 20:30:40'),
(2, 'Dr. Sarah Nakato', 'sarah@mamatrack.local', '+256700000001', 'doctor', '$2y$10$qJcdDVy34WwbHL9nRsSYluLiLRSQ6CBzGtq8dc1HBJhLycLPjNJY2', 1, 0, 0, NULL, '2026-11-20 20:52:35'),
(3, 'Dr. Peter Okello', 'peter@mamatrack.local', '+256700000002', 'doctor', '$2y$10$qJcdDVy34WwbHL9nRsSYluLiLRSQ6CBzGtq8dc1HBJhLycLPjNJY2', 1, 0, 0, NULL, '2026-11-20 20:52:35'),
(4, 'Grace Auma (Midwife)', 'grace@mamatrack.local', '+256700000003', 'nurse', '$2y$10$qJcdDVy34WwbHL9nRsSYluLiLRSQ6CBzGtq8dc1HBJhLycLPjNJY2', 1, 0, 0, NULL, '2026-11-20 20:52:35'),
(5, 'Ciara Nansubuga', 'legacyciara389@gmail.com', '+256743905016', 'doctor', '$2y$10$ZSEu/wC11.H0DJ3MRw./mOY6kz6RfDgjHRK.Uj5A/WRMwrkzvQMY2', 1, 1, 0, NULL, '2026-11-20 21:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `gest_weeks` int(11) DEFAULT NULL,
  `systolic` int(11) DEFAULT NULL,
  `diastolic` int(11) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `weight` decimal(6,2) DEFAULT NULL,
  `temperature` decimal(4,2) DEFAULT NULL,
  `fundal_height` decimal(5,2) DEFAULT NULL,
  `fetal_hr` int(11) DEFAULT NULL,
  `hemoglobin` decimal(5,2) DEFAULT NULL,
  `urine_protein` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visits`
--

INSERT INTO `visits` (`id`, `patient_id`, `appointment_id`, `visit_date`, `gest_weeks`, `systolic`, `diastolic`, `heart_rate`, `weight`, `temperature`, `fundal_height`, `fetal_hr`, `hemoglobin`, `urine_protein`, `notes`, `recorded_by`, `created_at`) VALUES
(1, 1, NULL, '2026-07-15', 12, 112, 70, 78, 58.00, 36.70, NULL, NULL, 12.10, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(2, 1, NULL, '2026-08-12', 16, 116, 72, 80, 60.00, 36.70, NULL, NULL, 11.80, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(3, 1, NULL, '2026-09-09', 20, 122, 78, 82, 62.50, 36.70, 20.00, 145, 11.60, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(4, 1, NULL, '2026-10-07', 24, 132, 86, 84, 65.00, 36.70, 24.00, 142, 11.40, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(5, 1, NULL, '2026-11-04', 28, 142, 92, 88, 69.50, 36.70, 28.00, 140, 11.20, '1+', NULL, NULL, '2026-11-20 20:52:35'),
(6, 2, NULL, '2026-07-29', 10, 108, 68, 76, 60.00, 36.70, NULL, NULL, 12.50, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(7, 2, NULL, '2026-08-26', 14, 110, 70, 76, 61.00, 36.70, NULL, NULL, 12.40, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(8, 2, NULL, '2026-09-23', 18, 110, 70, 78, 62.50, 36.70, 18.00, 150, 12.20, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(9, 2, NULL, '2026-10-21', 22, 112, 72, 78, 64.00, 36.70, 22.00, 148, 12.10, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(10, 2, NULL, '2026-11-18', 26, 112, 72, 80, 66.00, 36.70, 26.00, 146, 12.00, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(11, 3, NULL, '2026-08-26', 16, 106, 66, 84, 50.00, 36.70, NULL, NULL, 11.50, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(12, 3, NULL, '2026-10-07', 22, 108, 68, 88, 53.00, 36.70, 22.00, 150, 10.80, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(13, 3, NULL, '2026-11-18', 28, 118, 76, 96, 59.50, 36.70, 28.00, 152, 9.60, 'trace', NULL, NULL, '2026-11-20 20:52:35'),
(14, 4, NULL, '2026-08-26', 20, 118, 76, 82, 70.00, 36.70, 20.00, 144, 10.90, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(15, 4, NULL, '2026-10-07', 26, 120, 78, 84, 72.00, 36.70, 26.00, 142, 10.50, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(16, 5, NULL, '2026-09-23', 14, 110, 70, 76, 55.00, 36.70, NULL, NULL, 12.00, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(17, 5, NULL, '2026-10-21', 18, 112, 72, 78, 57.00, 36.70, 18.00, 148, 11.90, 'neg', NULL, NULL, '2026-11-20 20:52:35'),
(18, 8, NULL, '2026-11-21', 34, 115, 72, 76, 85.00, 36.80, 3.00, 145, 12.00, 'neg', '', 5, '2026-11-21 02:40:51'),
(19, 3, NULL, '2026-11-21', 28, 142, 92, 98, 80.00, 36.70, 3.00, 138, 10.50, '1+', '', 5, '2026-11-21 02:48:23'),
(20, 8, 8, '2026-11-21', 34, 115, 72, 76, 85.00, 36.80, 1.00, 145, 9.20, '1+', '', 5, '2026-11-21 03:36:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_appointments_patient` (`patient_id`),
  ADD KEY `fk_appointments_doctor` (`doctor_id`),
  ADD KEY `fk_appointments_creator` (`created_by`),
  ADD KEY `ix_appt_status` (`status`,`appt_date`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_appointment` (`appointment_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mrn` (`mrn`),
  ADD KEY `fk_patients_creator` (`created_by`),
  ADD KEY `ix_patients_doctor` (`doctor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visits_appointment` (`appointment_id`),
  ADD KEY `fk_visits_recorder` (`recorded_by`),
  ADD KEY `ix_visits_patient` (`patient_id`,`visit_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_patients_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `fk_visits_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_visits_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_visits_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
