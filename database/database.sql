CREATE DATABASE IF NOT EXISTS cafeteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cafeteria;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM(
        'student',
        'employee',
        'admin'
    ) NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS auth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auth_tokens_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_auth_tokens_user (user_id),
    INDEX idx_auth_tokens_expiration (expires_at)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    category VARCHAR(80) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    qty INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NULL,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_products_price CHECK (price >= 0)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    status ENUM(
        'pending_payment',
        'paid',
        'ready',
        'claimed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending_payment',
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    pickup_code VARCHAR(20) NOT NULL UNIQUE,
    notes VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT chk_orders_total CHECK (total >= 0)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    provider ENUM('wompi') NOT NULL DEFAULT 'wompi',
    transaction_reference VARCHAR(120) NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM(
        'pending',
        'approved',
        'rejected',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id),
    CONSTRAINT chk_payments_amount CHECK (amount >= 0)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS payment_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id INT UNSIGNED NULL,
    provider VARCHAR(40) NOT NULL DEFAULT 'nequi',
    event_type VARCHAR(80) NOT NULL,
    external_reference VARCHAR(120) NULL,
    payload JSON NOT NULL,
    processed BOOLEAN NOT NULL DEFAULT FALSE,
    received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    CONSTRAINT fk_notifications_payment FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE SET NULL,
    INDEX idx_notifications_external_reference (external_reference),
    INDEX idx_notifications_processed (processed)
) ENGINE = InnoDB;

-- Datos de prueba para desarrollo local
INSERT INTO
    users (
        id,
        name,
        email,
        phone,
        password_hash,
        role,
        status
    )
VALUES (
        1,
        'Ana Rodriguez',
        'ana.rodriguez@colegio.test',
        '3001234567',
        '$2y$10$ovulUopGJq.BLDYb.u4ZE.PTQejnZRNOVncDbTaMjIKIGFrWN1FEC',
        'student',
        'active'
    ),
    (
        2,
        'Carlos Martinez',
        'carlos.martinez@colegio.test',
        '3019876543',
        '$2y$10$ovulUopGJq.BLDYb.u4ZE.PTQejnZRNOVncDbTaMjIKIGFrWN1FEC',
        'student',
        'active'
    ),
    (
        3,
        'Laura Gomez',
        'laura.gomez@colegio.test',
        '3105550188',
        '$2y$10$ovulUopGJq.BLDYb.u4ZE.PTQejnZRNOVncDbTaMjIKIGFrWN1FEC',
        'employee',
        'active'
    ),
    (
        4,
        'Administrador Cafeteria',
        'admin@cafeteria.test',
        '3204441122',
        '$2y$10$ovulUopGJq.BLDYb.u4ZE.PTQejnZRNOVncDbTaMjIKIGFrWN1FEC',
        'admin',
        'active'
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    phone = VALUES(phone),
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    status = VALUES(status);

INSERT INTO
    products (
        id,
        name,
        description,
        category,
        price,
        qty,
        image_url,
        is_available
    )
VALUES (
        1,
        'Arepa con queso',
        'Arepa de maiz caliente con queso derretido.',
        'Desayunos',
        3500.00,
        35,
        NULL,
        TRUE
    ),
    (
        2,
        'Sandwich de pollo',
        'Pan fresco, pollo, lechuga, tomate y salsa.',
        'Comidas',
        8500.00,
        20,
        NULL,
        TRUE
    ),
    (
        3,
        'Empanada',
        'Empanada de carne y papa.',
        'Snacks',
        2500.00,
        50,
        NULL,
        TRUE
    ),
    (
        4,
        'Jugo natural',
        'Jugo natural preparado en el momento.',
        'Bebidas',
        4000.00,
        25,
        NULL,
        TRUE
    ),
    (
        5,
        'Agua',
        'Botella de agua de 600 ml.',
        'Bebidas',
        2500.00,
        40,
        NULL,
        TRUE
    ),
    (
        6,
        'Galletas de avena',
        'Galletas caseras de avena y pasas.',
        'Snacks',
        3000.00,
        30,
        NULL,
        TRUE
    ),
    (
        7,
        'Almuerzo del dia',
        'Proteina, arroz, ensalada y bebida.',
        'Almuerzos',
        12000.00,
        15,
        NULL,
        TRUE
    ),
    (
        8,
        'Yogur con granola',
        'Yogur natural con fruta y granola.',
        'Desayunos',
        6000.00,
        0,
        NULL,
        FALSE
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category),
    price = VALUES(price),
    qty = VALUES(qty),
    image_url = VALUES(image_url),
    is_available = VALUES(is_available);

INSERT INTO
    orders (
        id,
        user_id,
        status,
        total,
        pickup_code,
        notes
    )
VALUES (
        1,
        1,
        'paid',
        11000.00,
        'CAF1001',
        'Entregar en el descanso de las 10:00.'
    ),
    (
        2,
        2,
        'ready',
        15500.00,
        'CAF1002',
        NULL
    ),
    (
        3,
        1,
        'pending_payment',
        17000.00,
        'CAF1003',
        'Sin salsas.'
    ),
    (
        4,
        3,
        'claimed',
        12000.00,
        'CAF1004',
        NULL
    )
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    status = VALUES(status),
    total = VALUES(total),
    notes = VALUES(notes);

INSERT INTO
    order_items (
        id,
        order_id,
        product_id,
        quantity,
        unit_price
    )
VALUES (1, 1, 2, 1, 8500.00),
    (2, 1, 5, 1, 2500.00),
    (3, 2, 1, 1, 3500.00),
    (4, 2, 7, 1, 12000.00),
    (5, 3, 2, 2, 8500.00),
    (6, 4, 7, 1, 12000.00)
ON DUPLICATE KEY UPDATE
    order_id = VALUES(order_id),
    product_id = VALUES(product_id),
    quantity = VALUES(quantity),
    unit_price = VALUES(unit_price);

INSERT INTO
    payments (
        id,
        order_id,
        provider,
        transaction_reference,
        amount,
        status,
        paid_at
    )
VALUES (
        1,
        1,
        'nequi',
        'NEQUI-202609220001',
        11000.00,
        'approved',
        '2026-09-22 09:15:00'
    ),
    (
        2,
        2,
        'nequi',
        'NEQUI-202609220002',
        15500.00,
        'approved',
        '2026-09-22 09:20:00'
    ),
    (
        3,
        3,
        'nequi',
        NULL,
        17000.00,
        'pending',
        NULL
    ),
    (
        4,
        4,
        'nequi',
        'NEQUI-202609220004',
        12000.00,
        'approved',
        '2026-09-22 08:45:00'
    )
ON DUPLICATE KEY UPDATE
    order_id = VALUES(order_id),
    amount = VALUES(amount),
    status = VALUES(status),
    paid_at = VALUES(paid_at);

INSERT INTO
    payment_notifications (
        id,
        payment_id,
        provider,
        event_type,
        external_reference,
        payload,
        processed,
        processed_at
    )
VALUES (
        1,
        1,
        'nequi',
        'payment.approved',
        'NEQUI-202609220001',
        '{"status":"approved","amount":11000,"reference":"NEQUI-202609220001"}',
        TRUE,
        '2026-09-22 09:15:05'
    ),
    (
        2,
        2,
        'nequi',
        'payment.approved',
        'NEQUI-202609220002',
        '{"status":"approved","amount":15500,"reference":"NEQUI-202609220002"}',
        TRUE,
        '2026-09-22 09:20:05'
    ),
    (
        3,
        3,
        'nequi',
        'payment.created',
        'NEQUI-202609220003',
        '{"status":"pending","amount":17000,"reference":"NEQUI-202609220003"}',
        FALSE,
        NULL
    ),
    (
        4,
        4,
        'nequi',
        'payment.approved',
        'NEQUI-202609220004',
        '{"status":"approved","amount":12000,"reference":"NEQUI-202609220004"}',
        TRUE,
        '2026-09-22 08:45:05'
    )
ON DUPLICATE KEY UPDATE
    payment_id = VALUES(payment_id),
    event_type = VALUES(event_type),
    external_reference = VALUES(external_reference),
    payload = VALUES(payload),
    processed = VALUES(processed),
    processed_at = VALUES(processed_at);