-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: sdb-90.hosting.stackcp.net
-- Generation Time: Oct 23, 2025 at 10:26 AM
-- Server version: 10.11.14-MariaDB-log
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dizoora-353131390a03`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Asset','Liability','Equity','Income','Expense') NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `balance` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `code`, `name`, `type`, `category`, `parent_id`, `opening_balance`, `balance`, `created_at`, `updated_at`) VALUES
(1, 'A100', 'Cash in Hand', 'Asset', 'Current Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(2, 'A101', 'Bank Account', 'Asset', 'Current Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(3, 'A102', 'Accounts Receivable (Customers)', 'Asset', 'Current Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(4, 'A103', 'Advances to Labor', 'Asset', 'Current Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(5, 'A104', 'Inventory - Raw Material', 'Asset', 'Inventory', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(6, 'A105', 'Inventory - Finished Goods', 'Asset', 'Inventory', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(7, 'A106', 'Prepaid Expenses', 'Asset', 'Current Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(8, 'A107', 'Fixed Assets - Machinery', 'Asset', 'Fixed Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(9, 'A108', 'Fixed Assets - Building', 'Asset', 'Fixed Asset', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(10, 'L200', 'Accounts Payable (Suppliers)', 'Liability', 'Current Liability', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(11, 'L201', 'Wages Payable', 'Liability', 'Current Liability', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(12, 'L202', 'Accrued Expenses', 'Liability', 'Current Liability', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(13, 'L203', 'Bank Loan', 'Liability', 'Long-term Liability', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(14, 'E300', 'Owner’s Capital', 'Equity', 'Owner Equity', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(15, 'E301', 'Retained Earnings', 'Equity', 'Owner Equity', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(16, 'E302', 'Opening Balance Equity', 'Equity', 'System Account', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(17, 'I400', 'Sales Revenue', 'Income', 'Operating Income', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(18, 'I401', 'Service Income', 'Income', 'Operating Income', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(19, 'I402', 'Discount Received', 'Income', 'Other Income', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(20, 'E500', 'Purchases', 'Expense', 'Direct Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(21, 'E501', 'Direct Labor Expense', 'Expense', 'Direct Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(22, 'E502', 'Factory Overheads', 'Expense', 'Direct Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(23, 'E503', 'Electricity Expense', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(24, 'E504', 'Rent Expense', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(25, 'E505', 'Salaries & Wages', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(26, 'E506', 'Telephone & Internet Expense', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(27, 'E507', 'Transportation Expense', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(28, 'E508', 'Depreciation Expense', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(29, 'E509', 'Miscellaneous Expense', 'Expense', 'Operating Expense', NULL, 0.00, 0.00, '2025-10-11 05:53:57', '2025-10-11 05:53:57'),
(30, 'SUP-0001', 'Supplier - Leather Right Multan Road ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(31, 'SUP-0002', 'Supplier - Siddiqiui Brothers Gumti Bazar', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(32, 'SUP-0003', 'Supplier - Faysal Heel Maker ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(33, 'SUP-0004', 'Supplier - Fazal PU ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(34, 'SUP-0005', 'Supplier - Nadeem Leather Store ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(35, 'SUP-0006', 'Supplier - Johar Accecsories', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(36, 'SUP-0007', 'Supplier - Winner Solution', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(37, 'SUP-0008', 'Supplier - Brother Packages ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(38, 'SUP-0009', 'Supplier - Raja Billu Rexine Store ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(39, 'SUP-0010', 'Supplier - Qasim Khan Insole Maker ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(40, 'SUP-0011', 'Supplier - Mehboob Material Store ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(41, 'SUP-0012', 'Supplier - Nasir Material Store ', 'Liability', 'Accounts Payable', 10, 0.00, 0.00, '2025-10-16 15:48:54', '2025-10-16 15:48:54'),
(45, 'CUST-0003', 'Customer - Pastel Toes', 'Asset', 'Accounts Receivable', 3, 0.00, 0.00, '2025-10-16 15:50:29', '2025-10-16 15:50:29'),
(46, 'CUST-0004', 'Customer - Ids by Isma', 'Asset', 'Accounts Receivable', 3, 0.00, 0.00, '2025-10-16 15:50:29', '2025-10-16 15:50:29'),
(47, 'CUST-0008', 'Customer - Pastel Toes to Barqraftaar', 'Asset', 'Accounts Receivable', 3, 0.00, 0.00, '2025-10-16 15:50:29', '2025-10-16 15:50:29'),
(48, 'CUST-0009', 'Customer - Ids to Barqraftaar', 'Asset', 'Accounts Receivable', 3, 0.00, 0.00, '2025-10-16 15:50:29', '2025-10-16 15:50:29'),
(49, 'CUST-0010', 'Customer - Ids to Call Courier', 'Asset', 'Accounts Receivable', 3, 0.00, 0.00, '2025-10-16 15:50:29', '2025-10-16 15:50:29'),
(50, 'CUST-0011', 'Customer - Pastel Toes to Call Courier', 'Asset', 'Accounts Receivable', 3, 0.00, 0.00, '2025-10-16 15:50:29', '2025-10-16 15:50:29'),
(52, 'SUP-0013', 'Supplier - Usman Flyers', 'Liability', 'Accounts Payable', 4, 0.00, 0.00, '2025-10-17 14:08:53', '2025-10-17 14:08:53'),
(53, 'SUP-0014', 'Supplier - Nasir Material Store ', 'Liability', 'Accounts Payable', 4, 0.00, 0.00, '2025-10-17 14:10:08', '2025-10-17 14:10:08'),
(54, 'SUP-0015', 'Supplier - Amir Qaudri Karachi ', 'Liability', 'Accounts Payable', 4, 0.00, 0.00, '2025-10-20 06:35:56', '2025-10-20 06:35:56'),
(55, 'COURIER-001', 'TraxCourier', 'Asset', 'Accounts Receivable', NULL, 0.00, 0.00, '2025-10-21 05:58:04', '2025-10-21 05:58:04');

-- --------------------------------------------------------

--
-- Table structure for table `journal_details`
--

CREATE TABLE `journal_details` (
  `id` int(11) NOT NULL,
  `journal_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `journal_details`
--

INSERT INTO `journal_details` (`id`, `journal_id`, `account_id`, `debit`, `credit`) VALUES
(17, 12, 1, 6500.00, 0.00),
(18, 12, 17, 0.00, 6500.00),
(19, 13, 55, 12999.00, 0.00),
(20, 13, 17, 0.00, 12999.00),
(21, 14, 1, 30999.00, 0.00),
(22, 14, 17, 0.00, 30999.00),
(23, 15, 55, 3199.00, 0.00),
(24, 15, 17, 0.00, 3199.00),
(25, 16, 55, 5500.00, 0.00),
(26, 16, 17, 0.00, 5500.00),
(27, 17, 55, 3199.00, 0.00),
(28, 17, 17, 0.00, 3199.00);

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `journal_entries`
--

INSERT INTO `journal_entries` (`id`, `entry_date`, `description`, `reference_no`, `created_by`, `created_at`) VALUES
(12, '2025-10-22', 'Cash sales entry for Shopify Order ##JO5169', 'Shopify Order ##JO5169', 1, '2025-10-22 06:45:42'),
(13, '2025-10-22', 'Credit sales entry for Shopify Order ##JO5172', 'Shopify Order ##JO5172', 1, '2025-10-22 07:43:17'),
(14, '2025-10-22', 'Cash sales entry for Shopify Order ##JO5176', 'Shopify Order ##JO5176', 1, '2025-10-22 11:53:00'),
(15, '2025-10-23', 'Credit sales entry for Shopify Order ##JO5198', 'Shopify Order ##JO5198', 1, '2025-10-23 06:52:10'),
(16, '2025-10-23', 'Credit sales entry for Shopify Order ##JO5197', 'Shopify Order ##JO5197', 1, '2025-10-23 06:56:34'),
(17, '2025-10-23', 'Credit sales entry for Shopify Order ##JO5195', 'Shopify Order ##JO5195', 1, '2025-10-23 06:57:50');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `supplier_type` enum('Manufacturer','Wholesaler','Retailer','Service Provider','Other') DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `currency_preference` varchar(3) DEFAULT 'USD',
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(50) DEFAULT NULL,
  `bank_iban` varchar(50) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive','Blacklisted') DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `address`, `supplier_type`, `payment_terms`, `account_id`, `currency_preference`, `bank_name`, `bank_account_no`, `bank_iban`, `website`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Leather Right Multan Road ', '', '03096611228', 'Multan Road ', 'Retailer', 'CASH', 30, 'Oth', 'Meezan Bank ', '02500103920953', '', '', 'Active', '', '2025-06-16 12:11:02', '2025-10-16 15:49:07'),
(2, 'Siddiqiui Brothers Gumti Bazar', '', '03214948507', 'Gumti Bazar Walled City Lahore ', 'Retailer', 'CASH', 31, 'Oth', 'Meezan Bank', '02120102381418', '', '', 'Active', '', '2025-06-16 12:14:50', '2025-10-16 15:49:07'),
(3, 'Faysal Heel Maker ', '', '03214870543', 'Lahore', 'Retailer', 'CASH', 32, 'Oth', 'Jazz Cash ', '03214870543', '', '', 'Active', '', '2025-06-16 12:16:32', '2025-10-16 15:49:07'),
(4, 'Fazal PU ', '', '03314601110', 'Taxali Gate ', 'Retailer', 'CASH', 33, 'Oth', '', '', '', '', 'Active', '', '2025-06-16 12:18:40', '2025-10-16 15:49:07'),
(5, 'Nadeem Leather Store ', '', '03107779657', '', 'Retailer', 'CASH', 34, 'Oth', 'Meezan Bank', '02210102806948', '', '', 'Active', '', '2025-06-16 12:22:30', '2025-10-16 15:49:07'),
(6, 'Johar Accecsories', '', '', 'Moti Bazar Walled City Lahore', 'Retailer', 'CASH', 35, 'Oth', '', '', '', '', 'Active', '', '2025-06-16 12:23:38', '2025-10-16 15:49:07'),
(7, 'Winner Solution', '', '03224232297', '60 Sami Town Bund Road Lahore', 'Wholesaler', 'Credit ', 36, 'Oth', 'Meezan Bank', '02150101086536', 'Abdul Qadir ', '', 'Active', '', '2025-06-16 12:26:45', '2025-10-16 15:49:07'),
(8, 'Brother Packages ', '', '0333 8403231', 'Plot 94-98 B brothers packages opp starco fan industries small industrial area gujrat', 'Manufacturer', '', 37, 'Oth', 'Alfalah Bank ', '', '', '', 'Active', '', '2025-07-02 14:01:14', '2025-10-16 15:49:07'),
(9, 'Raja Billu Rexine Store ', '', '03248787838', '', 'Retailer', '', 38, 'Oth', '', '', '', '', 'Active', '', '2025-07-04 11:18:12', '2025-10-16 15:49:07'),
(10, 'Qasim Khan Insole Maker ', '', '03002742111', '', 'Manufacturer', '', 39, 'Oth', '', '', '', '', 'Active', '', '2025-07-31 08:24:52', '2025-10-16 15:49:07'),
(11, 'Mehboob Material Store ', '', '03394387786', 'Mahajarabad', 'Manufacturer', 'Cash', 40, '', 'Meezan Bank ', '02400110842221', '', '', 'Active', '', '2025-10-09 10:15:09', '2025-10-16 15:49:07'),
(12, 'Nasir Material Store ', '', '03214676998', 'Mahajarabad', 'Retailer', '', 41, 'Oth', '', '', '', '', 'Active', '', '2025-10-10 12:53:02', '2025-10-16 15:49:07'),
(13, 'Usman Flyers', '', '03336988312', 'Lahore', 'Manufacturer', '', 52, 'Oth', '', '', '', '', 'Active', '', '2025-10-17 14:08:53', '2025-10-17 14:08:53'),
(14, 'Nasir Material Store ', '', '', '', 'Retailer', '', 53, '', '', '', '', '', 'Active', '', '2025-10-17 14:10:08', '2025-10-17 14:10:08'),
(15, 'Amir Qaudri Karachi ', '', '03218265661', 'Karachi ', 'Wholesaler', 'Cash', 54, 'Oth', '', '', '', '', 'Active', '', '2025-10-20 06:35:56', '2025-10-20 06:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `tblcustomers`
--

CREATE TABLE `tblcustomers` (
  `CustomerID` int(11) NOT NULL,
  `CustomerName` varchar(100) NOT NULL,
  `ContactPerson` varchar(100) DEFAULT NULL,
  `account_id` int(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcustomers`
--

INSERT INTO `tblcustomers` (`CustomerID`, `CustomerName`, `ContactPerson`, `account_id`, `Phone`, `Email`, `Address`, `CreatedAt`) VALUES
(3, 'Pastel Toes', 'Abrar Hassan', 45, '03018467111', '', '', '2025-06-03 08:27:33'),
(4, 'Ids by Isma', 'Nasir', 46, '03018462527', '', '', '2025-07-02 13:12:41'),
(8, 'Pastel Toes to Barqraftaar', 'Najam', 47, '03161420407', '', '', '2025-10-09 10:08:59'),
(9, 'Ids to Barqraftaar', 'Hussain', 48, '03020540502', '', '', '2025-10-09 10:09:52'),
(10, 'Ids to Call Courier', 'Uzma Yaqoob', 49, '03213112288', '', '', '2025-10-09 10:11:04'),
(11, 'Pastel Toes to Call Courier', 'Hassan Randhawa', 50, '03190027316', '', '', '2025-10-09 10:12:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal_details`
--
ALTER TABLE `journal_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journal_id` (`journal_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcustomers`
--
ALTER TABLE `tblcustomers`
  ADD PRIMARY KEY (`CustomerID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `journal_details`
--
ALTER TABLE `journal_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tblcustomers`
--
ALTER TABLE `tblcustomers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `journal_details`
--
ALTER TABLE `journal_details`
  ADD CONSTRAINT `journal_details_ibfk_1` FOREIGN KEY (`journal_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_details_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
