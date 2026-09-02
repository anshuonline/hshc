<?php
// Database initialization script
include 'config/db.php';

try {
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS hotelmanagementsystem");
    $pdo->exec("USE hotelmanagementsystem");
    
    // Create hotels table
    $pdo->exec("CREATE TABLE IF NOT EXISTS hotels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        address TEXT,
        phone VARCHAR(20),
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create hotel_images table with room_id column
    $pdo->exec("CREATE TABLE IF NOT EXISTS hotel_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hotel_id INT,
        room_id INT NULL DEFAULT NULL,
        image_path VARCHAR(255) NOT NULL,
        caption VARCHAR(255),
        usage_type ENUM('carousel', 'cover', 'both') DEFAULT 'carousel',
        carousel_position INT NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
    )");
    
    // Create rooms table
    $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        amenities TEXT NULL DEFAULT NULL,
        price DECIMAL(10, 2) NOT NULL,
        capacity INT NOT NULL DEFAULT 2,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create admins table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role ENUM('admin', 'manager') DEFAULT 'admin',
        profile_picture VARCHAR(255) NULL DEFAULT NULL,
        authy_id VARCHAR(50) NULL DEFAULT NULL,
        authy_secret VARCHAR(255) NULL DEFAULT NULL,
        authy_enabled TINYINT(1) DEFAULT 0,
        authy_setup_complete TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create users table with phone and country columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        country VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create bookings table with payment_status and booking_number columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        check_in DATE NOT NULL,
        check_out DATE NOT NULL,
        adults INT NOT NULL DEFAULT 1,
        children INT NOT NULL DEFAULT 0,
        rooms INT NOT NULL DEFAULT 1,
        special_requests TEXT,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
        booking_number VARCHAR(20) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    
    // Create newsletter subscribers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) UNIQUE NOT NULL,
        name VARCHAR(100),
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create reviews table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        booking_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_booking_review (user_id, booking_id)
    )");
    
    // Insert sample admin user (username: admin, password: admin123) using MD5
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute(['admin']);
    if (!$stmt->fetch()) {
        $md5_password = md5('admin123'); // Using MD5 as requested
        $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)")
            ->execute(['admin', $md5_password, 'admin@example.com']);
    }
    
    // Insert real hotel information
    $stmt = $pdo->prepare("SELECT id FROM hotels LIMIT 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hotel_name = 'Demo Hotel & Resort';
        $hotel_description = 'Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City. Demo City has something for everyone; it has places of interest for the holiday-makers, fine restaurants for the foodies, gorgeous nature for the nature-lovers and fashion streets for the fashionistas.

The hotel is designed in new-age architecture with a focus on comfort and contemporary trends of living, all at an affordable tariff. It offers world-class facilities and brilliant services with modern amenities.

Our hotels in Demo City are located around these cultural and commercial places, making travelling within the city easy for visitors. Our hotels ensure an enjoyable stay for its patrons by making available rooms with the best amenities and a hospitable staff. Hotel is not only offer exceptional comfort and convenience, but also brilliant hotels deals that make accommodation easy and affordable.

Our hotel in Demo City that you must choose for a successful Demo City trip.';
        $hotel_address = '123 Demo Street, Demo City, Demo State';
        $hotel_phone = '+12345678900';
        $hotel_email = 'reservations@demohotel.com';
        
        $pdo->prepare("INSERT INTO hotels (name, description, address, phone, email) VALUES (?, ?, ?, ?, ?)")
            ->execute([
                $hotel_name,
                $hotel_description,
                $hotel_address,
                $hotel_phone,
                $hotel_email
            ]);
    }
    
    echo "Database initialized successfully with real hotel information and MD5 password!";
} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
?>