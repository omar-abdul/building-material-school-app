# BMMS - Building Material Management System

A comprehensive PHP-based backend system for managing building materials,
financial transactions, inventory, and business operations.

## 🚀 Quick Start

1. **Installation**: Run the installation wizard at `install.php`
2. **Documentation**: See [docs/README.md](docs/README.md) for complete guide
3. **Login**: Access the system after installation

## 📚 Documentation

All documentation has been consolidated in the **[docs/](docs/)** folder:

- **[docs/README.md](docs/README.md)** - Complete system documentation
- **[docs/INDEX.md](docs/INDEX.md)** - Documentation index
- Permission scripts for Linux/Unix and Windows
- API testing tools

## 🛠️ System Requirements

- **PHP**: 7.4+
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Extensions**: PDO, PDO_MySQL, JSON, Session

## 🔧 Installation

```bash
# Set permissions (Linux/Unix)
sudo chown -R www-data:www-data config/
sudo chmod -R 755 config/

# Visit install.php in your browser
# Follow the 2-step installation wizard
```

## 📁 Project Structure

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
├── docs/                   # Documentation & tools
└── database_structure.sql  # Database schema
```

---

**For complete documentation, see [docs/README.md](docs/README.md)**
