# BMMS - Building Material Management System

A comprehensive PHP-based backend system for managing building materials,
financial transactions, inventory, and business operations.

## 📋 Table of Contents

1. [Quick Start Installation](#-quick-start-installation)
2. [System Overview](#-system-overview)
3. [Features](#-features)
4. [Technical Architecture](#-technical-architecture)
5. [API Documentation](#-api-documentation)
6. [Troubleshooting](#-troubleshooting)
7. [Development & Maintenance](#-development--maintenance)

---

## 🚀 Quick Start Installation

### Prerequisites

- **Web Server**: Apache/Nginx with PHP 7.4+
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **PHP Extensions**: PDO, PDO_MySQL, JSON, Session

### Installation Steps

#### 1. **Download & Setup**

```bash
# Clone or download the project
cd /path/to/backend

# Set proper permissions (Linux/Unix)
sudo chown -R www-data:www-data config/
sudo chmod -R 755 config/

# Windows users: Run fix_permissions.bat as Administrator
```

#### 2. **Run Installation Wizard**

- Visit: `http://localhost/backend/install.php`
- **Step 1**: Database configuration
  - Host: `localhost`
  - Database: Create new or use existing
  - Username/Password: Your MySQL credentials
- **Step 2**: Admin user setup
  - Username, Password, Email
- **Automatic**: Tables created, files generated, redirect to login

#### 3. **Access System**

- Login with admin credentials
- Start managing your building materials!

---

## 🏗️ System Overview

BMMS is a modular PHP application designed for building material businesses to
manage:

- **Inventory Management**: Items, categories, suppliers
- **Financial Operations**: Sales, purchases, expenses, salaries
- **Customer Relations**: Orders, payments, balances
- **Employee Management**: Salaries, roles, permissions
- **Reporting**: Financial overviews, transaction history

### Core Modules

- **Financial Management**: Complete financial transaction tracking
- **Cash/Wallet**: Real-time balance management
- **Inventory**: Stock tracking and management
- **Orders**: Customer order processing
- **Users**: Role-based access control

---

## ✨ Features

### 🔐 Authentication & Security

- **Role-based Access**: Admin/User permissions
- **Password Security**: Bcrypt hashing
- **Session Management**: Secure user sessions
- **CSRF Protection**: Cross-site request forgery prevention

### 💰 Financial Management

- **Transaction Types**: Sales, purchases, salaries, expenses, inventory
- **Balance Tracking**: Customer, supplier, cash, wallet balances
- **Real-time Updates**: Automatic balance calculations
- **Period Filtering**: Date range financial reports

### 📊 Reporting & Analytics

- **Financial Overview**: Revenue, expenses, net income
- **COGS Calculation**: Cost of goods sold tracking
- **Transaction History**: Detailed financial records
- **Balance Reports**: Current financial positions

### 🛠️ System Administration

- **WordPress-like Installation**: Simple setup wizard
- **Cross-platform Support**: Linux, Windows, macOS
- **Automatic Permissions**: Platform-specific file handling
- **Database Management**: Dynamic table creation

---

## 🏛️ Technical Architecture

### **Backend Stack**

- **Language**: PHP 7.4+
- **Database**: MySQL/MariaDB with PDO
- **Architecture**: Modular, API-first design
- **Security**: Session-based authentication

### **Database Design**

- **Normalized Schema**: Efficient data relationships
- **Foreign Keys**: Referential integrity
- **Indexing**: Optimized query performance
- **Transactions**: ACID compliance

### **File Structure**

```
backend/
├── api/                    # REST API endpoints
├── config/                 # Configuration files
├── dashboard/              # Main dashboard
├── Financial/              # Financial management
├── Cash/                   # Cash/wallet management
├── Items/                  # Inventory management
├── Orders/                 # Order processing
├── Users/                  # User management
├── install.php             # Installation wizard
├── index.php               # Main entry point
└── docs/                   # Documentation
```

---

## 🔌 API Documentation

### **Financial API** (`/api/financial/`)

- `GET /financial-overview` - Financial summary
- `POST /add-transaction` - Create transaction
- `GET /transaction-history` - Transaction list
- `GET /customer-balances` - Customer balance info
- `GET /supplier-balances` - Supplier balance info

### **Cash API** (`/api/cash/`)

- `GET /balances` - Cash/wallet balances
- `GET /transactions` - Transaction history
- `POST /record-expense` - Record cash expense

### **Authentication**

- **Login**: `POST /Login/`
- **Logout**: `GET /Logout/`
- **Session**: Automatic session management

---

## 🐛 Troubleshooting

### **Common Issues & Solutions**

#### **Permission Denied Errors**

```bash
# Linux/Unix
sudo chown -R www-data:www-data config/
sudo chmod -R 755 config/

# Windows (Run as Administrator)
fix_permissions.bat
```

#### **Database Connection Issues**

- Verify MySQL/MariaDB is running
- Check credentials in installation form
- Ensure database exists or can be created
- Check PHP PDO extension is enabled

#### **Installation Problems**

- **File Creation**: Check web server write permissions
- **Database**: Verify SQL execution permissions
- **Redirects**: Check Apache/Nginx configuration

#### **Financial Data Issues**

- **COGS Not Showing**: Check inventory sale transactions
- **Balances Wrong**: Verify transaction statuses are 'Completed'
- **Filters Not Working**: Check JavaScript console for errors

### **Debug Tools**

- **Installation Debug**: `index.php?debug=1`
- **Error Logs**: Check web server error logs
- **PHP Errors**: Enabled during installation

---

## 🛠️ Development & Maintenance

### **Code Structure**

- **Modular Design**: Each feature in separate directory
- **API-First**: Backend operations via REST APIs
- **Frontend**: HTML/CSS/JavaScript with FontAwesome
- **Database**: PDO with prepared statements

### **Key Files**

- **`config/database.php`**: Database connection (generated)
- **`config/init.php`**: Application initialization
- **`api/*/`**: API endpoints for each module
- **`includes/FinancialHelper.php`**: Business logic helpers

### **Maintenance Tasks**

- **Regular Backups**: Database and configuration files
- **Security Updates**: Keep PHP and dependencies updated
- **Performance**: Monitor database query performance
- **Logs**: Review error logs regularly

### **Customization**

- **Themes**: Modify CSS in module directories
- **Features**: Add new API endpoints in `api/` folder
- **Database**: Extend schema in `database_structure.sql`
- **Permissions**: Modify role-based access in auth system

---

## 📚 Additional Resources

### **Scripts & Tools**

- **`fix_permissions.sh`**: Linux/Unix permission fixer
- **`fix_permissions.bat`**: Windows permission fixer
- **`test_apis.php`**: API testing and validation

### **Database Files**

- **`database_structure.sql`**: Clean table structure (no data)
- **`schema.sql`**: Original schema with sample data

### **Installation Files**

- **`install.php`**: WordPress-like installation wizard
- **`config/database_template.php`**: Database configuration template

---

## 🤝 Support & Contributing

### **Getting Help**

1. Check this documentation first
2. Review troubleshooting section
3. Check web server error logs
4. Verify system requirements

### **System Requirements**

- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Extensions**: PDO, PDO_MySQL, JSON, Session

### **Browser Support**

- **Modern Browsers**: Chrome, Firefox, Safari, Edge
- **JavaScript**: Required for full functionality
- **Responsive Design**: Mobile and desktop compatible

---

**Note**: This system automatically handles cross-platform compatibility and
permission management. The installation wizard guides you through the entire
setup process.
