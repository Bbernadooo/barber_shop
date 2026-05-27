USE barber_shop;

CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 30,
    price DECIMAL(5,2) NOT NULL
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_email VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20),
    service_id INT NOT NULL,
    staff_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    reminder_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

CREATE TABLE staff_unavailable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

INSERT INTO services (name, duration_minutes, price) VALUES 
('Haircut', 30, 25.00),
('Beard Trim', 20, 15.00),
('Haircut & Beard', 45, 35.00);

INSERT INTO staff (name, email, password) VALUES 
('John Barber', 'john@barber.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');