# Expense Tracker Database Schema

This repository contains the SQL schema for an **Expense Tracker** application. The schema includes tables for managing users, accounts, transactions, categories, budgets, and the relationship between transactions and categories.

## Tables

### 1. **User Table**

This table stores information about the users of the system.

```sql
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(100) NOT NULL, 
    email VARCHAR(255) UNIQUE NOT NULL, 
    password VARCHAR(255) NOT NULL, -- Password field to store the hashed password
    date_joined DATE NOT NULL
);
CREATE TABLE Account (
    account_id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT NOT NULL, 
    account_type ENUM('Savings', 'Credit', 'Checking') NOT NULL, 
    date_created DATE NOT NULL, 
    bank_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Transaction (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY, 
    account_id INT NOT NULL, 
    date DATE NOT NULL, 
    transaction_amount DECIMAL(10, 2) NOT NULL, 
    transaction_type ENUM('Credit', 'Debit') NOT NULL,
    FOREIGN KEY (account_id) REFERENCES Account(account_id)
);

CREATE TABLE Category (
    category_id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(100) NOT NULL, 
    description TEXT
);

CREATE TABLE Budget (
    budget_id INT AUTO_INCREMENT PRIMARY KEY, 
    category_id INT NOT NULL, 
    user_id INT NOT NULL, 
    start_date DATE NOT NULL, 
    end_date DATE NOT NULL,
    FOREIGN KEY (category_id) REFERENCES Category(category_id), 
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE TC (
    transaction_id INT NOT NULL, 
    category_id INT NOT NULL, 
    PRIMARY KEY (transaction_id, category_id),
    FOREIGN KEY (transaction_id) REFERENCES Transaction(transaction_id), 
    FOREIGN KEY (category_id) REFERENCES Category(category_id)
);this the content i wnt to put in y git read me file, make this content look proper, as in in the stucture, give it the proper stucture


