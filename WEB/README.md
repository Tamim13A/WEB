# Real Estate Listing & Inquiry Portal

**CIS324 Web Engineering - Fall 2026 Semester Project**  
**Student:** 241-16-034 | **Section:** 19_A

---

## 📋 Project Overview

A dynamic, database-driven real estate web application built with PHP MVC architecture and MySQL. Features three user roles (Admin, Agent, Customer) with role-based access control, property listings with search/filter, inquiry system, favourites, and secure authentication.

---

## 🛠 Technology Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **Backend** | PHP 8.1+ (MVC Architecture) |
| **Database** | MySQL 8.0+ |
| **DB Connectivity** | PDO (Prepared Statements) |
| **API** | JSON REST API + Fetch API |
| **Authentication** | PHP Sessions + Password Hashing (Argon2id) |
| **Security** | CSRF Protection, XSS Prevention, SQL Injection Protection |

---

## ✨ Features

### 🏠 Public Website
- Responsive property listings with search & filter
- Property details with image gallery
- Category, price range, location, type filters
- Featured properties showcase

### 🔐 Authentication System
- User registration with validation
- Secure login/logout with session management
- Password hashing (Argon2id)
- Remember me functionality
- Role-based redirection (Admin/Agent/Customer)

### 👤 Customer Module
- Personal dashboard with stats
- Save/remove favourite properties
- Submit property inquiries
- View inquiry history & status

### 🏢 Agent Module
- Agent dashboard with property/inquiry stats
- Add/Edit/Delete own properties
- Multiple image upload with drag & drop
- Respond to customer inquiries
- Update inquiry status

### 🛡 Admin Module
- Admin dashboard with system statistics
- User management (activate/deactivate/delete)
- Property approval/rejection workflow
- Category management
- Inquiry oversight

---

## 🗄 Database Schema

**6 Normalized Tables (3NF):**

| Table | Purpose |
|-------|---------|
| `users` | All users (Admin, Agent, Customer) |
| `property_categories` | Property types (Apartment, House, etc.) |
| `properties` | Property listings owned by agents |
| `property_images` | Multiple images per property |
| `inquiries` | Customer inquiries on properties |
| `favourites` | Customer saved properties (Many-to-Many) |

**Key Relationships:**
- User 1:N Properties (Agent owns properties)
- User 1:N Inquiries (Customer submits inquiries)
- Property 1:N Inquiries (Property receives inquiries)
- Property 1:N Images (Property has multiple images)
- User N:M Properties via Favourites (Customer saves favourites)

---

## 🔒 Security Features

- **SQL Injection Prevention**: PDO Prepared Statements on ALL queries
- **XSS Prevention**: Context-aware output escaping (`htmlspecialchars`)
- **Password Security**: Argon2id hashing with high memory cost
- **Session Security**: Regeneration on login, HttpOnly/Secure/SameSite cookies, 30-min timeout
- **CSRF Protection**: Per-form tokens with validation
- **File Upload Security**: MIME validation, size limits, secure renaming
- **Authorization**: Ownership checks on all protected operations
- **Security Headers**: CSP, X-Frame-Options, X-Content-Type-Options

---

## 📁 Project Structure

```
real-estate-portal/
├── app/
│   ├── Config/          # Configuration files
│   ├── Core/            # Core MVC classes (Router, Controller, Model, etc.)
│   ├── Controller/      # Controllers (Auth, Property, Inquiry, etc.)
│   ├── Model/           # Models (User, Property, Inquiry, etc.)
│   ├── View/            # View templates (layouts, partials, pages)
│   └── Middleware/      # Auth, Role, CSRF middleware
├── public/
│   ├── index.php        # Front controller
│   ├── .htaccess        # Apache rewrite rules
│   └── assets/          # CSS, JS, Images
├── database/
│   ├── schema.sql       # Complete database schema
│   └── seed.sql         # Test data
├── docs/
│   ├── er_diagram.drawio
│   └── architecture_diagram.drawio
├── .env.example         # Environment template
├── composer.json        # PSR-4 autoload
└── README.md
```

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Apache/Nginx with mod_rewrite
- Composer

### Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/241-16-034_19_A_CIS324_Project.git
   cd real-estate-portal
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Create Database**
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```

5. **Configure Web Server**
   - Document root: `public/`
   - Enable mod_rewrite (Apache) or equivalent

6. **Set Permissions**
   ```bash
   chmod -R 755 public/assets/images/uploads/
   ```

---

## 🔧 Configuration

### `.env` Variables
```env
APP_BASE_URL=http://localhost
APP_DEBUG=true

DB_HOST=localhost
DB_PORT=3306
DB_NAME=realestate_portal
DB_USER=root
DB_PASS=your_password
```

---

## 👥 Test Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@realestate.test | password123 |
| **Agent 1** | agent1@realestate.test | password123 |
| **Agent 2** | agent2@realestate.test | password123 |
| **Customer 1** | customer1@realestate.test | password123 |
| **Customer 2** | customer2@realestate.test | password123 |

---

## 📡 API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/properties` | List properties with filters | Public |
| GET | `/api/properties/{id}` | Get property details | Public |
| GET | `/api/properties/featured` | Get featured properties | Public |
| GET | `/api/categories` | List categories with counts | Public |
| GET | `/api/cities` | List distinct cities | Public |
| POST | `/api/favourites/toggle` | Toggle favourite | Customer |
| POST | `/api/inquiries` | Submit inquiry | Customer |
| GET | `/api/properties/stats` | Property statistics | Admin |

**Example Request:**
```javascript
const response = await fetch('/api/properties?city=Dhaka&min_price=5000000&price_type=sale', {
    headers: { 'Accept': 'application/json' }
});
const data = await response.json();
```

---

## 🎯 Assignment Compliance

This project fulfills all **CIS324 Fall 2026** requirements:

| Task | Requirement | Status |
|------|-------------|--------|
| **Q1** | Request-Response Lifecycle + Architecture Diagram | ✅ |
| **Q2** | WDLC (7 phases) + 3 Web Standards | ✅ |
| **Q3** | 5+ Pages, PHP Concepts, Login, Sessions, Cookies | ✅ |
| **Q4** | Normalized DB (3+ tables), PK/FK, ER Diagram, CRUD (2 entities), Prepared Statements | ✅ |
| **Q5** | Server-side Validation, Sanitization, Regex, 3 Threat Analyses (SQLi, XSS, Weak Passwords) | ✅ |
| **Q6** | JSON API + Fetch, Comparison (Performance, UX, Coupling, Security) | ✅ |
| **Q7** | Cookies, Sessions, RBAC, Session Hijacking/Fixation Analysis, 2 Hardening Measures | ✅ |
| **Q8** | PHP MVC Implementation, MVC vs Procedural Comparison | ✅ |
| **Q9** | Final Evaluation with Limitations & Improvements | ✅ |

---

## 📄 License

Academic project for CIS324 Web Engineering course. Not for commercial use.

---

## 👨‍🎓 Author

**Student ID:** 241-16-034  
**Section:** 19_A  
**Course:** CIS324 Web Engineering  
**Semester:** Fall 2026