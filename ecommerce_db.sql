-- ============================================
-- قاعدة البيانات: ecommerce_db
-- ============================================

CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- ============================================
-- 1. جدول المستخدمين (users)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. جدول التصنيفات (categories)
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- ============================================
-- 3. جدول المنتجات (products)
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- ============================================
-- 4. جدول سلة التسوق (cart_items)
-- ============================================
CREATE TABLE IF NOT EXISTS cart_items (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, product_id)
);

-- ============================================
-- 5. جدول الطلبات (orders)
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT
);

-- ============================================
-- 6. جدول تفاصيل الطلبات (order_items)
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- ============================================
-- 7. جدول الرسائل (contacts)
-- ============================================
CREATE TABLE IF NOT EXISTS contacts (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(100),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- بيانات تجريبية (للتجربة فقط)
-- ============================================

-- مستخدم Admin (كلمة المرور: admin123)
INSERT INTO users (full_name, email, password, role) VALUES 
('Admin', 'admin@lessence.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- مستخدم عادي (كلمة المرور: password123)
INSERT INTO users (full_name, email, password, phone, address, role) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', '123 Main St, New York', 'customer');

-- تصنيفات
INSERT INTO categories (name, description) VALUES 
('Oriental', 'Warm, spicy, and exotic fragrances'),
('Floral', 'Fresh and romantic flower-based scents'),
('Woody', 'Earthy and sophisticated woody aromas'),
('Fresh', 'Light and invigorating aquatic scents');

-- منتجات
INSERT INTO products (name, description, price, stock_quantity, image_url, category_id) VALUES 
('Solstice', 'A luminous journey capturing summer solstice with bergamot, sea salt, and white musk.', 145.00, 50, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDyMqFesDtexX47eyxMUalc2kNzt5l46Ue4BKem4-ldfz46wtE1C7SoE3hceD_-V1sHLYhCwbXKWpRldxoFh_JwUa63r5EljpydiEQlwEqLJ8fq0PFiUXiNS2Cb59x8C0owiZDBuoSTVKr4dspHZUJ7w4i3hWDXtY0Iqk--aL9Kk0-JfihIKt-DbXe--6wJ2uKCfjZ5y9RHphU8_OiHU2Iz6fthbkgTYMIrN-HMwmmwkPeT_rBFq4LC0sky2Uh3obEKh_eti8vfRVo', 4),
('Nocturne', 'A mysterious blend of saffron, tobacco, and leather.', 180.00, 30, 'https://lh3.googleusercontent.com/aida-public/AB6AXuCQdA4O5dDgK__Fl14KDCm0HpCR9TiY0XOUDZq7gnAanIYXj9cKy8uwzgxS2rMKIOV16J1OvxLGtBypxysym0E72zXB3IOVUZyXMXMYwUyUjM_FGxuNoR2UMOwz6GbcHr6UVo-weHuZ2nKIykcTVuZ04k56ADKGMC7xcFwUANcjlVgGzeFXw3P84wxQtA1S8kD9szNM99dwr8emLaxf5HXKcRmLTTC1KvXblTDc6BjwNxAiibnPNh1LFasTBvm7OfzwBh1sRBh4Q6g', 1),
('Amber Veil', 'Warm amber, vanilla bean, and cedarwood.', 160.00, 40, 'https://lh3.googleusercontent.com/aida-public/AB6AXuD95jn-26pOV3sUiF9WHnAQx-Cc1kQPIKdANSzSxvbxqvm8ht7UykLNGolzY5DtbR8XAtT4aXOsqXIoqnIyFkMvA6MFVnzSn7TYGAYjZgyx7Qot08VliHtLFtaPt8DAA2JVdUXWOvbSt4HtVM688VvPLuJlO6NPIxWUZ0mQi2WkVVBQ2pF_pqpnuCka8ESCUplaiMCrqFcUyxmGro1k2NHKOdIsXwLp5ByTJBvKU8qr4mL8cYYw5XaZTHXmaIXU1DV2i4ov-6_0Oq4', 1),
('Velvet Moss', 'Oakmoss, vetiver, and juniper for an earthy experience.', 135.00, 45, 'https://lh3.googleusercontent.com/aida-public/AB6AXuAtIQ4-g_cqWP3g0UmP4N0wsS7X21tP6wSUkWBY0CYbnN11Pag2ujYd17cFQdlHNHDkrcpf3_L8C623b7XsgfGDqoxZAkxWKSmh1xpDvSpiI-lJ6VTKh-hALMPHJYIN5KrPcpDTm4-8xURkD9-JcILQYowC3xfVDdZsY52uWXfnkNCEtmnmWNhbB9h3uAbWtZo5eKwvy61XWYH3LGVTf5ZQqkW8ptYFAcJRswwOxhPcaypbQaaFBQ76Vr2s4j99vhvNmmEuX1seG3M', 3),
('Oud Imperial', 'Rare oud, rose, and cardamom.', 195.00, 25, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDcHjbHfwP8qPGpD-2x45KmE84SXGr3l4-nUYkU3arwGJHu_sZHv71AF5iv9NrqMw3ek76GVya-q8qx9WYq59rnpKFoBPv_OEK61iP7iLJ5-HoDd9jvxcw1fbOHZpAcdb8jrxD_DCH2wseuSiEnvvvhAkFf8IKxG3Otsrw8ScCBAWY0nnwdlUIcqAnYG96CKvTPumgh2lPPN3xYHCLp4vCCPHdgo1qvylc5MuAbLftnpT1acwoaPK20e_z_5lzJjJvOVJcB-Bjig7A', 1),
('Petal Drift', 'Peony, jasmine, and pink pepper.', 120.00, 60, 'https://lh3.googleusercontent.com/aida-public/AB6AXuBrn6IHqA0AKQvloOOhN0gn93mYfB4aYABKkNe4V40BkNt-88rCplFVSq2rekx-iE34Ow6cloM4aryWB0Lo3X3mw5Oywpv2ulNJWdfQ02AnY7TpmB5oVkjGe8jbbgabLneO9VwqtbuCchiqu0edAzAwTCPFZ8N54m2S8drAlANt_WtqY9JPE0b1lwP63cPLexEKNmytBGLfaoRbV0sK9NFu5OZJ3vOcWkNFq3XiZi_mc3qOYKVKyayWGaPARbJbEAhmvAcn_-Jhf2E', 2);