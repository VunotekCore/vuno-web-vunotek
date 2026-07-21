-- Seed: Proyectos actuales de ProjectShowcase
-- Ejecutar: mysql -u user -p database < seed_projects.sql

USE vuno_web;

INSERT INTO projects (name, tag, slug, image, live_url, is_saas, description, tech, locale, status, sort_order) VALUES
('VUNO POS', 'POS', 'vuno-pos', 'https://ik.imagekit.io/vijys5g3r/vuno-web/products/Vuno-POS.webp', NULL, TRUE, NULL, NULL, 'es', 'published', 1),
('VUNO SHOP', 'E-COMMERCE', 'vuno-shop', 'https://ik.imagekit.io/vijys5g3r/vuno-web/products/ecomerce.webp', 'https://shop.vunotek.com', TRUE, NULL, NULL, 'es', 'published', 2),
('VUNO DRIVE', 'E-COMMERCE', 'vuno-drive', 'https://ik.imagekit.io/vijys5g3r/vuno-web/products/drive.webp', NULL, TRUE, NULL, NULL, 'es', 'published', 3),
('VUNO RESERVATIONS', 'HOTEL', 'vuno-reservations', 'https://ik.imagekit.io/vijys5g3r/vuno-web/products/reservations.webp', NULL, TRUE, NULL, NULL, 'es', 'published', 4),
('VUNO ENVÍOS', 'LOGISTICS', 'vuno-envios', 'https://ik.imagekit.io/vijys5g3r/vuno-web/products/logistics.webp', 'https://vuno-sa.netlify.app/', FALSE, 'Plataforma logística digital conectando Miami con Nicaragua con casillero virtual, rastreo de paquetes, envíos aéreos y marítimos, y entrega puerta a puerta.', NULL, 'es', 'published', 5),
('VUNO CRM', 'CRM', 'vuno-crm', 'https://ik.imagekit.io/vijys5g3r/vuno-web/products/crm.webp', NULL, TRUE, NULL, NULL, 'es', 'published', 6),
('Sabores & nutrición', 'Web Site', 'sabores-nutricion', 'https://ik.imagekit.io/vijys5g3r/projects/saboresynuricion.webp', 'https://vuno-nutricion.netlify.app', FALSE, NULL, NULL, 'es', 'published', 7),
('Experiencias', 'Web Site', 'experiencias', 'https://ik.imagekit.io/vijys5g3r/projects/experiencias.webp', 'https://vuno-experiencias.netlify.app/', FALSE, NULL, NULL, 'es', 'published', 8)
ON DUPLICATE KEY UPDATE name = VALUES(name), tag = VALUES(tag), image = VALUES(image), live_url = VALUES(live_url), is_saas = VALUES(is_saas), description = VALUES(description), tech = VALUES(tech), locale = VALUES(locale), status = VALUES(status), sort_order = VALUES(sort_order);
