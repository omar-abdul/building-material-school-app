<?php
require_once 'connection.php';

// Set headers for different file types
function setHeaders($type, $filename = 'bmmss_backup') {
    switch ($type) {
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
            break;
        case 'excel':
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
            break;
        case 'sql':
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="'.$filename.'.sql"');
            break;
    }
}

// Export to CSV
function exportToCSV($conn) {
    setHeaders('csv');
    $output = fopen('php://output', 'w');
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        fputcsv($output, array("Table: $table"));
        
        // Get table data
        $stmt = $conn->query("SELECT * FROM $table");
        $firstRow = true;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($firstRow) {
                fputcsv($output, array_keys($row));
                $firstRow = false;
            }
            fputcsv($output, $row);
        }
        
        fputcsv($output, array("")); // Empty line between tables
    }
    
    fclose($output);
}

// Export to Excel
function exportToExcel($conn) {
    setHeaders('excel');
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<table border='1'>";
    
    foreach ($tables as $table) {
        echo "<tr><th colspan='100' style='background-color:#f2f2f2;'>Table: $table</th></tr>";
        
        // Get table data
        $stmt = $conn->query("SELECT * FROM $table");
        $firstRow = true;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($firstRow) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th style='background-color:#e6e6e6;'>$key</th>";
                }
                echo "</tr>";
                $firstRow = false;
            }
            
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        
        echo "<tr><td colspan='100'>&nbsp;</td></tr>"; // Empty row between tables
    }
    
    echo "</table>";
}

// Export to SQL (database dump)
function exportToSQL($conn) {
    setHeaders('sql');
    
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        // Table structure
        $createTable = $conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
        echo $createTable['Create Table'] . ";\n\n";
        
        // Table data
        $stmt = $conn->query("SELECT * FROM $table");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_map(function($col) use ($conn) {
                return "`$col`";
            }, array_keys($row));
            
            $values = array_map(function($value) use ($conn) {
                if (is_null($value)) {
                    return 'NULL';
                }
                return "'" . addslashes($value) . "'";
            }, array_values($row));
            
            echo "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
        
        echo "\n";
    }
}

// Handle form submission
if (isset($_POST['export'])) {
    $format = $_POST['format'];
    
    switch ($format) {
        case 'csv':
            exportToCSV($conn);
            exit;
        case 'excel':
            exportToExcel($conn);
            exit;
        case 'sql':
            exportToSQL($conn);
            exit;
        default:
            die("Invalid export format");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMMSS Database Backup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>BMMSS Database Backup</h1>
        <form method="post">
            <div class="form-group">
                <label for="format">Select Export Format:</label>
                <select id="format" name="format" required>
                    <option value="">-- Select Format --</option>
                    <option value="csv">CSV (Comma Separated Values)</option>
                    <option value="excel">Excel (XLS)</option>
                    <option value="sql">SQL (Database Dump)</option>
                </select>
            </div>
            <button type="submit" name="export">Export Database</button>
        </form>
    </div>
</body>
</html>