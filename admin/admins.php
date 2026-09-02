<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Check if current user is a manager
$stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$current_admin = $stmt->fetch(PDO::FETCH_ASSOC);
$is_manager = $current_admin && $current_admin['role'] === 'manager';

// Handle form submission for adding/updating admins
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_admin'])) {
        // Only managers can add admins
        if (!$is_manager) {
            $error = 'Only managers can add new admins.';
        } else {
            // Add new admin
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $role = $_POST['role'] ?? 'admin';
            
            // Check manager limit (maximum 4 managers)
            if ($role === 'manager') {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins WHERE role = 'manager'");
                $manager_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                if ($manager_count >= 4) {
                    $error = 'Maximum of 4 managers allowed. Cannot add more managers.';
                }
            }
            
            if (empty($error)) {
                if (empty($username) || empty($email) || empty($password)) {
                    $error = 'All fields are required.';
                } elseif ($password !== $confirm_password) {
                    $error = 'Passwords do not match.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                } else {
                    // Check if username already exists
                    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = 'Username already exists.';
                    } else {
                        try {
                            $hashed_password = md5($password); // Using MD5 as required
                            $stmt = $pdo->prepare("INSERT INTO admins (username, password, email, role) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$username, $hashed_password, $email, $role]);
                            $message = 'Admin user added successfully!';
                        } catch (PDOException $e) {
                            $error = 'Error adding admin: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    } elseif (isset($_POST['delete_admin'])) {
        // Only managers can delete admins
        if (!$is_manager) {
            $error = 'Only managers can delete admins.';
        } else {
            // Delete admin (prevent deleting self)
            $id = intval($_POST['id']);
            if ($id == $_SESSION['admin_id']) {
                $error = 'You cannot delete your own account.';
            } else {
                // Check if trying to delete a manager
                $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
                $stmt->execute([$id]);
                $admin_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin_to_delete && $admin_to_delete['role'] === 'manager') {
                    // Check if current user is also a manager
                    if (!$is_manager) {
                        $error = 'Only managers can delete other managers.';
                    } else {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                            $stmt->execute([$id]);
                            $message = 'Admin user deleted successfully!';
                        } catch (PDOException $e) {
                            $error = 'Error deleting admin: ' . $e->getMessage();
                        }
                    }
                } else {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                        $stmt->execute([$id]);
                        $message = 'Admin user deleted successfully!';
                    } catch (PDOException $e) {
                        $error = 'Error deleting admin: ' . $e->getMessage();
                    }
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Managers can change passwords of admins, everyone can change their own
        $admin_id = intval($_POST['admin_id']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Check permissions
        $can_change = false;
        if ($admin_id == $_SESSION['admin_id']) {
            // Can change own password
            $can_change = true;
        } elseif ($is_manager) {
            // Managers can change passwords of other admins
            $can_change = true;
        }
        
        if (!$can_change) {
            $error = 'You do not have permission to change this password.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                $hashed_password = md5($new_password); // Using MD5 as required
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $admin_id]);
                $message = 'Password updated successfully!';
            } catch (PDOException $e) {
                $error = 'Error updating password: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['upload_profile_picture'])) {
        // Handle profile picture upload
        $admin_id = intval($_POST['admin_id']);
        
        // Check permissions
        $can_upload = false;
        if ($admin_id == $_SESSION['admin_id']) {
            // Can upload own profile picture
            $can_upload = true;
        } elseif ($is_manager) {
            // Managers can upload profile pictures for any admin
            $can_upload = true;
        }
        
        if (!$can_upload) {
            $error = 'You do not have permission to upload a profile picture for this admin.';
        } else {
            // Handle file upload
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if (!in_array($_FILES['profile_picture']['type'], $allowed_types)) {
                    $error = 'Invalid file type. Only JPEG, PNG, and GIF files are allowed.';
                } elseif ($_FILES['profile_picture']['size'] > $max_size) {
                    $error = 'File size exceeds 2MB limit.';
                } else {
                    // Create uploads directory if it doesn't exist
                    $upload_dir = '../uploads/admins/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $filename = 'admin_' . $admin_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // Update database with profile picture path
                        try {
                            $stmt = $pdo->prepare("UPDATE admins SET profile_picture = ? WHERE id = ?");
                            $stmt->execute([$filename, $admin_id]);
                            $message = 'Profile picture uploaded successfully!';
                        } catch (PDOException $e) {
                            $error = 'Error updating profile picture: ' . $e->getMessage();
                            // Delete uploaded file if database update failed
                            if (file_exists($upload_path)) {
                                unlink($upload_path);
                            }
                        }
                    } else {
                        $error = 'Error uploading file.';
                    }
                }
            } else {
                $error = 'Please select a file to upload.';
            }
        }
    }
}

// Fetch all admins
$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count managers
$stmt = $pdo->query("SELECT COUNT(*) as count FROM admins WHERE role = 'manager'");
$manager_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Demo Hotel & Resort</title>
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
        .admin-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .role-badge {
            transition: all 0.2s ease;
        }
        .role-badge:hover {
            transform: scale(1.05);
        }
        .action-btn {
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
        }
        .profile-img-container {
            position: relative;
            display: inline-block;
        }
        .profile-img-container:hover .overlay {
            opacity: 1;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 50%;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Management</h1>
                <p class="mt-2 text-gray-600">Manage administrator accounts and permissions</p>
            </div>
            <?php if ($is_manager): ?>
                <button onclick="openAddModal()" class="mt-4 md:mt-0 bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary text-white px-6 py-3 rounded-lg shadow-md font-medium flex items-center transition duration-300">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Admin
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Info Messages -->
        <?php if (!$is_manager): ?>
            <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            You are logged in as an Admin. Only Managers can add or delete admins.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
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

        <?php if ($error): ?>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 admin-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Admins</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($admins); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 admin-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100 text-green-600">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Managers</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $manager_count; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 admin-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                        <i class="fas fa-user text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Regular Admins</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($admins) - $manager_count; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 admin-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Available Manager Slots</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo 4 - $manager_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admins Grid -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Administrator Accounts</h2>
                <p class="text-gray-600 text-sm mt-1">Manage all admin accounts and their permissions</p>
            </div>
            
            <?php if (empty($admins)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-user-friends text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No admin users found</h3>
                    <p class="text-gray-500">Get started by adding a new admin user.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    <?php foreach ($admins as $admin): ?>
                        <div class="admin-card bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="profile-img-container">
                                        <?php if (!empty($admin['profile_picture'])): ?>
                                            <img src="../uploads/admins/<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="<?php echo htmlspecialchars($admin['username']); ?>" class="w-16 h-16 rounded-full object-cover">
                                        <?php else: ?>
                                            <div class="bg-gray-200 border-2 border-dashed rounded-full w-16 h-16 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-600 text-xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="overlay" onclick="openImageModal('../uploads/admins/<?php echo htmlspecialchars($admin['profile_picture']); ?>')">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></h3>
                                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($admin['email']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="role-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        <?php echo $admin['role'] === 'manager' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($admin['role'])); ?>
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        <?php echo date('M j, Y', strtotime($admin['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <div class="mt-6 flex flex-wrap gap-2">
                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                        <?php if ($is_manager): ?>
                                            <button onclick="openProfilePictureModal(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['username']); ?>')" class="action-btn inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                                <i class="fas fa-image mr-1"></i> Picture
                                            </button>
                                            <button onclick="openPasswordModal(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['username']); ?>')" class="action-btn inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                                <i class="fas fa-key mr-1"></i> Password
                                            </button>
                                            <button onclick="openDeleteModal(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['username']); ?>')" class="action-btn inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </button>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-500 bg-gray-100">
                                                <i class="fas fa-lock mr-1"></i> No permissions
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button onclick="openProfilePictureModal(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['username']); ?>')" class="action-btn inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                            <i class="fas fa-image mr-1"></i> Picture
                                        </button>
                                        <button onclick="openPasswordModal(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['username']); ?>')" class="action-btn inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                            <i class="fas fa-key mr-1"></i> Password
                                        </button>
                                        <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-500 bg-gray-100">
                                            <i class="fas fa-user mr-1"></i> You
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-2xl shadow-xl w-11/12 md:w-1/2 lg:w-2/5 mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <h3 class="text-xl font-bold text-gray-900">Add New Admin User</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="add_admin" value="1">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="username">Username *</label>
                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="username" name="username" type="text" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email *</label>
                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="email" name="email" type="email" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Password *</label>
                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="password" name="password" type="password" required>
                        <p class="text-gray-500 text-xs mt-1">Must be at least 6 characters long.</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="confirm_password">Confirm Password *</label>
                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="confirm_password" name="confirm_password" type="password" required>
                    </div>
                    <?php if ($is_manager): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="role">Role</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="role" name="role">
                            <option value="admin">Admin</option>
                            <option value="manager" <?php echo ($manager_count >= 4) ? 'disabled' : ''; ?>>Manager <?php echo ($manager_count >= 4) ? '(Limit reached: 4)' : "(Current: $manager_count/4)"; ?></option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Add Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Admin Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-2xl shadow-xl w-11/12 md:w-1/3 mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <h3 class="text-xl font-bold text-gray-900">Delete Admin User</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-gray-700">Are you sure you want to delete the admin user "<span id="delete_admin_name" class="font-bold"></span>"? This action cannot be undone.</p>
                </div>
                <form method="POST" class="mt-6">
                    <input type="hidden" name="delete_admin" value="1">
                    <input type="hidden" id="delete_id" name="id">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-2xl shadow-xl w-11/12 md:w-1/2 lg:w-2/5 mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <h3 class="text-xl font-bold text-gray-900">Change Password for <span id="password_admin_name"></span></h3>
                    <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="change_password" value="1">
                    <input type="hidden" id="password_admin_id" name="admin_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="new_password">New Password *</label>
                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="new_password" name="new_password" type="password" required>
                        <p class="text-gray-500 text-xs mt-1">Must be at least 6 characters long.</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="confirm_new_password">Confirm New Password *</label>
                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" id="confirm_new_password" name="confirm_password" type="password" required>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePasswordModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Picture Modal -->
    <div id="profilePictureModal" class="fixed inset-0 bg-black bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-2xl shadow-xl w-11/12 md:w-1/2 lg:w-2/5 mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <h3 class="text-xl font-bold text-gray-900">Upload Profile Picture for <span id="picture_admin_name"></span></h3>
                    <button onclick="closeProfilePictureModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="upload_profile_picture" value="1">
                    <input type="hidden" id="picture_admin_id" name="admin_id">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="profile_picture">Profile Picture *</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="profile_picture" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-secondary focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input id="profile_picture" name="profile_picture" type="file" accept="image/*" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">JPEG, PNG, or GIF files only. Maximum 2MB.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeProfilePictureModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Upload Picture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Zoom Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex items-center justify-center p-4">
        <div class="relative max-w-6xl max-h-full">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-3xl bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all duration-300 z-10">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex flex-col items-center">
                <img id="zoomedImage" src="" alt="Profile Picture" class="max-h-[80vh] max-w-full object-contain">
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openDeleteModal(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_admin_name').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openPasswordModal(id, name) {
            document.getElementById('password_admin_id').value = id;
            document.getElementById('password_admin_name').textContent = name;
            document.getElementById('passwordModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openProfilePictureModal(id, name) {
            document.getElementById('picture_admin_id').value = id;
            document.getElementById('picture_admin_name').textContent = name;
            document.getElementById('profilePictureModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeProfilePictureModal() {
            document.getElementById('profilePictureModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openImageModal(imagePath) {
            document.getElementById('zoomedImage').src = imagePath;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const addModal = document.getElementById('addModal');
            const deleteModal = document.getElementById('deleteModal');
            const passwordModal = document.getElementById('passwordModal');
            const profilePictureModal = document.getElementById('profilePictureModal');
            const imageModal = document.getElementById('imageModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
            if (event.target === passwordModal) {
                closePasswordModal();
            }
            if (event.target === profilePictureModal) {
                closeProfilePictureModal();
            }
            if (event.target === imageModal) {
                closeImageModal();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddModal();
                closeDeleteModal();
                closePasswordModal();
                closeProfilePictureModal();
                closeImageModal();
            }
        });
    </script>
</body>
</html>