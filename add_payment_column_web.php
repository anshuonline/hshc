<!DOCTYPE html>
<html>
<head>
    <title>Add Payment Status Column</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Add Payment Status Column</h1>
        
        <?php
        include 'config/db.php';

        try {
            echo "<div class='bg-white p-6 rounded-lg shadow-md mb-6'>";
            echo "<h2 class='text-xl font-semibold mb-4'>Database Connection Status</h2>";
            echo "<p class='text-green-600'>Successfully connected to database: " . htmlspecialchars($dbname) . "</p>";
            echo "</div>";

            // Use the database
            $pdo->exec("USE hotelmanagementsystem");
            
            // Check if the payment_status column exists
            $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'");
            $column = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<div class='bg-white p-6 rounded-lg shadow-md'>";
            echo "<h2 class='text-xl font-semibold mb-4'>Payment Status Column Update</h2>";
            
            if ($column) {
                echo "<p class='text-green-600 font-bold'>✓ The payment_status column already exists in the bookings table.</p>";
                echo "<p class='mt-2'>No action needed.</p>";
            } else {
                // Add the payment_status column
                $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status");
                echo "<p class='text-green-600 font-bold'>✓ Successfully added the payment_status column to the bookings table.</p>";
                echo "<p class='mt-2'>Column details:</p>";
                echo "<ul class='list-disc list-inside mt-2'>";
                echo "<li>Name: payment_status</li>";
                echo "<li>Type: ENUM('pending', 'paid', 'failed')</li>";
                echo "<li>Default: pending</li>";
                echo "<li>Position: After the status column</li>";
                echo "</ul>";
            }
            
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6'>";
            echo "<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
        
        <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Next Steps</h2>
            <ol class="list-decimal list-inside space-y-2">
                <li><a href="check_db_payment.php" class="text-blue-600 hover:underline">Check again</a> to verify the column was added</li>
                <li>Test the booking functionality to ensure payment status is working</li>
                <li>Admins can now manage payment status in the bookings panel</li>
                <li>Users can view their payment status in their bookings</li>
            </ol>
        </div>
    </div>
</body>
</html>