-- hotel_db.sql
-- Database for Hotel Management System

-- USERS
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- ADMINS
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- BOOKINGS
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    arrival_date DATE NOT NULL,
    suite_type VARCHAR(50) NOT NULL,
    accepted TINYINT(1) DEFAULT NULL,  -- null = pending, 1 = accepted, 0 = rejected
    update_status VARCHAR(20) NOT NULL DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CANCELLATIONS
CREATE TABLE IF NOT EXISTS cancellations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    arrival_date DATE NOT NULL,
    suite_type VARCHAR(50) NOT NULL,
    booking_id INT NULL,  -- optional, to link cancellation to a booking record
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);

-- UPDATE REQUESTS
CREATE TABLE IF NOT EXISTS update_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    arrival_date DATE NOT NULL,
    suite_type VARCHAR(20) NOT NULL,
    note TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    admin_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- USER ACTIVITY LOG
CREATE TABLE IF NOT EXISTS users_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    activity TEXT NOT NULL,
    activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ADMIN ACTIVITY LOG
CREATE TABLE IF NOT EXISTS admin_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(50) NOT NULL,
    activity TEXT NOT NULL,
    activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

