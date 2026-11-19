# Real Estate Listing & Inquiry Portal

**Course Title:** Web Engineering (Theory)  
**Course Code:** CIS324 (Level 3, Term 3)  
**Selected Topic:** Topic #10: Real Estate Listing and Inquiry Portal  
**Institution:** Daffodil International University (DIU)  
**Faculty:** Faculty of Science and Information Technology (FSIT)  
**Department:** Department of Computing and Information System (CIS)  

**Student Name:** Md Tamim Ahmed  
**Student ID:** 241-16-034  
**Batch & Section:** 19_A  

---

## 📌 Project Overview

The **Real Estate Listing & Inquiry Portal** is a dynamic, database-driven web application tailored for the contemporary property market. Built from scratch using native PHP, MySQL, semantic HTML5, responsive CSS3, and vanilla JavaScript (Fetch API), the system facilitates seamless property exploration, live asynchronous searching, direct customer-to-agent inquiry communication, and role-based administrative management.

The project intentionally avoids heavy third-party frameworks to maintain a clean, defendable codebase adhering strictly to the CIS324 Web Engineering curriculum and Bloom's Taxonomy course outcomes (CO1, CO2, CO3).

---

## 🛠️ Technology Stack

| Layer | Technologies & Tools |
|---|---|
| **Frontend** | HTML5 (Semantic Landmarks), CSS3 (Flexbox & Grid, Mobile-First Responsive), Vanilla JavaScript ES6 (Fetch API, DOM Manipulation) |
| **Backend** | Native PHP 8+ (Procedural Controllers, Model-View-Controller Alignment, Superglobals `$_GET`, `$_POST`, `$_SESSION`, `$_COOKIE`, `$_FILES`) |
| **Database** | MySQL / MariaDB (Normalized 3NF Relational Schema, InnoDB Engine, UTF8mb4 Charset) |
| **Database Access** | PDO (PHP Data Objects) with Prepared Statements & Parameterized Queries |
| **API Integration** | RESTful JSON Endpoint (`/api/properties.php`) consumed asynchronously via Fetch API |
| **Authentication** | Native PHP Sessions, Cookie Hardening (`HttpOnly`, `SameSite=Lax`), Bcrypt Password Hashing (`PASSWORD_BCRYPT`) |
| **Security Controls** | Context-Aware Output Escaping (`htmlspecialchars`), Strict Server-Side Regex & Numeric Validation, Role-Based Access Control (RBAC) |

---

## 👥 User Roles & Permissions

The application implements a 3-tier Role-Based Access Control (RBAC) system:

1. **Customer / Buyer / Tenant:**
   - Browse published properties with pagination and instant filtering.
   - Perform real-time live searches using asynchronous Fetch API (search by city, title, or buy/rent type).
   - Retain search preferences using client-side cookies (`pref_type`).
   - Inspect comprehensive property detail views with high-resolution imagery.
   - Submit direct inquiries to property agents with contact information.
   - Access a personalized customer dashboard to monitor inquiry statuses and agent replies.

2. **Agent / Property Owner:**
   - Authenticate securely via login credentials.
   - Access the Agent Dashboard to manage listings.
   - Publish new properties with title, price, transaction type (sale/rent), city, description, and image upload.
   - Perform full CRUD operations (Create, Read, Update pricing/details, Delete listings).
   - Review and reply to inquiries received specifically for their listings.

3. **Administrator:**
   - Central platform oversight with elevated privileges.
   - View, edit, and delete any property listing across all agents.
   - Monitor and manage all customer inquiries across the entire platform.

---

## 📁 Repository Structure

```
├── api/
│   └── properties.php           # JSON Web API endpoint for asynchronous Fetch search
├── assets/
│   ├── css/
│   │   └── style.css            # Responsive, mobile-first CSS styling
│   ├── js/
│   │   └── script.js            # Client-side Fetch API, debounced search & DOM rendering
│   └── images/
│       └── uploads/             # Property photograph catalog (prop1.jpg – prop13.jpg)
├── config/
│   └── database.php             # PDO database connection, session hardening & security helpers
├── database/
│   └── real_estate.sql          # 3NF relational schema (users, properties, inquiries) & seed data
├── includes/
│   ├── header.php               # Semantic HTML head, responsive navbar & flash messages
│   └── footer.php               # Semantic footer & script inclusions
├── models/
│   └── Property.php             # Data Model class with PDO prepared statement queries
├── .env                         # Environment configuration file
├── .gitignore                   # Git ignore specifications
├── dashboard.php                # Unified CRUD dashboard for Properties and Inquiries
├── index.php                    # Homepage, property catalog controller & live search interface
├── login.php                    # User authentication controller with session fixation prevention
├── logout.php                   # Session termination and state cleanup
├── property-details.php         # Single property view & customer inquiry submission
├── register.php                 # User registration controller (Customer & Agent accounts)
└── README.md                    # Project documentation & setup instructions
```

---

## 🚀 Setup & Installation Guide

### Prerequisites:
- **PHP 8.0+** (with `pdo_mysql` extension enabled)
- **MySQL 5.7+ / MariaDB 10.4+**
- **Apache Web Server** (e.g., via XAMPP, WAMP, or PHP Built-in Server)

---

### Step 1: Clone Repository
Clone the repository to your local web root directory:
```bash
# If using XAMPP:
cd C:/xampp/htdocs
git clone https://github.com/Tamim13A/WEB.git real-estate-portal
cd real-estate-portal
```

---

### Step 2: Database Setup
1. Start **Apache** and **MySQL** services from the XAMPP Control Panel.
2. Open **phpMyAdmin** in your browser (`http://localhost/phpmyadmin/`).
3. Click on the **Import** tab.
4. Select the database script: `database/real_estate.sql`.
5. Click **Import** (or **Go**). The script will automatically create the `realestate_portal` database, build all three 3NF tables with foreign keys and cascade rules, and populate realistic seed records.

*Alternatively, import via MySQL command-line client:*
```bash
mysql -u root -p < database/real_estate.sql
```

---

### Step 3: Configure Database Connection
Review `config/database.php` (or copy `.env.example` to `.env`):
```php
$host = 'localhost';
$db   = 'realestate_portal';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
```

---

### Step 4: Run the Application

#### Option A: Using PHP Built-in Development Server (Recommended)
Open a terminal in the project directory and run:
```bash
php -S localhost:8000
```
Then visit in your web browser:
```
http://localhost:8000/
```

#### Option B: Using XAMPP / Apache
If placed in `C:/xampp/htdocs/real-estate-portal/`, access:
```
http://localhost/real-estate-portal/
```

---

## 🔑 Default Test Accounts (Viva-Voce Evaluation)

All seed accounts are initialized with the secure bcrypt password: **`password123`**

| Role | Name | Email | Password | Access Scope & Capabilities |
|---|---|---|---|---|
| **Admin** | Tamim | `admin@realestate.test` | `password123` | Full governance: manage, edit, and delete all listings & inquiries |
| **Agent** | Tonmoy | `agent1@realestate.test` | `password123` | Publish new properties, edit/delete own listings, reply to inquiries |
| **Agent** | Tusher | `tusher@realestate.test` | `password123` | Publish new properties, edit/delete own listings, reply to inquiries |
| **Customer** | Tonu | `customer1@realestate.test` | `password123` | Browse catalog, search with Fetch API, submit inquiries, view replies |
| **Customer** | Sosso | `sosso@realestate.test` | `password123` | Browse catalog, search with Fetch API, submit inquiries, view replies |
| **Customer** | Marjuk | `marjuk@realestate.test` | `password123` | Browse catalog, search with Fetch API, submit inquiries, view replies |

---

## 🔒 Security Implementations

1. **SQL Injection Defense:**
   - 100% of database interactions are performed using PDO prepared statements with explicit parameter binding.
   - User inputs are never directly concatenated into SQL queries.
   - Example:
     ```php
     $stmt = $pdo->prepare("SELECT * FROM properties WHERE city = :city AND price <= :price");
     $stmt->execute([':city' => $city, ':price' => $price]);
     ```

2. **Cross-Site Scripting (XSS) Mitigation:**
   - Dual-layer protection: input normalization via `clean()` and context-aware HTML escaping on all rendered variables via `escape()`:
     ```php
     function escape(string $data): string {
         return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
     }
     ```

3. **Strict Server-Side Input Validation:**
   - Email format validated using `filter_var($email, FILTER_VALIDATE_EMAIL)`.
   - Name fields validated via strict regular expression: `/^[a-zA-Z\s.'-]{2,100}$/`.
   - Numeric and financial values validated using `is_numeric()` with positive range verification before database insertion.

4. **Session Hijacking & Fixation Protection:**
   - Session identifiers regenerated immediately upon successful authentication via `session_regenerate_id(true)`.
   - Session cookie security flags enforced:
     ```php
     session_set_cookie_params([
         'lifetime' => 1800,
         'path'     => '/',
         'httponly' => true,
         'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
         'samesite' => 'Lax'
     ]);
     ```

5. **Cryptographic Password Storage:**
   - Passwords hashed using industry-standard Bcrypt algorithm (`PASSWORD_BCRYPT` with cost factor 10) through `password_hash()`.
   - Verification handled in constant-time via `password_verify()`.

---

## 🌐 Web API Endpoint Documentation

The application exposes a lightweight JSON API endpoint consumed by the client-side JavaScript Fetch API for real-time live search without page reloads:

- **Endpoint:** `GET /api/properties.php`
- **Query Parameters:**
  - `search` (string, optional) — Filter properties by title or city name.
  - `type` (string, optional) — Filter properties by transaction type (`sale` or `rent`).
- **Response Headers:** `Content-Type: application/json; charset=utf-8`
- **Sample JSON Response:**
  ```json
  [
    {
      "id": 1,
      "title": "Luxury 3BR Apartment in Gulshan",
      "price": "15000000.00",
      "price_type": "sale",
      "city": "Dhaka",
      "image": "/assets/images/uploads/prop1.jpg",
      "description": "Spacious 3BR apartment with parking and elevator.",
      "created_at": "2026-11-19 12:00:00"
    }
  ]
  ```

---

## 📋 Course Learning Outcomes (COs) Mapping

- **CO1 (Task 1 & Task 2 - Q1, Q2, Q3):** Front-end and back-end web development principles, client-server HTTP lifecycle, Web Development Life Cycle (WDLC) phases, semantic HTML5, responsive CSS3, and 5 functional PHP pages.
- **CO2 (Task 2 & Task 3 & Task 4 - Q4, Q5, Q7):** 3NF normalized relational database design (`users`, `properties`, `inquiries`), complete CRUD operations, server-side validation, SQL injection/XSS mitigations, and session security hardening.
- **CO3 (Task 3 & Task 4 - Q6, Q8, Q9):** Front-end/back-end integration via JSON Web API and Fetch API, Model-View-Controller (MVC) architectural separation vs. procedural evaluation, and defensible architectural conclusions.

---

## 📜 License & Academic Ownership

This project is submitted in partial fulfillment of the academic requirements for the **CIS324: Web Engineering (Theory)** course at **Daffodil International University**.

All intellectual ownership belongs to **Md Tamim Ahmed** (Student ID: `241-16-034`). Unauthorized commercial reproduction or uncredited plagiarism is prohibited under DIU Academic Integrity Guidelines.
