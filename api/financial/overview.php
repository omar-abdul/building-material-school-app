<?php

/**
 * Financial Overview API
 * Provides summary financial data for the dashboard
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/FinancialHelper.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getInstance()->getConnection();
    $financialHelper = new FinancialHelper($pdo);

    // Get current month and previous month for comparison
    $currentMonth = date('Y-m');
    $previousMonth = date('Y-m', strtotime('-1 month'));

    // Calculate total revenue (sales orders and direct income)
    $revenueQuery = "
        SELECT 
            COALESCE(SUM(CASE WHEN TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE') THEN Amount ELSE 0 END), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE') AND DATE_FORMAT(TransactionDate, '%Y-%m') = ? THEN Amount ELSE 0 END), 0) as current_month_revenue,
            COALESCE(SUM(CASE WHEN TransactionType IN ('SALES_ORDER', 'DIRECT_INCOME', 'INVENTORY_SALE') AND DATE_FORMAT(TransactionDate, '%Y-%m') = ? THEN Amount ELSE 0 END), 0) as previous_month_revenue
        FROM financial_transactions 
        WHERE Status = 'Completed'
    ";

    $revenueStmt = $pdo->prepare($revenueQuery);
    $revenueStmt->execute([$currentMonth, $previousMonth]);
    $revenueData = $revenueStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate total expenses (purchases, salaries, and direct expenses)
    $expenseQuery = "
        SELECT 
            COALESCE(SUM(CASE WHEN TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE') THEN ABS(Amount) ELSE 0 END), 0) as total_expenses,
            COALESCE(SUM(CASE WHEN TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE') AND DATE_FORMAT(TransactionDate, '%Y-%m') = ? THEN ABS(Amount) ELSE 0 END), 0) as current_month_expenses,
            COALESCE(SUM(CASE WHEN TransactionType IN ('PURCHASE_ORDER', 'SALARY_PAYMENT', 'DIRECT_EXPENSE', 'INVENTORY_PURCHASE') AND DATE_FORMAT(TransactionDate, '%Y-%m') = ? THEN ABS(Amount) ELSE 0 END), 0) as previous_month_expenses
        FROM financial_transactions 
        WHERE Status = 'Completed'
    ";

    $expenseStmt = $pdo->prepare($expenseQuery);
    $expenseStmt->execute([$currentMonth, $previousMonth]);
    $expenseData = $expenseStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate net profit
    $totalRevenue = $revenueData['total_revenue'];
    $totalExpenses = $expenseData['total_expenses'];
    $netProfit = $totalRevenue - $totalExpenses;

    // Calculate pending payments
    $pendingQuery = "
        SELECT COALESCE(SUM(ABS(Amount)), 0) as pending_payments
        FROM financial_transactions 
        WHERE Status = 'Pending'
    ";

    $pendingStmt = $pdo->prepare($pendingQuery);
    $pendingStmt->execute();
    $pendingData = $pendingStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate growth percentages
    $revenueGrowth = $revenueData['previous_month_revenue'] > 0
        ? (($revenueData['current_month_revenue'] - $revenueData['previous_month_revenue']) / $revenueData['previous_month_revenue']) * 100
        : 0;

    $expenseGrowth = $expenseData['previous_month_expenses'] > 0
        ? (($expenseData['current_month_expenses'] - $expenseData['previous_month_expenses']) / $expenseData['previous_month_expenses']) * 100
        : 0;

    $profitGrowth = ($revenueData['current_month_revenue'] - $expenseData['current_month_expenses']) -
        ($revenueData['previous_month_revenue'] - $expenseData['previous_month_expenses']);
    $profitGrowthPercent = ($revenueData['previous_month_revenue'] - $expenseData['previous_month_expenses']) > 0
        ? ($profitGrowth / ($revenueData['previous_month_revenue'] - $expenseData['previous_month_expenses'])) * 100
        : 0;

    $response = [
        'success' => true,
        'data' => [
            'total_revenue' => (float)$totalRevenue,
            'total_expenses' => (float)$totalExpenses,
            'net_profit' => (float)$netProfit,
            'pending_payments' => (float)$pendingData['pending_payments'],
            'growth' => [
                'revenue_growth' => round($revenueGrowth, 2),
                'expense_growth' => round($expenseGrowth, 2),
                'profit_growth' => round($profitGrowthPercent, 2)
            ],
            'current_month' => [
                'revenue' => (float)$revenueData['current_month_revenue'],
                'expenses' => (float)$expenseData['current_month_expenses'],
                'profit' => (float)($revenueData['current_month_revenue'] - $expenseData['current_month_expenses'])
            ]
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading financial overview: ' . $e->getMessage()
    ]);
}
