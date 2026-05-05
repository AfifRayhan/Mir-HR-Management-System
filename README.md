<p align="center">
  <img src="public/images/mirlogo.png" alt="Mir HR Management System" width="120">
</p>

<h1 align="center">Mir HR Management System</h1>

<p align="center">
  A comprehensive Human Resource Management System built for <strong>Mir Group</strong> to streamline employee management, attendance tracking, leave processing, overtime calculation, and roster scheduling.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

---

## Overview

Mir HRMS is a role-based HR platform that centralizes core workforce operations into a single web application. It connects to attendance hardware devices, automates overtime calculations, manages leave workflows with multi-level approvals, and generates exportable reports in PDF and Excel formats.

**Built for three user roles:**

| Role | Capabilities |
|------|-------------|
| **HR Admin** | Full system access — manage employees, departments, attendance, leave balances, overtime, rosters, devices, reports, and system settings |
| **Team Lead** | Approve/reject leave applications and attendance adjustments for their team, add supervisor remarks |
| **Employee** | View personal dashboard, apply for leave, check attendance history, view roster schedules |

## Features

### 👥 Employee Management
- Complete employee profiles with personal, academic, and employment details
- Department, section, designation, and grade hierarchies
- Salary history tracking
- Bulk employee data import from Excel spreadsheets

### 📋 Attendance Tracking
- Integration with biometric attendance devices via API
- Daily, monthly, and yearly attendance views
- Manual attendance adjustments with approval workflow
- Late arrival and early departure detection
- Attendance reports with Excel/PDF export

### 🏖️ Leave Management
- Configurable leave types with annual balances
- Multi-step leave application workflow (Apply → Team Lead Review → HR Approval)
- Leave balance tracking and reports
- Calendar view of team leave schedules

### ⏰ Overtime Management
- Configurable overtime rates and rules
- Automatic overtime calculation from attendance data
- Overtime summary reports with Excel/PDF export
- Holiday and weekly-off aware calculations

### 📅 Roster & Scheduling
- Shift roster management with configurable time slots
- Specialized driver roster system
- Weekly and monthly roster views
- Group-based roster assignment

### 📊 Reports & Exports
- Attendance reports (daily, monthly, yearly)
- Leave balance reports
- Overtime summary reports
- Employee directory exports
- PDF generation via SnappyPdf (wkhtmltopdf)
- Excel exports via Maatwebsite Excel
- Customizable report templates

### 🔔 Notifications
- In-app notification system
- Leave application status updates
- Attendance adjustment alerts

### ⚙️ System Configuration
- Office and office type management
- Configurable office hours
- Public holiday calendar
- Weekly holiday settings
- Device management for attendance hardware
- Dynamic, database-driven sidebar navigation
- Role-based menu visibility

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Frontend** | Blade Templates, Bootstrap 5.3, Alpine.js |
| **Realtime** | Livewire / Volt |
| **Database** | MySQL 8.0 (dual-database: app data + attendance logs) |
| **Build** | Vite 7, PostCSS, Autoprefixer |
| **PDF Export** | SnappyPdf (wkhtmltopdf) |
| **Excel Export** | Maatwebsite Excel / PhpSpreadsheet |
| **Queue** | Database driver |
| **Auth** | Laravel Breeze |

## Architecture

```
app/
├── Console/          # Artisan commands
├── Exports/          # Excel/PDF export classes (9 exporters)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/         # Authentication (Breeze)
│   │   ├── Personnel/    # HR Admin controllers (employees, attendance, overtime, etc.)
│   │   ├── Roster/       # Roster time management
│   │   ├── Settings/     # System configuration (offices, holidays, devices)
│   │   └── TeamLead/     # Team lead approval workflows
│   └── ...
├── Models/           # 33 Eloquent models
├── Services/         # Business logic (Attendance, Menu, Notification, Report)
├── Jobs/             # Queued jobs
└── View/             # View components
```

## Prerequisites

- **PHP** ≥ 8.2 with extensions: `mbstring`, `xml`, `curl`, `mysql`, `gd`
- **Composer** ≥ 2.x
- **Node.js** ≥ 18.x with npm
- **MySQL** ≥ 8.0
- **wkhtmltopdf** (for PDF report generation) — [download here](https://wkhtmltopdf.org/downloads.html)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/AfifRayhan/Mir-HR-Management-System.git
cd Mir-HR-Management-System
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment

```bash
cp .env.example .env
php.exe artisan key:generate
```

Edit `.env` and configure your database connections:

```env
# Primary database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mir_hr
DB_USERNAME=root
DB_PASSWORD=your_password

# Attendance device database
DB_ATTENDANCE_CONNECTION=mysql
DB_ATTENDANCE_HOST=127.0.0.1
DB_ATTENDANCE_PORT=3306
DB_ATTENDANCE_DATABASE=attendance_analysis
DB_ATTENDANCE_USERNAME=root
DB_ATTENDANCE_PASSWORD=your_password
```

### 4. Create databases

```sql
CREATE DATABASE mir_hr;
CREATE DATABASE attendance_analysis;
```

### 5. Run migrations and seed data

```bash
php.exe artisan migrate
php.exe artisan db:seed
```

This seeds the system with sample data including departments, designations, employees, leave types, roster schedules, and holidays.

> **Note:** The employee seeder requires an Excel file at `storage/app/private/EmployeeSummary_transformed.xlsx`. If you don't have this file, the employee seeder will skip gracefully and you can add employees manually through the HR Admin panel.

### 6. Build frontend assets

```bash
npm run build
```

### 7. Start the application

**Option A — All-in-one dev server** (recommended):

```bash
composer dev
```

This concurrently starts:
- Laravel dev server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log viewer (`php artisan pail`)
- Vite dev server (`npm run dev`)

**Option B — Manual start:**

```bash
php.exe artisan serve
npm run dev
# In separate terminals:
php.exe artisan queue:listen
```

### 8. Access the application

Open `http://localhost:8000` in your browser.

**Default login credentials** (from seeders):

| Role | Email | Password |
|------|-------|----------|
| HR Admin | `hradmin@example.com` | `password` |
| Team Lead | *(auto-generated from employee data — department incharges)* | `password` |
| Employee | *(auto-generated from employee data)* | `password` |

> ⚠️ **Important:** All seeded users share the default password `password`. Change passwords immediately in any non-development environment.

## Project Structure

```
Mir-HR-Management-System/
├── app/                  # Application code
│   ├── Exports/          # Report export classes
│   ├── Http/Controllers/ # Route controllers
│   ├── Models/           # Eloquent models (33)
│   └── Services/         # Business logic services
├── config/               # Laravel configuration
├── database/
│   ├── migrations/       # Database schema
│   └── seeders/          # Demo data seeders (25)
├── public/               # Web root & static assets
├── resources/
│   └── views/            # Blade templates
├── routes/
│   └── web.php           # Application routes
├── storage/              # Logs, cache, uploads
└── tests/                # PHPUnit tests
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'feat: add your feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

## License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).
