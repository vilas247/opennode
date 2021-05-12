--
-- Drop table `247custompages`
--
DROP TABLE IF EXISTS `247custompages`;

--
-- Drop table `247webhooks`
--
DROP TABLE IF EXISTS `247webhooks`;

--
-- Drop table `api_log`
--
DROP TABLE IF EXISTS api_log;

--
-- Drop table `custom_opennodepay_button`
--
DROP TABLE IF EXISTS custom_opennodepay_button;

--
-- Drop table `opennode_scripts`
--
DROP TABLE IF EXISTS opennode_scripts;

--
-- Drop table `opennode_token_validation`
--
DROP TABLE IF EXISTS opennode_token_validation;

--
-- Drop table `order_details`
--
DROP TABLE IF EXISTS order_details;

--
-- Drop table `order_payment_details`
--
DROP TABLE IF EXISTS order_payment_details;

--
-- Drop table `order_refund`
--
DROP TABLE IF EXISTS order_refund;

--
-- Drop table `user`
--
DROP TABLE IF EXISTS user;

--
-- Drop table `webhook_log`
--
DROP TABLE IF EXISTS webhook_log;

--
-- Set default database
--
USE opennode;

--
-- Create table `webhook_log`
--
CREATE TABLE webhook_log (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  type VARCHAR(255) NOT NULL,
  operation VARCHAR(255) NOT NULL,
  api_response LONGTEXT NOT NULL,
  cat_or_product_id VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `user`
--
CREATE TABLE user (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  user_type VARCHAR(50) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `order_refund`
--
CREATE TABLE order_refund (
  r_id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  invoice_id VARCHAR(255) NOT NULL,
  refund_status VARCHAR(255) NOT NULL,
  refund_amount FLOAT NOT NULL,
  api_request LONGTEXT DEFAULT NULL,
  api_response LONGTEXT DEFAULT NULL,
  order_comments VARCHAR(255) DEFAULT NULL,
  created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (r_id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `order_payment_details`
--
CREATE TABLE order_payment_details (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  order_id VARCHAR(255) NOT NULL,
  cart_id VARCHAR(255) NOT NULL,
  type ENUM('SALE','AUTH') NOT NULL DEFAULT 'SALE',
  total_amount FLOAT NOT NULL,
  amount_paid FLOAT NOT NULL,
  currency VARCHAR(10) NOT NULL,
  status VARCHAR(255) NOT NULL DEFAULT 'PENDING',
  settlement_status VARCHAR(255) NOT NULL DEFAULT 'PENDING',
  params LONGTEXT DEFAULT NULL,
  api_response LONGTEXT DEFAULT NULL,
  settlement_response LONGTEXT DEFAULT NULL,
  created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `order_details`
--
CREATE TABLE order_details (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  invoice_id VARCHAR(255) NOT NULL,
  order_id VARCHAR(255) NOT NULL,
  bg_customer_id VARCHAR(255) NOT NULL,
  reponse_params LONGTEXT NOT NULL,
  total_inc_tax FLOAT NOT NULL,
  total_ex_tax FLOAT NOT NULL,
  currecy VARCHAR(20) NOT NULL,
  is_cancelled INT(11) NOT NULL DEFAULT 0,
  created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `opennode_token_validation`
--
CREATE TABLE opennode_token_validation (
  validation_id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  api_auth_token VARCHAR(255) NOT NULL,
  sellerdb VARCHAR(255) NOT NULL,
  acess_token VARCHAR(255) DEFAULT NULL,
  store_hash VARCHAR(255) DEFAULT NULL,
  is_enable INT(11) NOT NULL DEFAULT 0,
  payment_option ENUM('CFO','CFS') NOT NULL DEFAULT 'CFO',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (validation_id)
)
ENGINE = INNODB,
CHARACTER SET latin1,
COLLATE latin1_swedish_ci;

--
-- Create table `opennode_scripts`
--
CREATE TABLE opennode_scripts (
  script_id INT(11) NOT NULL AUTO_INCREMENT,
  token_validation_id INT(11) DEFAULT NULL,
  script_email_id VARCHAR(255) NOT NULL,
  script_filename VARCHAR(255) NOT NULL,
  script_code VARCHAR(255) NOT NULL,
  status INT(11) NOT NULL DEFAULT 0,
  api_response LONGTEXT DEFAULT NULL,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (script_id)
)
ENGINE = INNODB,
CHARACTER SET latin1,
COLLATE latin1_swedish_ci;

--
-- Create table `custom_opennodepay_button`
--
CREATE TABLE custom_opennodepay_button (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  container_id VARCHAR(255) NOT NULL,
  css_prop LONGTEXT NOT NULL,
  html_code LONGTEXT DEFAULT NULL,
  image_url VARCHAR(255) DEFAULT NULL,
  is_image_enabled INT(11) NOT NULL DEFAULT 0,
  is_enabled INT(11) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `api_log`
--
CREATE TABLE api_log (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  type VARCHAR(255) NOT NULL,
  action VARCHAR(255) NOT NULL,
  api_url VARCHAR(255) NOT NULL,
  api_request LONGTEXT DEFAULT NULL,
  api_response LONGTEXT DEFAULT NULL,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 2,
AVG_ROW_LENGTH = 16384,
CHARACTER SET latin1,
COLLATE latin1_swedish_ci;

--
-- Create table `247webhooks`
--
CREATE TABLE `247webhooks` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  webhook_bc_id VARCHAR(255) NOT NULL,
  scope VARCHAR(255) NOT NULL,
  destination TEXT NOT NULL,
  api_response LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create table `247custompages`
--
CREATE TABLE `247custompages` (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email_id VARCHAR(255) NOT NULL,
  token_validation_id INT(11) DEFAULT NULL,
  page_bc_id VARCHAR(255) NOT NULL,
  api_response LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

