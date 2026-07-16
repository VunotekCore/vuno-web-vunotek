-- Vunotek Backoffice Database Schema
-- Ejecutar: mysql -u user -p database < schema.sql
CREATE DATABASE IF NOT EXISTS vuno_web
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vuno_web;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#69dca4',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    category_id INT NOT NULL,
    author VARCHAR(100) DEFAULT 'Daniel Flores',
    locale ENUM('es', 'en') NOT NULL DEFAULT 'es',
    image VARCHAR(500),
    meta_title VARCHAR(255),
    og_image VARCHAR(500),
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles iniciales
INSERT INTO roles (name, slug, permissions) VALUES
('Admin', 'admin', '{"all": true}'),
('Editor', 'editor', '{"blog": ["read","create","edit"], "categories": ["read","create","edit"]}'),
('Viewer', 'viewer', '{"blog": ["read"], "categories": ["read"]}')
ON DUPLICATE KEY UPDATE name = name;

-- Admin user default (password: admin123 — cambiar en producción)
-- Generar hash: php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
-- role_id = 1 (admin)
INSERT INTO users (email, password, name, role_id) VALUES
('admin@vunotek.com', '$2y$10$YourHashHere', 'Daniel Flores', 1)
ON DUPLICATE KEY UPDATE email = email;

-- Categorías iniciales
INSERT INTO categories (name, slug, description, color, sort_order) VALUES
('Frontend', 'frontend', 'Desarrollo de interfaces y用户体验', '#80D4FF', 1),
('Backend', 'backend', 'Arquitectura de servidores y APIs', '#69dca4', 2),
('TypeScript', 'typescript', 'Tipado estático y mejores prácticas', '#77dd6d', 3),
('DevOps', 'devops', 'Infraestructura y despliegue', '#ffb4ab', 4)
ON DUPLICATE KEY UPDATE name = name;
