CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    image VARCHAR(255),
    category VARCHAR(50),
    download_path VARCHAR(255)
);

INSERT INTO products (name, description, price, image, category, download_path) VALUES
('Product 1', 'Description for Product 1', 120.00, 'assets/images/products/product_1.jpg', 'Wallpapers', 'assets/downloads/file1.zip'),
('Product 2', 'Description for Product 2', 220.00, 'assets/images/products/product_2.jpg', 'Templates', 'assets/downloads/file2.zip'),
('Product 3', 'Description for Product 3', 150.00, 'assets/images/products/product_3.jpg', 'Fonts', 'assets/downloads/file3.zip'),
('Product 4', 'Description for Product 4', 180.00, 'assets/images/products/product_4.jpg', 'Wallpapers', 'assets/downloads/file4.zip'),
('Product 5', 'Description for Product 5', 199.00, 'assets/images/products/product_5.jpg', 'Templates', 'assets/downloads/file5.zip'),
('Product 6', 'Description for Product 6', 130.00, 'assets/images/products/product_6.jpg', 'Fonts', 'assets/downloads/file6.zip');
