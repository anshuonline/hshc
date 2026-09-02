<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    
    if (in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $booking_id]);
            $message = 'Booking status updated successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to update booking status.';
        }
    }
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $payment_status = $_POST['payment_status'];
    
    if (in_array($payment_status, ['pending', 'paid', 'failed'])) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
            $stmt->execute([$payment_status, $booking_id]);
            $message = 'Payment status updated successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to update payment status.';
        }
    }
}

// Handle check-in/check-out time update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_times'])) {
    $booking_id = intval($_POST['booking_id']);
    $check_in_time = !empty($_POST['check_in_time']) ? $_POST['check_in_time'] : null;
    $check_out_time = !empty($_POST['check_out_time']) ? $_POST['check_out_time'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET check_in_time = ?, check_out_time = ? WHERE id = ?");
        $stmt->execute([$check_in_time, $check_out_time, $booking_id]);
        $message = 'Check-in/Check-out times updated successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to update check-in/check-out times.';
    }
}

// Handle nights update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_nights'])) {
    $booking_id = intval($_POST['booking_id']);
    $nights = intval($_POST['nights']);
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET nights = ? WHERE id = ?");
        $stmt->execute([$nights, $booking_id]);
        $message = 'Number of nights updated successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to update number of nights.';
    }
}

// Handle Google Spreadsheet export
if (isset($_GET['export']) && $_GET['export'] === 'spreadsheet') {
    // Fetch all bookings with filters
    $whereClause = "";
    $params = [];
    
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $whereClause = "WHERE b.created_at BETWEEN ? AND ?";
        $params[] = $_GET['start_date'] . ' 00:00:00';
        $params[] = $_GET['end_date'] . ' 23:59:59';
    } elseif (!empty($_GET['start_date'])) {
        $whereClause = "WHERE b.created_at >= ?";
        $params[] = $_GET['start_date'] . ' 00:00:00';
    } elseif (!empty($_GET['end_date'])) {
        $whereClause = "WHERE b.created_at <= ?";
        $params[] = $_GET['end_date'] . ' 23:59:59';
    }
    
    if (!empty($_GET['status'])) {
        $whereClause .= $whereClause ? " AND b.status = ?" : "WHERE b.status = ?";
        $params[] = $_GET['status'];
    }
    
    if (!empty($_GET['payment_status'])) {
        $whereClause .= $whereClause ? " AND b.payment_status = ?" : "WHERE b.payment_status = ?";
        $params[] = $_GET['payment_status'];
    }
    
    if (!empty($_GET['booking_number'])) {
        $whereClause .= $whereClause ? " AND b.booking_number LIKE ?" : "WHERE b.booking_number LIKE ?";
        $params[] = '%' . $_GET['booking_number'] . '%';
    }
    
    $stmt = $pdo->prepare("SELECT b.*, u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id = u.id $whereClause ORDER BY b.created_at DESC");
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV export (compatible with Google Sheets)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bookings_' . date('Y-m-d') . '.csv"');
    
    // Output CSV headers
    echo "Booking ID, Booking Number, Guest Name, Email, Phone, Check-in, Check-in Time, Check-out, Check-out Time, Adults, Children, Rooms, Status, Payment Status, Special Requests, Created At\n";
    
    // Output data rows
    foreach ($bookings as $booking) {
        echo '"' . $booking['id'] . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($booking['booking_number'])) . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($booking['name'])) . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($booking['email'])) . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($booking['phone'])) . '",';
        echo '"' . date('Y-m-d', strtotime($booking['check_in'])) . '",';
        echo '"' . ($booking['check_in_time'] ? $booking['check_in_time'] : '') . '",';
        echo '"' . date('Y-m-d', strtotime($booking['check_out'])) . '",';
        echo '"' . ($booking['check_out_time'] ? $booking['check_out_time'] : '') . '",';
        echo '"' . $booking['adults'] . '",';
        echo '"' . $booking['children'] . '",';
        echo '"' . $booking['rooms'] . '",';
        echo '"' . ucfirst($booking['status']) . '",';
        echo '"' . ucfirst($booking['payment_status']) . '",';
        echo '"' . str_replace('"', '""', htmlspecialchars($booking['special_requests'])) . '",';
        echo '"' . date('Y-m-d H:i:s', strtotime($booking['created_at'])) . '"' . "\n";
    }
    
    exit;
}

// Fetch bookings with filters
$whereClause = "";
$params = [];

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $whereClause = "WHERE b.created_at BETWEEN ? AND ?";
    $params[] = $_GET['start_date'] . ' 00:00:00';
    $params[] = $_GET['end_date'] . ' 23:59:59';
} elseif (!empty($_GET['start_date'])) {
    $whereClause = "WHERE b.created_at >= ?";
    $params[] = $_GET['start_date'] . ' 00:00:00';
} elseif (!empty($_GET['end_date'])) {
    $whereClause = "WHERE b.created_at <= ?";
    $params[] = $_GET['end_date'] . ' 23:59:59';
}

if (!empty($_GET['status'])) {
    $whereClause .= $whereClause ? " AND b.status = ?" : "WHERE b.status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['payment_status'])) {
    $whereClause .= $whereClause ? " AND b.payment_status = ?" : "WHERE b.payment_status = ?";
    $params[] = $_GET['payment_status'];
}

if (!empty($_GET['booking_number'])) {
    $whereClause .= $whereClause ? " AND b.booking_number LIKE ?" : "WHERE b.booking_number LIKE ?";
    $params[] = '%' . $_GET['booking_number'] . '%';
}

$stmt = $pdo->prepare("SELECT b.*, u.name as user_name, r.name as room_name FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN rooms r ON b.room_id = r.id $whereClause ORDER BY b.created_at DESC");
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'paid' => 0,
    'unpaid' => 0
];

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $stats[$row['status']] = $row['count'];
    }
    
    $stmt = $pdo->query("SELECT payment_status, COUNT(*) as count FROM bookings GROUP BY payment_status");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        if ($row['payment_status'] === 'paid') {
            $stats['paid'] = $row['count'];
        } else {
            $stats['unpaid'] += $row['count'];
        }
    }
} catch (PDOException $e) {
    $error = 'Failed to fetch statistics: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        secondary: '#0284c7',
                        accent: '#06b6d4',
                        dark: '#0c4a6e',
                        light: '#f0f9ff'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        /* Modern scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #0ea5e9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0284c7;
        }

        .booking-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            overflow: hidden;
        }
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .status-badge {
            transition: all 0.2s ease;
        }
        .status-badge:hover {
            transform: scale(1.05);
        }
        .action-btn {
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .filter-section {
            transition: all 0.3s ease;
        }
        
        /* Hotel image placeholder */
        .hotel-image {
            background: linear-gradient(rgba(14, 165, 233, 0.1), rgba(14, 165, 233, 0.1)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            height: 150px;
            position: relative;
        }
        
        /* Responsive grid */
        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .bookings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-primary to-secondary shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white font-bold text-xl">Demo Hotel & Resort Admin</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="dashboard.php" class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">Dashboard</a>
                            <a href="hotels.php" class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">Hotels</a>
                            <a href="images.php" class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">Images</a>
                            <a href="bookings.php" class="bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium">Bookings</a>
                            <a href="admins.php" class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">Admins</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="text-blue-100 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                        <a href="logout.php" class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Booking Management</h1>
                <p class="mt-2 text-gray-600">Manage hotel reservations and guest bookings</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="?export=spreadsheet<?php 
                    $params = [];
                    if (!empty($_GET['start_date'])) $params[] = 'start_date=' . urlencode($_GET['start_date']);
                    if (!empty($_GET['end_date'])) $params[] = 'end_date=' . urlencode($_GET['end_date']);
                    if (!empty($_GET['status'])) $params[] = 'status=' . urlencode($_GET['status']);
                    if (!empty($_GET['payment_status'])) $params[] = 'payment_status=' . urlencode($_GET['payment_status']);
                    if (!empty($_GET['booking_number'])) $params[] = 'booking_number=' . urlencode($_GET['booking_number']);
                    echo $params ? '&' . implode('&', $params) : '';
                ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-export mr-2"></i>
                    Export to CSV
                </a>
            </div>
        </div>

        <!-- Info Messages -->
        <?php if (isset($message)): ?>
            <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-4 text-white transform transition-all duration-300 hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-xs font-medium uppercase tracking-wide">Total Bookings</p>
                        <p class="text-lg font-bold mt-1"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="p-2 rounded-full bg-blue-400 bg-opacity-30">
                        <i class="fas fa-calendar-check text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg p-4 text-white transform transition-all duration-300 hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-100 text-xs font-medium uppercase tracking-wide">Pending</p>
                        <p class="text-lg font-bold mt-1"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="p-2 rounded-full bg-amber-400 bg-opacity-30">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg p-4 text-white transform transition-all duration-300 hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-medium uppercase tracking-wide">Confirmed</p>
                        <p class="text-lg font-bold mt-1"><?php echo $stats['confirmed']; ?></p>
                    </div>
                    <div class="p-2 rounded-full bg-emerald-400 bg-opacity-30">
                        <i class="fas fa-check-circle text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-xl shadow-lg p-4 text-white transform transition-all duration-300 hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-rose-100 text-xs font-medium uppercase tracking-wide">Cancelled</p>
                        <p class="text-lg font-bold mt-1"><?php echo $stats['cancelled']; ?></p>
                    </div>
                    <div class="p-2 rounded-full bg-rose-400 bg-opacity-30">
                        <i class="fas fa-times-circle text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 text-white transform transition-all duration-300 hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-xs font-medium uppercase tracking-wide">Paid</p>
                        <p class="text-lg font-bold mt-1"><?php echo $stats['paid']; ?></p>
                    </div>
                    <div class="p-2 rounded-full bg-green-400 bg-opacity-30">
                        <i class="fas fa-money-bill-wave text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-4 text-white transform transition-all duration-300 hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-xs font-medium uppercase tracking-wide">Unpaid</p>
                        <p class="text-lg font-bold mt-1"><?php echo $stats['unpaid']; ?></p>
                    </div>
                    <div class="p-2 rounded-full bg-orange-400 bg-opacity-30">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md mb-8 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Filter Bookings</h2>
                <p class="text-gray-600 text-sm mt-1">Refine your search with specific criteria</p>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label for="booking_number" class="block text-sm font-medium text-gray-700 mb-1">Booking Number</label>
                        <input type="text" id="booking_number" name="booking_number" value="<?php echo isset($_GET['booking_number']) ? htmlspecialchars($_GET['booking_number']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" placeholder="Search by booking number">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                        <select id="payment_status" name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                            <option value="">All Payments</option>
                            <option value="pending" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    
                    <div class="lg:col-span-5 flex justify-end space-x-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Apply Filters
                        </button>
                        <a href="bookings.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings Section -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Booking Records</h2>
                    <p class="text-gray-600 text-sm mt-1">Manage all hotel reservations</p>
                </div>
                <p class="mt-2 md:mt-0 text-sm text-gray-500"><?php echo count($bookings); ?> booking(s) found</p>
            </div>
            
            <?php if (empty($bookings)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No bookings found</h3>
                    <p class="text-gray-500">Try adjusting your filters or add a new booking.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6 p-6">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="flex items-center">
                                            <h3 class="text-lg font-bold text-gray-900">Booking #<?php echo htmlspecialchars($booking['booking_number']); ?></h3>
                                            <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                ID: <?php echo $booking['id']; ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($booking['name']); ?></p>
                                        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($booking['email']); ?> | <?php echo htmlspecialchars($booking['phone']); ?></p>
                                    </div>
                                    <div class="mt-4 md:mt-0 flex space-x-2">
                                        <?php
                                        $status_class = '';
                                        switch ($booking['status']) {
                                            case 'confirmed':
                                                $status_class = 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $status_class = 'bg-yellow-100 text-yellow-800';
                                        }
                                        ?>
                                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_class; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                        
                                        <?php
                                        $payment_class = '';
                                        switch ($booking['payment_status']) {
                                            case 'paid':
                                                $payment_class = 'bg-green-100 text-green-800';
                                                break;
                                            case 'failed':
                                                $payment_class = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $payment_class = 'bg-amber-100 text-amber-800';
                                        }
                                        ?>
                                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $payment_class; ?>">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Check-in / Check-out</p>
                                        <p class="font-medium mt-1"><?php echo date('M j, Y', strtotime($booking['check_in'])); ?> - <?php echo date('M j, Y', strtotime($booking['check_out'])); ?></p>
                                        <?php if (!empty($booking['check_in_time']) || !empty($booking['check_out_time'])): ?>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <?php if (!empty($booking['check_in_time'])): ?>
                                                    Check-in: <?php echo date('g:i A', strtotime($booking['check_in_time'])); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($booking['check_out_time'])): ?>
                                                    <?php if (!empty($booking['check_in_time'])) echo ' | '; ?>
                                                    Check-out: <?php echo date('g:i A', strtotime($booking['check_out_time'])); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?php 
                                            // Use manually set nights if available, otherwise calculate
                                            if (!is_null($booking['nights'])) {
                                                echo $booking['nights'] . " nights (manually set)";
                                            } else {
                                                $checkIn = new DateTime($booking['check_in']);
                                                $checkOut = new DateTime($booking['check_out']);
                                                $interval = $checkIn->diff($checkOut);
                                                $nights = $interval->days;
                                                echo $nights . " nights (auto calculated)";
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Guests & Rooms</p>
                                        <p class="font-medium mt-1"><?php echo $booking['adults']; ?> Adult<?php echo $booking['adults'] != 1 ? 's' : ''; ?>, <?php echo $booking['children']; ?> Child<?php echo $booking['children'] != 1 ? 'ren' : ''; ?></p>
                                        <p class="text-sm text-gray-500 mt-1"><?php echo $booking['rooms']; ?> Room<?php echo $booking['rooms'] != 1 ? 's' : ''; ?></p>
                                    </div>
                                    
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Room Type</p>
                                        <p class="font-medium mt-1"><?php echo !empty($booking['room_name']) ? htmlspecialchars($booking['room_name']) : 'Not specified'; ?></p>
                                        <p class="text-sm text-gray-500 mt-1">Booking ID: #<?php echo $booking['id']; ?></p>
                                    </div>
                                    
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Total Price</p>
                                        <p class="font-bold text-lg mt-1">₹<?php echo number_format($booking['total_price'] ?? 0, 2); ?></p>
                                        <p class="text-sm text-gray-500 mt-1">Booked on <?php echo date('M j, Y', strtotime($booking['created_at'])); ?></p>
                                    </div>
                                </div>
                                
                                <?php if (!empty($booking['special_requests'])): ?>
                                    <div class="mt-4 bg-blue-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Special Requests</p>
                                        <p class="mt-1"><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-6 flex flex-wrap gap-3">
                                    <form method="POST" class="flex items-center">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <label class="text-sm text-gray-600 mr-2">Status:</label>
                                        <select name="status" onchange="this.form.submit()" class="px-3 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary transition">
                                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                    
                                    <form method="POST" class="flex items-center">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="update_payment_status" value="1">
                                        <label class="text-sm text-gray-600 mr-2">Payment:</label>
                                        <select name="payment_status" onchange="this.form.submit()" class="px-3 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary transition">
                                            <option value="pending" <?php echo $booking['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $booking['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="failed" <?php echo $booking['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        </select>
                                    </form>
                                    
                                    <!-- Check-in/Check-out Time Form -->
                                    <form method="POST" class="flex items-center flex-wrap gap-2 bg-gray-100 p-3 rounded-lg">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="update_times" value="1">
                                        <label class="text-sm text-gray-600">Check-in Time:</label>
                                        <input type="time" name="check_in_time" value="<?php echo $booking['check_in_time']; ?>" class="px-2 py-1 border border-gray-300 rounded-md text-sm">
                                        <label class="text-sm text-gray-600">Check-out Time:</label>
                                        <input type="time" name="check_out_time" value="<?php echo $booking['check_out_time']; ?>" class="px-2 py-1 border border-gray-300 rounded-md text-sm">
                                        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600">Update Times</button>
                                    </form>
                                    
                                    <!-- Nights Override Form -->
                                    <form method="POST" class="flex items-center flex-wrap gap-2 bg-gray-100 p-3 rounded-lg">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="update_nights" value="1">
                                        <label class="text-sm text-gray-600">Nights:</label>
                                        <input type="number" name="nights" value="<?php echo $booking['nights'] ?? ''; ?>" min="0" max="365" class="px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="Auto">
                                        <button type="submit" class="px-3 py-1 bg-purple-500 text-white rounded-md text-sm hover:bg-purple-600">Set Nights</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>