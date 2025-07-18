<?php

/**
 * Financial Report API
 * Generates comprehensive financial reports with charts and analytics
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/utils.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    // Get report period
    $period = isset($_GET['period']) ? $_GET['period'] : 'month';

    // Calculate date range based on period
    $endDate = date('Y-m-d');
    switch ($period) {
        case 'month':
            $startDate = date('Y-m-01');
            break;
        case 'quarter':
            $quarter = ceil(date('n') / 3);
            $startDate = date('Y-') . (($quarter - 1) * 3 + 1) . '-01';
            break;
        case 'year':
            $startDate = date('Y-01-01');
            break;
        case 'custom':
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
            break;
        default:
            $startDate = date('Y-m-01');
    }

    // Get revenue summary
    $revenueQuery = "
        SELECT 
            COALESCE(SUM(Amount), 0) as total,
            COUNT(*) as sales_count,
            COALESCE(AVG(Amount), 0) as average_order
        FROM financial_transactions 
        WHERE TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE')
        AND Status = 'Completed'
        AND TransactionDate BETWEEN ? AND ?
    ";

    $revenueData = $db->fetchOne($revenueQuery, [$startDate, $endDate]);

    // Get expense summary
    $expenseQuery = "
        SELECT 
            COALESCE(SUM(CASE WHEN TransactionType = 'PURCHASE_ORDER' THEN ABS(Amount) ELSE 0 END), 0) as purchase_total,
            COUNT(CASE WHEN TransactionType = 'PURCHASE_ORDER' THEN 1 END) as purchase_count,
            COALESCE(SUM(CASE WHEN TransactionType = 'SALARY_PAYMENT' THEN ABS(Amount) ELSE 0 END), 0) as salaries
        FROM financial_transactions 
        WHERE TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE')
        AND Status = 'Completed'
        AND TransactionDate BETWEEN ? AND ?
    ";

    $expenseData = $db->fetchOne($expenseQuery, [$startDate, $endDate]);

    // Calculate totals
    $totalRevenue = $revenueData['total'];
    $totalExpenses = $expenseData['purchase_total'] + $expenseData['salaries'];
    $netProfit = $totalRevenue - $totalExpenses;
    $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

    // Get previous period for growth calculation
    $prevStartDate = date('Y-m-d', strtotime($startDate . ' -1 ' . $period));
    $prevEndDate = date('Y-m-d', strtotime($startDate . ' -1 day'));

    $prevRevenueQuery = "
        SELECT COALESCE(SUM(Amount), 0) as total
        FROM financial_transactions 
        WHERE TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE')
        AND Status = 'Completed'
        AND TransactionDate BETWEEN ? AND ?
    ";

    $prevRevenue = $db->fetchOne($prevRevenueQuery, [$prevStartDate, $prevEndDate])['total'];

    $prevExpenseQuery = "
        SELECT COALESCE(SUM(ABS(Amount)), 0) as total
        FROM financial_transactions 
        WHERE TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE')
        AND Status = 'Completed'
        AND TransactionDate BETWEEN ? AND ?
    ";

    $prevExpenses = $db->fetchOne($prevExpenseQuery, [$prevStartDate, $prevEndDate])['total'];

    $prevProfit = $prevRevenue - $prevExpenses;
    $profitGrowth = $prevProfit > 0 ? (($netProfit - $prevProfit) / $prevProfit) * 100 : 0;

    // Get monthly trends for charts
    $monthlyQuery = "
        SELECT 
            DATE_FORMAT(TransactionDate, '%Y-%m') as month,
            COALESCE(SUM(CASE WHEN TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE') THEN Amount ELSE 0 END), 0) as revenue,
            COALESCE(SUM(CASE WHEN TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE') THEN ABS(Amount) ELSE 0 END), 0) as expenses
        FROM financial_transactions 
        WHERE Status = 'Completed'
        AND TransactionDate BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(TransactionDate, '%Y-%m')
        ORDER BY month ASC
    ";

    $monthlyData = $db->fetchAll($monthlyQuery, [$startDate, $endDate]);

    // Format monthly data for charts
    $monthlyLabels = [];
    $monthlyRevenue = [];
    $monthlyExpenses = [];

    foreach ($monthlyData as $month) {
        $monthlyLabels[] = date('M Y', strtotime($month['month'] . '-01'));
        $monthlyRevenue[] = (float)$month['revenue'];
        $monthlyExpenses[] = (float)$month['expenses'];
    }

    // Get transaction distribution
    $distributionQuery = "
        SELECT 
            TransactionType,
            COUNT(*) as count,
            COALESCE(SUM(ABS(Amount)), 0) as total_amount
        FROM financial_transactions 
        WHERE Status = 'Completed'
        AND TransactionDate BETWEEN ? AND ?
        GROUP BY TransactionType
    ";

    $distributionData = $db->fetchAll($distributionQuery, [$startDate, $endDate]);

    // Format distribution data for charts
    $distributionLabels = [];
    $distributionCounts = [];
    $distributionAmounts = [];

    foreach ($distributionData as $dist) {
        $distributionLabels[] = ucfirst(str_replace('_', ' ', strtolower($dist['TransactionType'])));
        $distributionCounts[] = (int)$dist['count'];
        $distributionAmounts[] = (float)$dist['total_amount'];
    }

    // Get top customers and suppliers
    $topCustomersQuery = "
        SELECT 
            c.CustomerName as name,
            COALESCE(SUM(ft.Amount), 0) as total_amount,
            COUNT(*) as transaction_count
        FROM customers c
        LEFT JOIN financial_transactions ft ON c.CustomerID = ft.CustomerID 
            AND ft.TransactionType IN ('SALES_ORDER', 'INVENTORY_SALE')
            AND ft.Status = 'Completed'
            AND ft.TransactionDate BETWEEN ? AND ?
        GROUP BY c.CustomerID, c.CustomerName
        HAVING total_amount > 0
        ORDER BY total_amount DESC
        LIMIT 5
    ";

    $topCustomers = $db->fetchAll($topCustomersQuery, [$startDate, $endDate]);

    $topSuppliersQuery = "
        SELECT 
            s.SupplierName as name,
            COALESCE(SUM(ABS(ft.Amount)), 0) as total_amount,
            COUNT(*) as transaction_count
        FROM suppliers s
        LEFT JOIN financial_transactions ft ON s.SupplierID = ft.SupplierID 
            AND ft.TransactionType IN ('PURCHASE_ORDER', 'INVENTORY_PURCHASE')
            AND ft.Status = 'Completed'
            AND ft.TransactionDate BETWEEN ? AND ?
        GROUP BY s.SupplierID, s.SupplierName
        HAVING total_amount > 0
        ORDER BY total_amount DESC
        LIMIT 5
    ";

    $topSuppliers = $db->fetchAll($topSuppliersQuery, [$startDate, $endDate]);

    $response = [
        'success' => true,
        'data' => [
            'period' => [
                'type' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'revenue' => [
                'total' => (float)$totalRevenue,
                'sales_count' => (int)$revenueData['sales_count'],
                'average_order' => (float)$revenueData['average_order']
            ],
            'expenses' => [
                'total' => (float)$totalExpenses,
                'purchase_total' => (float)$expenseData['purchase_total'],
                'purchase_count' => (int)$expenseData['purchase_count'],
                'salaries' => (float)$expenseData['salaries']
            ],
            'profit' => [
                'net' => (float)$netProfit,
                'margin' => round($profitMargin, 2),
                'growth' => round($profitGrowth, 2)
            ],
            'monthly_trends' => [
                'labels' => $monthlyLabels,
                'revenue' => $monthlyRevenue,
                'expenses' => $monthlyExpenses
            ],
            'transaction_distribution' => [
                'labels' => $distributionLabels,
                'counts' => $distributionCounts,
                'amounts' => $distributionAmounts
            ],
            'top_customers' => $topCustomers,
            'top_suppliers' => $topSuppliers
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error generating financial report: ' . $e->getMessage()
    ]);
}
