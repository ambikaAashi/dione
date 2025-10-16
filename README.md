# Dione Menu Application

This repository contains a lightweight PHP application with a CSV-powered admin panel and a Tailwind CSS guest-facing menu for the Dione restaurant.

## Features

- **Admin panel** to import menu items from a CSV file and preview the current menu.
- **CSV support** with required `name` and `price` columns plus optional descriptive fields.
- **Reset option** to clear the menu data when needed.
- **Responsive public menu** with a dark, card-based layout, live search, category chips, and dietary filters.

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
name,description,price,category,type,spicy,cuisine,tags,unit,addons
Espresso,Rich espresso shot,3.00,Beverages,Veg,0,Cafe,"hot;signature",₹,
Cappuccino,Espresso with steamed milk,4.50,Beverages,Veg,0,Cafe,"hot;classic",₹,Extra Shot|0.80
Bruschetta,Grilled bread with toppings,6.50,Starters,Veg,1,Italian,"shareable;popular",₹,
```

Only the `name` and `price` columns are required. Optional fields include:

- `description` – Text displayed under the item name.
- `category` – Used to group items and power the section navigation.
- `type` – Accepts `Veg`, `Non-Veg`, or `Egg` to display dietary indicators.
- `spicy` – Numeric 0–3 to render spice level flames.
- `cuisine` – Short cuisine or style label.
- `tags` – Semicolon or comma separated tags for quick chips and improved search.
- `unit` – Currency symbol or unit prefix displayed before the price.
- `addons` – Semicolon separated list of `Name|Price` pairs for optional add-ons.

## Data Storage

Uploaded menu data is stored in `data/menu.json`. This file is versioned for convenience but can be safely removed if you want to start from scratch.
