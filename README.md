# InVault - Modern POS & Inventory Management System

InVault is a premium, powerful, and easy-to-use Point of Sale (POS) and Inventory Management System built with **Laravel**, **Vue.js 3**, and **Tailwind CSS**. It is designed to streamline business operations with a focus on speed, aesthetics, and reliability.

## 🚀 Features

- **Advanced POS Terminal**: Fast checkout, barcode scanning support, and cart management.
- **Inventory Control**: Real-time stock tracking, stock movements, and low stock alerts.
- **Sales & Purchase Management**: Comprehensive history and tracking for all transactions.
- **Shift Management**: Cash drawer control with opening/closing counts and discrepancies tracking.
- **Multi-language Support**: Seamless switching between Arabic (RTL), French, and English.
- **Settings & Customization**: Business identity, tax management, and currency settings.
- **Responsive Design**: Premium dark mode support and mobile-friendly interface.
- **Floating Calculator**: Draggable, integrated calculator for quick mathematical operations.

## 🛠 Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 & Inertia.js
- **Styling**: Tailwind CSS
- **Database**: MySQL / MariaDB
- **Internationalization**: laravel-vue-i18n

## 📦 Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/TFarouki/InVault.git
   cd InVault
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**:
   Update your `.env` file with your database credentials.

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=CashDenominationSeeder
   ```

6. **Serve the application**:
   ```bash
   php artisan serve
   npm run dev
   ```

## 📄 License

This project is proprietary and for internal use only.

---
Developed with ❤️ by the InVault Team.
