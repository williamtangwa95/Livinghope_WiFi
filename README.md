# LIVING HOPE Wi-Fi Business Management System

A production-grade Wi-Fi voucher management and customer captive portal system built on **Laravel 13** and **PHP 8.4**, meticulously preserving the exact, elegant UI design, dark-navy aesthetics, and responsive layout.

---

## 🌟 Key Features

### 1. Business Dashboard (`/`)
- **Key Financial Metrics**: Live revenue calculations in Tanzanian Shillings (TZS), vouchers sold today, active vouchers count, available stock.
- **Interactive SVG Revenue Chart**: 7-day revenue performance with smooth gradients and custom axis scaling.
- **Popular Packages**: Live percentage bars calculated dynamically against the top-selling Wi-Fi package.
- **Recent Voucher Activity**: Paginated voucher records with status badges (`active`, `available`, `expired`, `revoked`), instant client search, copy-to-clipboard, printable slips, and revoke actions.
- **Recent Activities Feed**: System audit logs of sales, sessions, and hardware diagnostic events.
- **Network Hardware Health**: Real-time status for the **TP-Link EAP110-Outdoor** access point and RADIUS authentication gateway.

### 2. Voucher Management (`/vouchers`)
- **Instant Voucher Sales**: Sell vouchers directly to customers (with phone number, customer name, cashier attribution, and optional immediate auto-activation).
- **Batch Generator**: Generate bulk vouchers (10, 25, 50, 100 codes formatted like `LH-XXXX-XXXX`) for inventory or thermal receipt printing.
- **Filter Tabs**: Filter by status (`All`, `Available`, `Active`, `Expired`, `Revoked`).
- **Thermal Slip Printing (`/vouchers/{id}/print`)**: Ready-to-print clean voucher slip for 58mm/80mm POS receipt printers or standard desktop printers.
- **CSV Data Export (`/export/vouchers`)**: Live streaming CSV export of all vouchers, packages, and sales records.

### 3. Wi-Fi Packages (`/packages`)
- Manage tariff plans (duration in hours, pricing in TZS, speed profiles, device limits, badge labels, and descriptions).
- Defaults pre-configured:
  - **6 Hours** · TZS 500 (`SHORT STAY`)
  - **24 Hours** · TZS 1,000 (`DAY PASS`)
  - **7 Days** · TZS 6,000 (`POPULAR CHOICE`)
  - **30 Days** · TZS 25,000 (`MONTHLY PASS`)

### 4. Active Sessions (`/sessions`)
- Real-time monitoring of connected client IP addresses, MAC addresses, device models, uploaded/downloaded bandwidth in MB/GB, and session duration.
- One-click client disconnection / session kick.

### 5. Network Hardware & Outdoor AP Setup (`/devices`)
- Pre-configured profiles for **TP-Link EAP110-Outdoor** and **Omada / RADIUS Gateway**.
- Live ping diagnostic test button with latency reporting.
- Built-in integration checklist for external captive portal and RADIUS redirection.

### 6. Customer Captive Portal (`/portal`)
- Customer-facing landing page matching the design from `index2.html`.
- Displays Wi-Fi packages, step-by-step connectivity instructions, and operator contact details.
- **Live Voucher Sign-in**: Customers enter their voucher code (e.g. `LH-8K4P-2M7Q`) and are instantly authenticated, activating their session and redirecting to the connected status page (`/portal/connect`).

---

## 🚀 Getting Started

### 1. Run the Development Server
```powershell
php artisan serve
```
The application will be accessible at:
- **Admin Workspace:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Customer Captive Portal:** [http://127.0.0.1:8000/portal](http://127.0.0.1:8000/portal)

### 2. Database Migrations & Seeders
The project uses SQLite by default (`database/database.sqlite`).
```powershell
php artisan migrate --seed
```

### 3. Running Automated Tests
```powershell
php artisan test
```

---

## 📂 System Architecture

- **Models**:
  - `App\Models\Package`: Wi-Fi package tariffs and bandwidth rules.
  - `App\Models\Voucher`: Voucher codes, amounts, statuses, expiration, and cashier records.
  - `App\Models\ActiveSession`: Live client connections, IP/MAC tracking, and byte counters.
  - `App\Models\NetworkDevice`: Outdoor APs and RADIUS server configurations.
  - `App\Models\Activity`: Live activity stream logs.
- **Controllers**:
  - `DashboardController`: Aggregates metrics, charts, package breakdowns, and vouchers.
  - `VoucherController`: Sales, bulk generation, ticket printing, and revocation.
  - `PackageController`: Tariff management and updates.
  - `SessionController`: Live connection monitoring and disconnection.
  - `NetworkDeviceController`: Hardware health and ping diagnostics.
  - `PortalController`: Customer portal and voucher authentication.
  - `ExportController`: Real-time CSV export.
- **Views**:
  - `resources/views/layouts/app.blade.php`: Master design layout.
  - `resources/views/dashboard.blade.php`: Overview workspace.
  - `resources/views/vouchers/`: Voucher management and printable slips.
  - `resources/views/packages/`: Tariff cards and editor.
  - `resources/views/sessions/`: Active client connections monitor.
  - `resources/views/devices/`: Network devices and setup checklist.
  - `resources/views/portal/`: Customer captive portal and login confirmation.
- **Styling**:
  - `public/css/dashboard.css`: Exact design system and tokens preserved from the original prototype.
