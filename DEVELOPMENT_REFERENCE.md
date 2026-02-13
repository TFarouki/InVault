# InVault - Development Reference

## Project Overview
- **Type**: POS & Inventory Management System
- **Stack**: Laravel 12 + Vue 3 + Inertia.js + Tailwind CSS
- **Database**: MySQL

## Key Changes Made

### 1. Navigation & UI
- Added full menu to sidebar (Suppliers, POS, Categories, etc.)
- Added ICE field to Customers and Suppliers
- Added business identifiers (ICE, RC, IF, Patente) to Settings
- Updated edit/delete buttons to icons with confirmation modals

### 2. Database
- New migration: `add_ice_to_entities` - adds ICE, RC, IF, tax_number fields
- Updated models: Customer, Supplier (added ice to fillable)

### 3. Controllers Updated
- CustomerController - added ICE validation
- SupplierController - added ICE validation
- SaleController - added destroy method
- PurchaseController - added destroy method

### 4. Tests Created
- DashboardTest.php
- ProductsTest.php
- CategoriesTest.php
- CustomersTest.php
- SuppliersTest.php
- SalesTest.php
- PurchasesTest.php
- InventoryTest.php
- ReportsTest.php
- SettingsTest.php
- CashDenominationsTest.php
- ShiftManagementTest.php
- UsersTest.php

### 5. Files Created
- PROJECT_PLANNING.md - Project roadmap and planning

## Commands

```bash
# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Run tests
php artisan test

# Start dev server
composer run dev
```

## Test Credentials
- Email: admin@admin.com
- Password: password

## Environment
- PHP: 8.4.18
- Node.js: 20.20.0
- Database: MySQL @ 10.70.110.5

---
*Last updated: Feb 2026*
