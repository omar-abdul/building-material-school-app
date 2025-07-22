# BMMS (Building Material Management System) - Changelog

## [2025-07-22] - Major System Updates

### 🎯 **Core Features Implemented**

#### **1. Cost of Goods Sold (COGS) Implementation**

- **Database Changes:**
  - Added `Cost` column to `inventory` table (decimal(10,2) DEFAULT 0.00)
  - Updated existing inventory records to have default cost of 0.00

- **Financial Helper Updates:**
  - Added `calculateAverageCost()` method for weighted average costing
  - Added `updateInventoryWithCost()` method to update inventory with average
    costing
  - Added `createCOGSTransaction()` method to record COGS when items are sold
  - Modified `createSalesOrderTransaction()` to prevent duplicate transactions
  - Modified `createPurchaseOrderTransaction()` to prevent duplicate
    transactions

- **API Updates:**
  - Updated `api/orders/orders.php` to create COGS transactions after sales
  - Updated `api/purchase-orders/purchase-orders.php` to use average costing
  - Updated `api/financial/financial.php` to calculate COGS in financial
    overview

- **Frontend Updates:**
  - Added COGS card to Financial dashboard (`Financial/index.php`)
  - Updated financial overview to display COGS values (`Financial/financial.js`)

#### **2. Purchase Orders - Expense vs Payable Fix**

- **Problem:** Purchase orders were incorrectly recorded as expenses
- **Solution:** Modified purchase order transactions to create payables instead
  of expenses
- **Changes:**
  - Updated `FinancialHelper::createPurchaseOrderTransaction()` to use positive
    amounts (payables)
  - Updated financial overview calculation to exclude PURCHASE_ORDER from
    expenses
  - Deleted and recreated incorrect purchase order transactions in database

#### **3. Editable Price Fields**

- **Sales Orders:**
  - Removed `readonly` attribute from unit price input (`Orders/index.php`)
  - Added `min="0"` and `placeholder="Enter price"` attributes
  - Updated `Orders/orders.js` to clear price field and show suggested price as
    placeholder

- **Purchase Orders:**
  - Removed `readonly` attribute from unit price input
    (`PurchaseOrders/index.php`)
  - Added `min="0"` and `placeholder="Enter price"` attributes
  - Updated `PurchaseOrders/purchase-orders.js` to clear price field and show
    suggested price as placeholder

#### **4. Item Creation - Optional Fields**

- **Database Changes:**
  - Modified `items` table to make `SupplierID` and `RegisteredByEmployeeID`
    nullable
  - Updated foreign key constraints to use `ON DELETE SET NULL`

- **Frontend Updates:**
  - Removed supplier field from item creation form (`Items/index.php`)
  - Made employee field optional with updated label (`Items/index.php`)
  - Updated `Items/items.js` to remove supplier-related functionality
  - Updated validation to not require supplier or employee

- **API Updates:**
  - Modified `api/items/items.php` to use `LEFT JOIN` for suppliers and
    employees
  - Updated search logic to handle NULL supplier names
  - Made `SupplierID` and `RegisteredByEmployeeID` optional in POST/PUT requests

#### **5. Employee Salary Increase Fix**

- **Problem:** 0% salary increase was not allowed
- **Solution:** Added `value="0"` to salary increase input field
  (`Employees/index.php`)

### 🔧 **Technical Fixes**

#### **1. Session Manager Error Fix**

- **Problem:** "cannot ini_set to a session that is already started" error
- **Solution:** Modified `config/session_manager.php` to check session status
  before setting ini values
- **Changes:**
  - Added session status check in `init()` method
  - Only set ini values if session is not already active

#### **2. Transaction History Duplicate Display Fix**

- **Problem:** Transaction history showed duplicate entries in UI
- **Root Cause:** JavaScript field name mismatches and wrong API endpoint
- **Solution:**
  - Fixed API endpoint from `/transactions.php` to
    `/financial.php?action=getTransactions`
  - Updated field names to match API response (uppercase):
    - `transaction_id` → `TransactionID`
    - `transaction_date` → `TransactionDate`
    - `transaction_type` → `TransactionType`
    - `description` → `Description`
    - `amount` → `Amount`
    - `status` → `Status`
  - Fixed customer/supplier name display logic

#### **3. Financial Calculations Update**

- **Revenue:** Only SALES_ORDER transactions (positive amounts)
- **COGS:** Only INVENTORY_SALE transactions (negative amounts)
- **Other Expenses:** Only SALARY_PAYMENT and DIRECT_EXPENSE transactions
- **Net Profit:** Revenue - COGS - Other Expenses

### 📊 **Database Schema Updates**

#### **Inventory Table**

```sql
ALTER TABLE `inventory` ADD COLUMN `Cost` decimal(10,2) DEFAULT 0.00 AFTER `Quantity`;
```

#### **Items Table**

```sql
ALTER TABLE `items` MODIFY `SupplierID` int(11) DEFAULT NULL;
ALTER TABLE `items` MODIFY `RegisteredByEmployeeID` int(11) DEFAULT NULL;
ALTER TABLE `items` DROP FOREIGN KEY `items_ibfk_2`;
ALTER TABLE `items` DROP FOREIGN KEY `items_ibfk_3`;
ALTER TABLE `items`
ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`) ON DELETE SET NULL,
ADD CONSTRAINT `items_ibfk_3` FOREIGN KEY (`RegisteredByEmployeeID`) REFERENCES `employees` (`EmployeeID`) ON DELETE SET NULL;
```

### 🎨 **UI/UX Improvements**

#### **Financial Dashboard**

- Added COGS card with inventory icon
- Renamed "Total Expenses" to "Other Expenses" for clarity
- Updated financial overview to show correct calculations

#### **Order Forms**

- Made price fields editable with clear placeholders
- Added suggested price display without preventing manual input
- Improved user experience for price entry

#### **Item Management**

- Simplified item creation by removing mandatory supplier requirement
- Made employee registration optional
- Updated form labels for clarity

### 🔍 **Bug Fixes**

1. **Transaction Duplication:** Fixed duplicate financial transactions for sales
   and purchase orders
2. **Price Field Read-only:** Made price fields editable in both sales and
   purchase orders
3. **COGS Not Recorded:** Implemented proper COGS recording for sales
   transactions
4. **Purchase Orders as Expenses:** Fixed purchase orders to create payables
   instead of expenses
5. **Session Errors:** Prevented ini_set errors when session is already active
6. **0% Salary Increase:** Allowed 0% salary increase input
7. **Transaction History Display:** Fixed duplicate entries and field name
   issues

### 📈 **Financial Impact**

- **Proper COGS Accounting:** Sales now correctly record cost of goods sold
- **Accurate Net Profit:** Financial reports now show true profitability
- **Correct Expense Classification:** Purchase orders no longer inflate expenses
- **Weighted Average Costing:** Inventory costs are calculated using proper
  accounting method

### 🚀 **Performance Improvements**

- **Transaction Deduplication:** Prevented duplicate financial transactions
- **Efficient Queries:** Updated JOIN statements to handle nullable foreign keys
- **Session Management:** Improved session handling to prevent errors

---

## **For Future Development**

### **Key Principles Established:**

1. **Purchase Orders** → Create payables (not expenses)
2. **Sales Orders** → Create revenue + COGS
3. **Inventory Costing** → Use weighted average method
4. **Financial Reports** → Revenue - COGS - Other Expenses = Net Profit

### **Database Conventions:**

- Use `LEFT JOIN` for optional foreign key relationships
- Handle NULL values in search queries with `COALESCE()`
- Use proper transaction types for financial categorization

### **API Patterns:**

- Check for existing transactions before creating new ones
- Use consistent field naming (uppercase for database fields)
- Implement proper error handling and rollback mechanisms
