# QB PDF Converter - WR602D | BUT MMI  

### Welcome to the repository for my submission for resource **R6.DWeb-DI.02**!  
This project is part of the **WR602 - Web Development and Interactive Devices** course in the **BUT MMI** program. It consists of a web application developed with **Symfony 7.1**, allowing users to convert files, text, or URLs into PDF format. It also includes user authentication and a subscription system with different tiers.  

## Features  
- **User authentication** (login, registration)  
- **Subscription system** with different tiers (Standard, Expert, Premium)  
- **PDF generation** from URLs, text input, or file uploads  
- **User dashboard** to track PDF conversions  
- **Admin panel** to manage users and subscriptions  
- **Cypress E2E tests** to ensure application stability  

## Prerequisites  
- **PHP** (>= 8.1)  
- **Composer**  
- **Symfony CLI**  
- A **web server** (Apache or Nginx)  
- A **DBMS** (MySQL or PostgreSQL)  
- **Node.js** & **npm/yarn**  
- **Git**  

## Installation  

### 1. Clone the repository  
```sh
git clone https://github.com/YOUR_GITHUB_USERNAME/WR602D_MMI22C03.git  
cd WR602D_MMI22C03 
```  

### 2. Install dependencies  
```sh
composer install  
npm install  
```  

### 3. Configure the environment  
Duplicate the `.env` file at the root of the project and rename it to `.env.local`. Then, update the following line with your database credentials:  
```sh
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=10.8.8-MariaDB&charset=utf8mb4"  
```  
Replace `db_user`, `db_password`, and `db_name` with your actual credentials.  

### 4. Create and initialize the database  
```sh
php bin/console doctrine:database:create  
php bin/console doctrine:migrations:migrate  
```  

### 5. Load dummy data  
```sh
php bin/console doctrine:fixtures:load  
```  

## Running Cypress E2E Tests  
To ensure the application is working correctly, you can run Cypress tests:  
```sh
xvfb-run npx cypress run
```  
This will open the Cypress UI where you can execute E2E tests for login, PDF generation, and user interactions.  

## Start the project  
Make sure your **Docker environment** is properly configured and that the **Apache configuration file** includes the necessary redirection rules for Symfony.  

## Developer & Teacher  
- **Quentin Buteau** - B.U.T.3 MMI Student  
- **Romain Delon** - Instructor at IUT de Troyes  

### &copy; 2025 - Quentin BUTEAU | All rights reserved  

