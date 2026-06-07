# Employee Leave Management System

A complete web-based Leave Management System built with PHP 8.x, MySQL, HTML5, Bootstrap 5, jQuery, and AJAX.

---

## Features

- **3 User Roles**: Admin, Manager, Employee
- **Secure Authentication**: bcrypt-hashed passwords, session management, auto-timeout
- **Employee Module**: Apply leave, view history, check balance
- **Manager Module**: Approve/reject requests with real-time AJAX updates
- **Admin Module**: User management, leave configuration, reports with CSV export
- **Business Rules**: Date validation, overlap detection, balance enforcement
- **Charts**: Chart.js dashboards for admin and employee views
- **AJAX**: All key actions (apply, approve, reject, search) without page refresh
- **Audit Log**: All actions tracked with user and IP

---

## Technology Stack

| Layer      | Technology                         |
|------------|-------------------------------------|
| Backend    | PHP 8.x (Plain PHP, no framework)  |
| Database   | MySQL 5.7+ / MariaDB               |
| Frontend   | HTML5, CSS3, Bootstrap 5.3         |
| JS Library | jQuery 3.7, DataTables, Chart.js   |
| Icons      | Bootstrap Icons 1.11               |

---

## Requirements

- PHP 8.2 or higher
- MySQL 5.7+
- Apache with mod_rewrite or Nginx
- XAMPP / WAMP / LAMP / MAMP (for local development)

---

## Setup Instructions

### Step 1: Clone / Extract the Project

Place the project folder inside your web server root:

```
XAMPP:  C:/xampp/htdocs/leave_management/
WAMP:   C:/wamp64/www/leave_management/
Linux:  /var/www/html/leave_management/
```

### Step 2: Create the Database

1. Open **phpMyAdmin** or any MySQL client.
2. Create a new database named `emp_leave_management`.
3. Import the SQL file:

```sql
-- Option A: Via phpMyAdmin
-- Select database → Import → Choose file: sql/emp_leave_management.sql

-- Option B: Via terminal
mysql -u root -p leave_management < sql/leave_management.sql
```

### Step 3: Configure Database Connection

Edit `config/db.php` with your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'emp_leave_management');
```

### Step 4: Set Application URL

Edit `config/app.php`:

```php
define('APP_URL', 'http://localhost/leave_management');
```

If you're using a different port (e.g., WAMP on port 8080):
```php
define('APP_URL', 'http://localhost:8080/leave_management');
```

### Step 5: Run the Application

Open your browser and navigate to:

```
http://localhost/leave_management/
```

---

## Test User Credentials

| Role     | Email                    | Password   |
|----------|--------------------------|------------|
| Admin    | admin@gmail.com          | User1234   |
| Manager  | john@gmail.com           | User1234   |
| Employee | rohit@gmail.com          | User1234   |

---

## Project Structure

```
leave_management/
├── index.php                                   # Login page
├── logout.php                                  # Logout handler
├── dashboard.php                               # Role-based dashboard router
├── screenshots/
│   ├── login_page.png                          # Login page 
│   ├── employee_dashboard.png                  # Employer Side Dashboard page              
│   ├── apply_leave_page.png                    # Employer Leave Apply Page 
│   ├── employee_leave_history.png              # Employer Leave History Page
│   ├── manager_dashboard.png                   # Manager Dashbaord page
│   ├── pending_request_page.png                # Pending Leave Request page 
│   ├── admin_dashboard.png                     # Admin side Dashboard page
│   ├── users_page.png                          # Users List page
│   ├── add_user_page.png                       # Admin side add user page
│   ├── leave_config_page.png                   # Leave Configuration page
│   ├── admin_report_page.png                   # Total Leave report page
├── config/
│   ├── app.php                                 # App config, helpers, session management
│   └── db.php                                  # PDO database connection
├── includes/
│   ├── header.php                              # Shared navbar & HTML head
│   └── footer.php                              # Shared footer & JS includes
├── modules/
│   ├── employee/
│   │   ├── dashboard.php                       # Employee home with stats & balances
│   │   ├── apply_leave.php                     # Leave application form
│   │   └── leave_history.php                   # Leave history with filters
│   ├── manager/
│   │   ├── dashboard.php                       # Manager home with quick approve/reject
│   │   └── pending_requests.php                # All leave requests with full actions
│   └── admin/
│       ├── dashboard.php                       # Admin home with charts
│       ├── users.php                           # User list with AJAX search
│       ├── create_user.php                     # Create new user
│       ├── leave_config.php                    # Manage leave types & quotas
│       └── reports.php                         # Leave reports with CSV export
├── ajax/
│   ├── apply_leave.php                         # Submit leave application
│   ├── update_leave_status.php                 # Approve / reject leave
│   ├── search_users.php                        # Live user search
│   ├── get_user.php                            # Fetch user data for edit
│   ├── create_user.php                         # Create user
│   ├── update_user.php                         # Update user
│   ├── toggle_user_status.php                  # Activate / deactivate user
│   ├── update_leave_type.php                   # Edit leave type
│   └── add_leave_type.php                      # Add new leave type
├── assets/
│   ├── css/style.css                           # Custom stylesheet
│   └── js/app.js                               # AJAX helpers, date logic, DataTables
└── sql/
    └── emp_leave_management.sql                # Full database schema + seed data
```

---

## Business Rules Implemented

| Rule | Description |
|------|-------------|
| Rule 1 | Start date cannot be earlier than today |
| Rule 2 | End date cannot be before start date |
| Rule 3 | Total days auto-calculated (inclusive) |
| Rule 4 | Cannot apply beyond available balance |
| Rule 5 | Overlapping leave requests rejected |
| Rule 6 | Approved leaves cannot be edited |

---

## AJAX Operations

| Operation          | Endpoint                         |
|--------------------|----------------------------------|
| Apply Leave        | ajax/apply_leave.php             |
| Approve Leave      | ajax/update_leave_status.php     |
| Reject Leave       | ajax/update_leave_status.php     |
| Search Users       | ajax/search_users.php            |
| Create User        | ajax/create_user.php             |
| Update User        | ajax/update_user.php             |
| Toggle User Status | ajax/toggle_user_status.php      |
| Update Leave Type  | ajax/update_leave_type.php       |
| Add Leave Type     | ajax/add_leave_type.php          |

---

## Security Features

- Passwords hashed with `bcrypt` (cost factor 12)
- PDO prepared statements (SQL injection prevention)
- `htmlspecialchars()` on all output (XSS prevention)
- Role-based access control on every page and AJAX endpoint
- Session timeout (30 minutes)
- `session_regenerate_id()` on login
- HTTPOnly session cookies

---
