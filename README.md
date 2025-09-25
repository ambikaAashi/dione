# Dione Menu Application

This repository contains a lightweight PHP + Bootstrap application for managing and displaying the Dione restaurant menu.

## Features

- **Admin panel** to import menu items from a CSV file and preview the current menu.
- **CSV support** with required `name` and `price` columns plus optional `description` and `category` columns.
- **Reset option** to clear the menu data when needed.
- **Responsive public menu** grouped by category for easy browsing on any device.

## Getting Started

1. Make sure you have PHP 8.1+ installed.
2. Start the built-in PHP development server from the project root:

   ```bash
   php -S localhost:8000
   ```

3. Visit `http://localhost:8000/admin.php` to upload a menu CSV file.
4. Browse `http://localhost:8000/index.php` to see the menu rendered for guests.

## CSV Format

```
name,description,price,category
Espresso,Rich espresso shot,$3.00,Beverages
Cappuccino,Espresso with steamed milk,$4.50,Beverages
Bruschetta,Grilled bread with toppings,$6.50,Starters
```

Only the `name` and `price` columns are required. Any additional columns in the CSV will be ignored.

## Data Storage

Uploaded menu data is stored in `data/menu.json`. This file is versioned for convenience but can be safely removed if you want to start from scratch.
