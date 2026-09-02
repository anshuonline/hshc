<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle Google Sheets export
if (isset($_GET['export']) && $_GET['export'] === 'spreadsheet') {
    // Fetch subscribers with filters
    $whereClause = "";
    $params = [];
    
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $whereClause = "WHERE subscribed_at BETWEEN ? AND ?";
        $params[] = $_GET['start_date'] . ' 00:00:00';
        $params[] = $_GET['end_date'] . ' 23:59:59';
    } elseif (!empty($_GET['start_date'])) {
        $whereClause = "WHERE subscribed_at >= ?";
        $params[] = $_GET['start_date'] . ' 00:00:00';
    } elseif (!empty($_GET['end_date'])) {
        $whereClause = "WHERE subscribed_at <= ?";
        $params[] = $_GET['end_date'] . ' 23:59:59';
    }
    
    $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers $whereClause ORDER BY subscribed_at DESC");
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV export (compatible with Google Sheets)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
    
    // Output CSV headers
    echo "ID,Name,Email,Subscribed At\n";
    
    // Output data rows
    foreach ($subscribers as $subscriber) {
        echo '"' . $subscriber['id'] . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($subscriber['name'])) . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($subscriber['email'])) . '",';
        echo '"' . date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at'])) . '"' . "\n";
    }
    
    exit;
}

// Fetch subscribers with filters
$whereClause = "";
$params = [];

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $whereClause = "WHERE subscribed_at BETWEEN ? AND ?";
    $params[] = $_GET['start_date'] . ' 00:00:00';
    $params[] = $_GET['end_date'] . ' 23:59:59';
} elseif (!empty($_GET['start_date'])) {
    $whereClause = "WHERE subscribed_at >= ?";
    $params[] = $_GET['start_date'] . ' 00:00:00';
} elseif (!empty($_GET['end_date'])) {
    $whereClause = "WHERE subscribed_at <= ?";
    $params[] = $_GET['end_date'] . ' 23:59:59';
}

$stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers $whereClause ORDER BY subscribed_at DESC");
$stmt->execute($params);
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch statistics
$stats = [
    'total' => 0
];

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM newsletter_subscribers");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    $error = 'Failed to fetch statistics: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669',
                        secondary: '#047857',
                        accent: '#10b981',
                        light: '#ecfdf5',
                        dark: '#065f46',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gray-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white font-bold">Demo Hotel & Resort Admin</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="hotels.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Hotel</a>
                            <a href="images.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Images</a>
                            <a href="rooms.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Rooms</a>
                            <a href="bookings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Bookings</a>
                            <a href="subscribers.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Subscribers</a>
                            <a href="admins.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Admins</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="text-gray-300 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                        <a href="logout.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Newsletter Subscribers</h1>
                <p class="mt-1 text-gray-600">Manage newsletter subscribers</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="?export=spreadsheet<?php 
                    $params = [];
                    if (!empty($_GET['start_date'])) $params[] = 'start_date=' . urlencode($_GET['start_date']);
                    if (!empty($_GET['end_date'])) $params[] = 'end_date=' . urlencode($_GET['end_date']);
                    echo $params ? '&' . implode('&', $params) : '';
                ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-export mr-2"></i>
                    Export to Google Sheets
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Subscribers</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900"><?php echo $stats['total']; ?></div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Filters</h2>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Apply Filters
                        </button>
                        <a href="subscribers.php" class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-center">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Subscribers Table -->
        <?php if (empty($subscribers)): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Subscribers</h2>
                </div>
                <div class="p-6 text-center">
                    <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No subscribers found.</p>
                    <?php if (!empty($_GET['start_date']) || !empty($_GET['end_date'])): ?>
                        <p class="text-gray-500 mt-2">Try adjusting your filters.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Subscribers</h2>
                    <p class="mt-2 md:mt-0 text-sm text-gray-500"><?php echo count($subscribers); ?> subscriber(s) found</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $subscriber['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($subscriber['name'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($subscriber['subscribed_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <p class="text-sm text-gray-700">
                        Showing <?php echo count($subscribers); ?> of <?php echo $stats['total']; ?> subscribers
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>