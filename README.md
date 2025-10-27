# 💊 Pharmacy Billing Software using PHP

A complete **Pharmacy Management & Billing System** built with **PHP, MySQL, jQuery, and Bootstrap 5**.  
This system allows pharmacies to manage their inventory, handle billing operations, and maintain sales records easily and efficiently.

---

## 🚀 Features

### 🧮 Billing System
- Search medicines instantly using **live autocomplete (jQuery UI)**
- Add medicines to cart dynamically
- Real-time calculation of subtotal, discount, and grand total
- Generate bills and automatically update stock

### 💊 Inventory Management
- Add, edit, and delete medicines
- Auto-update medicine stock after each sale
- Track expiry dates and batch numbers
- Manage medicine cost and MRP pricing

### 🧾 Sales Reports
- View all previous bills
- Filter by date range
- Track total sales and discount summaries

### ⚙️ Admin Features
- User-friendly dashboard
- Responsive UI with **Bootstrap 5**
- AJAX-powered system for a seamless experience

---

## 🗃️ Database Structure

### Tables Included:
1. **`medicines`** — Stores medicine details (name, batch, expiry, stock, MRP, cost).
2. **`sales`** — Contains bill/sale information.
3. **`sale_items`** — Each bill’s item-wise sale record.

A ready SQL dump file is provided:  
📄 [`pharmacy_db.sql`](pharmacy_db.sql)

---

## 🛠️ Technologies Used
| Component | Technology |
|------------|-------------|
| Frontend | HTML5, CSS3, Bootstrap 5, jQuery, jQuery UI |
| Backend | PHP (Procedural) |
| Database | MySQL |
| AJAX | For asynchronous CRUD operations |
| Version Control | Git & GitHub |

---

## ⚙️ Installation Steps

1. **Clone this repository:**
   ```bash
   git clone https://github.com/vaustech/pharmacy-Billing-Software-using-PHP.git
