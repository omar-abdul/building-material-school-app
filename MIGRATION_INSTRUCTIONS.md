# Database Migration Instructions

## 🎯 Goal

Fix the issue where creating one order with multiple items creates multiple
separate orders instead of one order with multiple items.

## 🔧 What This Fix Does

- **Separates orders from order items** into different tables
- **One order** can now have **multiple items** properly grouped
- **Cleaner database structure** with proper relationships
- **Eliminates duplicate order creation**

## 📋 Step-by-Step Implementation

### **Step 1: Backup Your Database**

```bash
mysqldump -u root -p bmmss > bmmss_backup_$(date +%Y%m%d_%H%M%S).sql
```

### **Step 2: Create New Tables**

```bash
mysql -u root -p bmmss < database_structure_fixed.sql
```

### **Step 3: Migrate Existing Data**

```bash
mysql -u root -p bmmss < migrate_to_new_structure.sql
```

### **Step 4: Verify Migration**

```sql
-- Check purchase orders
SELECT COUNT(*) as purchase_orders FROM purchase_orders_main;
SELECT COUNT(*) as purchase_items FROM purchase_order_items;

-- Check sales orders  
SELECT COUNT(*) as sales_orders FROM sales_orders_main;
SELECT COUNT(*) as sales_items FROM sales_order_items;
```

### **Step 5: Test New Structure**

1. Create a purchase order with multiple items
2. Create a sales order with multiple items
3. Verify only ONE order is created with multiple items

### **Step 6: Remove Old Tables (Optional)**

```sql
-- Only after confirming everything works
DROP TABLE purchase_orders;
DROP TABLE orders;
```

## 🏗️ New Database Structure

### **Purchase Orders**

```
purchase_orders_main (1 order)
├── PurchaseOrderID (unique)
├── SupplierID, EmployeeID, OrderDate, Status
└── purchase_order_items (multiple items)
    ├── ItemID, Quantity, UnitPrice, TotalAmount
    └── References PurchaseOrderID
```

### **Sales Orders**

```
sales_orders_main (1 order)
├── OrderID (unique)
├── CustomerID, EmployeeID, OrderDate, Status
└── sales_order_items (multiple items)
    ├── ItemID, Quantity, UnitPrice, TotalAmount
    └── References OrderID
```

## ✅ Benefits After Migration

1. **One order = Multiple items** (proper grouping)
2. **Cleaner database structure** (normalized)
3. **Better performance** (proper indexing)
4. **Easier maintenance** (clear relationships)
5. **Professional structure** (industry standard)

## 🚨 Important Notes

- **Backup first** - Always backup before major changes
- **Test thoroughly** - Verify all functionality works
- **Update frontend** - Frontend code has been updated
- **Check COGS** - COGS creation should now work properly

## 🔍 Troubleshooting

### **If Migration Fails:**

1. Check database permissions
2. Verify table names match exactly
3. Check for foreign key constraints
4. Review error messages

### **If Orders Don't Show:**

1. Check table joins in API queries
2. Verify data was migrated correctly
3. Check API error logs

## 🎉 Expected Result

After migration:

- ✅ **One purchase order** with multiple items = **One order record**
- ✅ **One sales order** with multiple items = **One order record**
- ✅ **Items properly grouped** under their respective orders
- ✅ **COGS calculations** working correctly
- ✅ **Clean, professional database structure**

---

**Need Help?** Check the error logs and verify each step was completed
successfully.
