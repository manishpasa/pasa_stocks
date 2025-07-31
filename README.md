-- sql for the code 
CREATE DATABASE IF NOT EXISTS pasa_stocks;
USE pasa_stocks;

-- Table: company
CREATE TABLE company (
  company_id INT(11) NOT NULL AUTO_INCREMENT,
  company_code VARCHAR(50) NOT NULL UNIQUE,
  company_name VARCHAR(100) NOT NULL,
  location VARCHAR(150),
  contact_number VARCHAR(15),
  total_employees INT(11),
  PRIMARY KEY (company_id)
);

-- Table: employee
CREATE TABLE employee (
  emp_id INT(11) NOT NULL AUTO_INCREMENT,
  emp_name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  phone VARCHAR(15),
  DOB DATE,
  company_code VARCHAR(50),
  role VARCHAR(50),
  profile_pic VARCHAR(255),
  email_verified TINYINT(1) DEFAULT 0,
  join_date DATE,
  PRIMARY KEY (emp_id),
  FOREIGN KEY (company_code) REFERENCES company(company_code)
);

-- Table: customer
CREATE TABLE customer (
  customer_id INT(11) NOT NULL AUTO_INCREMENT,
  cust_name VARCHAR(100),
  phone VARCHAR(15),
  company_id INT(11),
  email VARCHAR(100),
  address VARCHAR(255),
  join_date DATE,
  PRIMARY KEY (customer_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);

-- Table: inventory
CREATE TABLE inventory (
  item_id INT(11) NOT NULL AUTO_INCREMENT,
  item_name VARCHAR(100),
  cost_price DECIMAL(10,2),
  company_id INT(11),
  quantity INT(11),
  price DECIMAL(10,2),
  category VARCHAR(50),
  Quantity_sold INT(11) DEFAULT 0,
  PRIMARY KEY (item_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);

-- Table: purchase_list
CREATE TABLE purchase_list (
  item_id INT(11),
  quantity INT(11),
  cost_price DECIMAL(10,2),
  purchase_date DATE NOT NULL,
  supplier VARCHAR(100),
  company_id INT(11),
  PRIMARY KEY (item_id, purchase_date),
  FOREIGN KEY (item_id) REFERENCES inventory(item_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);

-- Table: bills
CREATE TABLE bills (
  bill_id INT(11) NOT NULL AUTO_INCREMENT,
  emp_id INT(11),
  customer_id INT(11),
  bill_date DATE,
  company_id INT(11),
  islive INT(10),
  PRIMARY KEY (bill_id),
  FOREIGN KEY (emp_id) REFERENCES employee(emp_id),
  FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);

-- Table: sold_list
CREATE TABLE sold_list (
  price DECIMAL(10,2),
  quantity INT(11),
  cost_price DECIMAL(10,2),
  bill_id INT(11),
  company_id INT(11),
  sale_date DATE,
  emp_id INT(11),
  item_id INT(11),
  customer_id INT(11),
  PRIMARY KEY (bill_id, item_id),
  FOREIGN KEY (bill_id) REFERENCES bills(bill_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id),
  FOREIGN KEY (emp_id) REFERENCES employee(emp_id),
  FOREIGN KEY (item_id) REFERENCES inventory(item_id),
  FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
);

-- Table: returned_list
CREATE TABLE returned_list (
  return_id INT(11) NOT NULL AUTO_INCREMENT,
  bill_id INT(11),
  item_id INT(11),
  quantity INT(11),
  reason TEXT,
  return_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  emp_id INT(11),
  company_id INT(11),
  PRIMARY KEY (return_id),
  FOREIGN KEY (bill_id) REFERENCES bills(bill_id),
  FOREIGN KEY (item_id) REFERENCES inventory(item_id),
  FOREIGN KEY (emp_id) REFERENCES employee(emp_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);

-- Table: supplier_returns (added missing foreign keys)
CREATE TABLE supplier_returns (
  id INT(11) NOT NULL AUTO_INCREMENT,
  item_id INT(11),
  quantity INT(11),
  reason TEXT,
  return_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  emp_id INT(11),
  company_id INT(11),
  PRIMARY KEY (id),
  FOREIGN KEY (item_id) REFERENCES inventory(item_id),
  FOREIGN KEY (emp_id) REFERENCES employee(emp_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);

-- Table: email_otp
CREATE TABLE email_otp (
  otp_code VARCHAR(10) NOT NULL,
  emp_id INT(11),
  created_at DATETIME,
  PRIMARY KEY (otp_code),
  FOREIGN KEY (emp_id) REFERENCES employee(emp_id)
);
-- Existing database
CREATE DATABASE IF NOT EXISTS pasa_stocks;
USE pasa_stocks;

-- 1. Add column is_solo to employee
ALTER TABLE employee
ADD COLUMN is_solo TINYINT(1) DEFAULT 0;

-- 2. New table: live_inventory
CREATE TABLE live_inventory (
  live_id INT(11) NOT NULL AUTO_INCREMENT,
  item_name VARCHAR(100) NOT NULL,
  company_id INT(11) NOT NULL,
  cost_per_unit DECIMAL(10,2) NOT NULL,
  sell_price DECIMAL(10,2) NOT NULL,
  total_bought INT(11) DEFAULT 0,
  total_sold INT(11) DEFAULT 0,
  total_cost DECIMAL(12,2) DEFAULT 0.00,
  category VARCHAR(50),
  added_date DATE DEFAULT CURRENT_DATE,
  PRIMARY KEY (live_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id)
);
CREATE TABLE live_inventory_sales (
  sale_id INT(11) NOT NULL AUTO_INCREMENT,
  live_id INT(11) NOT NULL,                -- Reference to live_inventory
  quantity_sold INT(11) NOT NULL,          -- How many were sold (can be fractional if needed)
  sold_price_per_unit DECIMAL(10,2) NOT NULL,  -- Actual selling price per unit (flexible)
  cost_price_per_unit DECIMAL(10,2),       -- Optional: cost per unit when bought
  total_amount DECIMAL(12,2) GENERATED ALWAYS AS (quantity_sold * sold_price_per_unit) STORED,
  sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  emp_id INT(11),                          -- Who sold
  company_id INT(11),
  bill_id INT(11),
  customer_id INT(11),
  PRIMARY KEY (sale_id),
  FOREIGN KEY (live_id) REFERENCES live_inventory(live_id),
  FOREIGN KEY (emp_id) REFERENCES employee(emp_id),
  FOREIGN KEY (company_id) REFERENCES company(company_id),
  FOREIGN KEY (bill_id) REFERENCES bills(bill_id),
  FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
);

