# 🎓 SCCI Workshop Registration System

<div align="center">

![SCCI Logo](assets/img/SCCI_Logo.png)

**Student Community for Computer Innovations**

A comprehensive workshop registration and management system built with PHP, MySQL, and modern web technologies.

[![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production-success.svg)]()

[Demo](#demo) • [Features](#features) • [Installation](#installation) • [Documentation](#documentation)

</div>

---

## 📖 Table of Contents

- [About](#about)
- [Features](#features)
- [Technologies](#technologies)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Schema](#database-schema)
- [Security Features](#security-features)
- [Performance Optimization](#performance-optimization)
- [Admin Dashboard](#admin-dashboard)
- [API Endpoints](#api-endpoints)
- [File Structure](#file-structure)
- [Screenshots](#screenshots)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## 🎯 About

SCCI Workshop Registration System is a full-featured web application designed to manage workshop registrations for student organizations. It provides a user-friendly registration form, secure data handling, and a powerful admin dashboard for managing participants.

### Key Highlights

- 🔐 **Secure**: Multiple layers of security including SQL injection prevention, XSS protection, and rate limiting
- ⚡ **Fast**: Optimized queries, database indexing, and efficient pagination
- 📱 **Responsive**: Works seamlessly on desktop, tablet, and mobile devices
- 🎨 **Modern UI**: Clean, professional design with smooth animations
- 📊 **Analytics**: Real-time statistics and participant tracking
- 📥 **Export**: CSV export functionality with professional formatting

---

## ✨ Features

### 🎫 Registration System
- ✅ Multi-step workshop preference selection (1st, 2nd, 3rd choice)
- ✅ Real-time form validation
- ✅ Duplicate email detection
- ✅ Technical skills assessment
- ✅ Rate limiting (3 submissions per session, 5 per IP per hour)
- ✅ AJAX-powered submission
- ✅ Success/error feedback

### 👨‍💼 Admin Dashboard
- ✅ Secure login system with session management
- ✅ Real-time statistics (total, 1st/2nd/3rd choices, today's registrations)
- ✅ Participant status tracking (Pending, Contacted, Scheduled, Rejected)
- ✅ Advanced search and filtering
- ✅ Pagination (50 items per page)
- ✅ AJAX status updates
- ✅ CSV export with color-coded preferences
- ✅ Auto-logout after 30 minutes of inactivity

### 🔒 Security Features
- ✅ Prepared statements (SQL injection prevention)
- ✅ Input sanitization and validation
- ✅ XSS protection
- ✅ Session hijacking prevention
- ✅ Secure password hashing (bcrypt)
- ✅ HTTPS enforcement
- ✅ Rate limiting
- ✅ IP-based throttling
- ✅ Secure error logging

### ⚡ Performance
- ✅ Database indexing (7 indexes)
- ✅ Optimized queries (<50ms average)
- ✅ GZIP compression
- ✅ Browser caching
- ✅ Lazy loading
- ✅ Minified CSS/JS
- ✅ Image optimization

---

## 🛠️ Technologies

### Backend
- **PHP 8.1+** - Server-side logic
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling with modern features
- **JavaScript (ES6+)** - Client-side interactivity
- **AJAX** - Asynchronous data handling

### Server
- **Apache/LiteSpeed** - Web server
- **mod_deflate** - GZIP compression
- **mod_expires** - Browser caching

---

## 📋 System Requirements

### Minimum Requirements
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Apache 2.4+ or LiteSpeed
- 256 MB RAM
- 100 MB disk space

### Recommended
- PHP 8.2+
- MySQL 8.0+
- 512 MB RAM
- HTTPS/SSL certificate
- OPcache enabled
- GZIP compression enabled

### PHP Extensions Required
- `pdo`
- `pdo_mysql`
- `mbstring`
- `json`
- `session`

---


### Workshop Passwords

To generate secure password hashes:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

### Timezone Settings

The system uses Cairo timezone (GMT+2). To change:
```php
date_default_timezone_set('Your/Timezone');
```

### Rate Limiting

Adjust in `process_workshop.php`:
```php
// 3 registrations per session, 5 per IP per hour
if ($limitData['count'] >= 3) { ... }
if ($ip_limit['count'] >= 5) { ... }
```

### Pagination

Change items per page in `admin/dashboard.php`:
```php
const ITEMS_PER_PAGE = 50; // Default: 50
```



## 🔐 Security Features

### Input Validation
- ✅ Server-side validation for all inputs
- ✅ Type checking and length limits
- ✅ Regex pattern matching
- ✅ Email format validation
- ✅ Phone number sanitization

### SQL Injection Prevention
```php
// Using prepared statements
$stmt = $pdo->prepare("SELECT * FROM participants WHERE email = ?");
$stmt->execute([$email]);
```

### XSS Protection
```php
// Sanitizing output
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
```

### Session Security
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
```

### Rate Limiting
- 3 submissions per session
- 5 submissions per IP per hour
- Automatic cooldown period

---

## ⚡ Performance Optimization

### Database Optimization
- **7 indexes** for fast queries
- **Query execution time**: <50ms average
- **Connection pooling**: Persistent connections
- **Prepared statements**: Query caching

### Frontend Optimization
- **GZIP compression**: 60-80% size reduction
- **Browser caching**: 1 year for images, 1 month for CSS/JS
- **Lazy loading**: Images load on demand
- **Minified assets**: Reduced file sizes

### Server Optimization
- **OPcache**: PHP bytecode caching
- **Keep-Alive**: Persistent connections
- **ETags disabled**: Better caching


## 👨‍💼 Admin Dashboard

### Login Credentials

Default workshop codes and passwords are set in `config.php`.

### Dashboard Features

#### Statistics Overview
- Total registrations
- 1st/2nd/3rd preference counts
- Today's registrations
- Participants with skills

#### Participant Management
- View all participants
- Search by name, email, phone
- Filter by preference level
- Filter by technical skills
- Update participant status

#### Status Options
- 🟡 **Pending**: New registration
- 🟠 **Contacted**: Reached out
- 🟢 **Scheduled**: Interview set
- 🔴 **Rejected**: Not selected

#### Export Features
- CSV export with all participant data
- Color-coded preferences
- Status column for tracking
- HR notes column

---

## 📡 API Endpoints

### Registration Endpoint

**POST** `/process_workshop.php`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "01234567890",
  "university": "Cairo University",
  "faculty": "Engineering",
  "level": "3rd Year",
  "first_preference": "Devology",
  "second_preference": "Techsolve",
  "third_preference": "Data Station",
  "tech_skills": "Python, JavaScript"
}
```



---

## 📁 File Structure
```
scci-registration/
│
├── admin/                          # Admin dashboard
│   ├── assets/
│   │   ├── css/
│   │   │   ├── root.css           # Global styles
│   │   │   ├── dashboard.css      # Dashboard styles
│   │   │   └── login.css          # Login styles
│   │   └── js/
│   │       └── all.min.js         # Minified JavaScript
│   │
│   ├── dashboard.php              # Main dashboard
│   ├── login.php                  # Login page
│   ├── logout.php                 # Logout handler
│   ├── export.php                 # CSV export
│   └── update_status.php          # AJAX status update
│
├── assets/
│   ├── css/
│   │   ├── root.css               # Global variables
│   │   └── form.css               # Form styles
│   ├── img/
│   │   ├── SCCI_Logo.png          # Logo
│   │   └── background.webp        # Background image
│   └── js/
│       └── form.js                # Form validation & AJAX
│
├── database/
│   └── schema.sql                 # Database schema
│
├── config.php                     # Database configuration
├── index.php                      # Registration form
├── registration_form.php          # Alternative form
├── process_workshop.php           # Form processor
├── test_performance.php           # Performance testing
├── closed.php                     # Registration closed page
├── .htaccess                      # Apache configuration
├── README.md                      # This file
