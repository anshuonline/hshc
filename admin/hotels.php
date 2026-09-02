<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle form submission for updating the hotel
$message = '';
$error = '';

// Fetch the hotel information
$stmt = $pdo->query("SELECT * FROM hotels LIMIT 1");
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_hotel'])) {
        // Update hotel
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        
        if (empty($name) || empty($description)) {
            $error = 'Name and description are required.';
        } else {
            try {
                if ($hotel) {
                    // Update existing hotel
                    $stmt = $pdo->prepare("UPDATE hotels SET name = ?, description = ?, address = ?, phone = ?, email = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $address, $phone, $email, $hotel['id']]);
                } else {
                    // Insert new hotel
                    $stmt = $pdo->prepare("INSERT INTO hotels (name, description, address, phone, email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $address, $phone, $email]);
                }
                $message = 'Hotel information updated successfully!';
                
                // Refresh hotel data
                $stmt = $pdo->query("SELECT * FROM hotels LIMIT 1");
                $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = 'Error updating hotel: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotel - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Manage Hotel Information</h1>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Hotel Form -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Hotel Details</h2>
            </div>
            <div class="p-6">
                <form method="POST">
                    <input type="hidden" name="update_hotel" value="1">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Hotel Name *</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" type="text" value="<?php echo $hotel ? htmlspecialchars($hotel['name']) : ''; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description *</label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="6" required><?php echo $hotel ? htmlspecialchars($hotel['description']) : ''; ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address</label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" name="address" rows="3"><?php echo $hotel ? htmlspecialchars($hotel['address']) : ''; ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" type="text" value="<?php echo $hotel ? htmlspecialchars($hotel['phone']) : ''; ?>">
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" value="<?php echo $hotel ? htmlspecialchars($hotel['email']) : ''; ?>">
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </a>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Hotel Information
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>