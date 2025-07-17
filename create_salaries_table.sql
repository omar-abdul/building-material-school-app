-- Create Salaries Table
-- This table stores salary records for employees

CREATE TABLE IF NOT EXISTS salaries (
    SalaryID INT PRIMARY KEY AUTO_INCREMENT,
    EmployeeID INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    AdvanceSalary DECIMAL(10,2) DEFAULT 0.00,
    NetSalary DECIMAL(10,2) NOT NULL,
    PaymentMethod VARCHAR(50) NOT NULL,
    PaymentDate DATE NOT NULL,
    Status ENUM('Paid', 'Pending', 'Cancelled') DEFAULT 'Paid',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (EmployeeID) REFERENCES employees(EmployeeID) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_employee_id (EmployeeID),
    INDEX idx_payment_date (PaymentDate),
    INDEX idx_status (Status),
    INDEX idx_employee_payment_date (EmployeeID, PaymentDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for testing
INSERT INTO salaries (EmployeeID, Amount, AdvanceSalary, NetSalary, PaymentMethod, PaymentDate, Status) VALUES
(1, 2500.00, 200.00, 2300.00, 'Bank Transfer', '2024-01-15', 'Paid'),
(2, 2200.00, 0.00, 2200.00, 'Cash', '2024-01-15', 'Paid'),
(3, 2800.00, 300.00, 2500.00, 'Online', '2024-01-15', 'Paid'),
(1, 2500.00, 150.00, 2350.00, 'Bank Transfer', '2024-02-15', 'Paid'),
(2, 2200.00, 100.00, 2100.00, 'Cash', '2024-02-15', 'Pending'),
(3, 2800.00, 0.00, 2800.00, 'Online', '2024-02-15', 'Paid'); 