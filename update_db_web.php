<!DOCTYPE html>
<html>
<head>
    <title>Database Update</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Database Update</h1>
        
        <?php
        include 'config/db.php';

        try {
            echo "<div class='bg-white p-6 rounded-lg shadow-md mb-6'>";
            echo "<h2 class='text-xl font-semibold mb-4'>Database Connection Status</h2>";
            echo "<p class='text-green-600'>Successfully connected to database: " . htmlspecialchars($dbname) . "</p>";
            echo "</div>";

            // Use the database
            $pdo->exec("USE hotelmanagementsystem");
            
            // Check if the users table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() == 0) {
                echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6'>";
                echo "<p>Users table does not exist. Please run <a href='init.php' class='font-bold'>init.php</a> first.</p>";
                echo "</div>";
            } else {
                echo "<div class='bg-white p-6 rounded-lg shadow-md mb-6'>";
                echo "<h2 class='text-xl font-semibold mb-4'>Updating Database Schema</h2>";
                
                // Check if phone column exists
                $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
                if ($stmt->rowCount() == 0) {
                    // Add phone column
                    $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER password");
                    echo "<p class='text-green-600 mb-2'>✓ Added phone column to users table.</p>";
                } else {
                    echo "<p class='text-blue-600 mb-2'>→ Phone column already exists.</p>";
                }
                
                // Check if country column exists
                $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'country'");
                if ($stmt->rowCount() == 0) {
                    // Add country column
                    $pdo->exec("ALTER TABLE users ADD COLUMN country VARCHAR(100) AFTER phone");
                    echo "<p class='text-green-600 mb-2'>✓ Added country column to users table.</p>";
                } else {
                    echo "<p class='text-blue-600 mb-2'>→ Country column already exists.</p>";
                }
                
                // Check if all required columns exist now
                $stmt = $pdo->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $required_columns = ['id', 'name', 'email', 'password', 'phone', 'country', 'created_at'];
                $missing_columns = array_diff($required_columns, $columns);
                
                if (empty($missing_columns)) {
                    echo "<p class='text-green-600 font-bold mt-4'>✓ All required columns are now present in the users table.</p>";
                    echo "<p class='mt-2'>You can now try registering again.</p>";
                } else {
                    echo "<p class='text-red-600 font-bold mt-4'>✗ Still missing columns: " . htmlspecialchars(implode(', ', $missing_columns)) . "</p>";
                }
                
                echo "</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6'>";
            echo "<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Next Steps</h2>
            <ol class="list-decimal list-inside space-y-2">
                <li>Try registering again - it should work now</li>
                <li>If you still have issues, check that XAMPP MySQL service is running</li>
                <li>As a last resort, you can run <a href="init.php" class="text-blue-600 hover:underline">init.php</a> to recreate the entire database (note: this will delete existing data)</li>
            </ol>
            <div class="mt-4">
                <a href="register.php" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md inline-block">
                    Try Registration Again
                </a>
            </div>
        </div>
    </div>
</body>
</html>