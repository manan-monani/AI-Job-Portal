# AI Job Portal (Single Company)

A modern, high-performance Job Portal SPA for single-company hiring, built with **Laravel 12**, **Inertia.js v2**, **Vue 3**, and **Tailwind CSS v4**.

---

## 🔒 License & Ownership Notice

> **STRICTLY PROPRIETARY & CONFIDENTIAL**  
> Copyright © 2026 Manan Monani. All Rights Reserved.

This project is **NOT** open-source software. No rights, permissions, or licenses are granted to copy, reproduce, clone, modify, distribute, publish, or contribute to this repository. All public and open-source rights are explicitly revoked. See the full [LICENSE](file:///d:/Projects/6amtech%20project/AI-Job-Portal-Single-Company-demo/LICENSE) file for terms.

---

## ⚡ Quick Start Guide (Frontend Only)

To start only the frontend Vite development server for UI work and screenshots:

1. **Install Frontend Dependencies** (if not already installed):
   ```bash
   npm install
   ```

2. **Start Vite Development Server**:
   ```bash
   npm run dev
   ```
   *The frontend server runs at:* `http://localhost:5173`

---

## 🚀 Full Project Setup Guide (Frontend + Backend)

### Prerequisites

- **PHP**: ^8.2 (PHP 8.5 recommended)
- **Composer**: ^2.0
- **Node.js**: ^18.0 or ^20.0
- **Database**: MySQL / MariaDB (or SQLite for development)

### Step 1: Environment Setup

1. Copy environment template:
   ```bash
   cp .env.example .env
   ```
2. Generate Application Key:
   ```bash
   php artisan key:generate
   ```
3. Configure database settings in `.env`:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=job_portal
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Step 2: Database Migration & Seeding

```bash
php artisan migrate --seed
```

### Step 3: Running Development Servers

#### Option A: Run Servers Separately

- **Terminal 1 (Backend Server)**:
  ```bash
  php artisan serve
  ```
  *(Access at `http://127.0.0.1:8000`)*

- **Terminal 2 (Frontend Vite Server)**:
  ```bash
  npm run dev
  ```
  *(Access at `http://localhost:5173`)*

#### Option B: Run Concurrently (Recommended)

```bash
composer run dev
```
*(Runs artisan serve, queue listener, logs, and Vite concurrently)*

---

## 🛡️ Security Best Practices for GitHub

Before pushing this repository to GitHub:
- [x] Ensure `.env` is listed in `.gitignore` and **never** committed.
- [x] Verify no hardcoded API keys, database passwords, or secret tokens are present in tracked files.
- [x] Confirm `LICENSE` specifies proprietary ownership.

---

## 📁 Key Project Structure

```
├── app/                  # Laravel Application Core (Controllers, Models, Services)
├── config/               # Application & Branding Configurations
├── database/             # Migrations, Factories, and Seeders
├── resources/
│   ├── css/              # Tailwind CSS v4 Theme & Styles
│   └── js/               # Vue 3 Components & Inertia Pages
│       ├── Components/   # Reusable UI Components
│       ├── Layouts/      # Page Layouts (Admin, Guest, Candidate)
│       └── Pages/        # Inertia Page Views
├── routes/               # Web & API Route Definitions
└── vite.config.ts        # Vite Bundler Configuration
```
