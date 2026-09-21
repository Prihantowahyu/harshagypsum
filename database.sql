-- Database Schema for Harsha Gypsum
-- Character set utf8mb4 for full unicode support

CREATE DATABASE IF NOT EXISTS `harsha_gypsum` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `harsha_gypsum`;

-- Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL DEFAULT 'Administrator',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'fas fa-th-large',
  `description` TEXT,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `category_slug` VARCHAR(50) NOT NULL,
  `width` VARCHAR(50) DEFAULT '',
  `width_number` FLOAT DEFAULT 0,
  `length` VARCHAR(50) DEFAULT '',
  `length_meter` FLOAT DEFAULT 0,
  `price` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `price_bulk` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `min_bulk_qty` INT DEFAULT 10,
  `rating` FLOAT DEFAULT 5.0,
  `sold` INT DEFAULT 0,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_best_seller` TINYINT(1) DEFAULT 0,
  `image` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `specs` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`category_slug`),
  INDEX (`is_active`),
  INDEX (`is_best_seller`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Admin Account (Username: admin | Password: admin123)
-- Password hash generated with password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admins` (`username`, `password`, `name`)
VALUES ('admin', '$2y$10$Q7eYqgA23GkXN2H5w64F.e6o5bOQ9K7M.4UomSskd3ZlT2B2G0X7S', 'Admin Harsha Gypsum')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Seed Categories
INSERT INTO `categories` (`slug`, `name`, `icon`, `description`, `display_order`) VALUES
('minimalis', 'Minimalis & Modern', 'fas fa-border-style', 'List profil gypsum corak garis tegas dan minimalis kekinian', 1),
('klasik', 'Klasik & Mewah', 'fas fa-crown', 'Profil cornice ukir daun dan motif eropa mewah', 2),
('shadowline', 'Shadowline & Tali Air', 'fas fa-grip-lines', 'Profil tali air celah lampu drop ceiling plafon', 3),
('wall-moulding', 'Wall Moulding Dinding', 'fas fa-square', 'List pigura hiasan dinding aksen interior mewah', 4),
('ornamen', 'Ornamen & Center Panel', 'fas fa-certificate', 'Medallion bunga plafon dudukan lampu gantung', 5),
('aksesoris', 'Aksesoris & Bahan Pasang', 'fas fa-tools', 'Tepung kompon perekat gypsum & kasa fiber roving', 6)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Seed Products
INSERT INTO `products` (`id`, `code`, `name`, `category_slug`, `width`, `width_number`, `length`, `length_meter`, `price`, `price_bulk`, `min_bulk_qty`, `rating`, `sold`, `is_popular`, `is_best_seller`, `image`, `description`, `specs`) VALUES
(1, 'GP-101', 'List Gypsum Minimalis Step 7cm', 'minimalis', '7 cm', 7, '2.10 meter', 2.1, 13500, 11000, 50, 4.9, 1420, 1, 1, 'assets/images/list-minimalis.jpg', 'List profil gypsum minimalis corak bertingkat (step-profile). Sangat cocok untuk rumah tipe modern minimalis, apartemen, dan ruang kantor. Presisi sudut 90 derajat memudahkan pemotongan miter.', '[{"label": "Bahan", "value": "Tepung Casting A-Grade + Serat Roving Berkualitas"}, {"label": "Panjang Efektif", "value": "2.10 Meter / Batang"}, {"label": "Lebar Muka", "value": "7 cm"}, {"label": "Ketebalan", "value": "12 - 14 mm (Kuat & Tidak Melengkung)"}, {"label": "Finishing", "value": "Permukaan Halus, Siap Cat Dasar (Porous Free)"}]'),
(2, 'GP-102', 'List Gypsum Minimalis Bertingkat 10cm', 'minimalis', '10 cm', 10, '2.10 meter', 2.1, 17500, 14500, 50, 5.0, 2150, 1, 1, 'assets/images/list-minimalis.jpg', 'Model best seller terfavorit para kontraktor dan desainer interior. Profil garis tegas memberikan ilusi plafon tampak lebih tinggi dan rapi.', '[{"label": "Bahan", "value": "Gypsum Casting Roving High Density"}, {"label": "Panjang Efektif", "value": "2.10 Meter / Batang"}, {"label": "Lebar Muka", "value": "10 cm"}, {"label": "Ketebalan", "value": "14 mm"}, {"label": "Karakteristik", "value": "Tahan Retak, Presisi Tinggi"}]'),
(3, 'GP-103', 'List Minimalis Garis Dobel 12cm', 'minimalis', '12 cm', 12, '2.20 meter', 2.2, 22000, 18500, 40, 4.8, 890, 0, 0, 'assets/images/list-minimalis.jpg', 'Ukuran ideal untuk ruangan dengan ketinggian plafon 3 - 3.5 meter. Tampilan elegan tanpa terkesan berlebihan.', '[{"label": "Bahan", "value": "Gypsum Casting Super + 3 Lapis Serat Roving"}, {"label": "Panjang Efektif", "value": "2.20 Meter / Batang"}, {"label": "Lebar Muka", "value": "12 cm"}, {"label": "Ketebalan", "value": "15 mm"}, {"label": "Pemasangan", "value": "Menggunakan Perekat Kompon Khusus"}]'),
(4, 'GP-201', 'List Profil Klasik Ukir Daun Acanthus 15cm', 'klasik', '15 cm', 15, '2.10 meter', 2.1, 32000, 27500, 30, 4.9, 730, 1, 0, 'assets/images/list-klasik.jpg', 'Karya seni cetakan gypsum klasik bergaya Baroque Eropa. Ukiran relief daun Acanthus yang tajam, sangat mewah untuk ruang tamu, aula, dan rumah mewah bergaya klasik Amerika atau Mediterania.', '[{"label": "Bahan", "value": "Gypsum Casting Premium + Serat Roving Tebal"}, {"label": "Panjang Efektif", "value": "2.10 Meter / Batang"}, {"label": "Lebar Muka", "value": "15 cm"}, {"label": "Ketebalan", "value": "18 mm"}, {"label": "Kelebihan", "value": "Detail Ukiran Tajam & Tidak Mudah Gompal"}]'),
(5, 'GP-202', 'List Cornice Klasik Royal Roman 18cm', 'klasik', '18 cm', 18, '2.10 meter', 2.1, 39500, 34000, 30, 5.0, 520, 0, 0, 'assets/images/list-klasik.jpg', 'Ukuran jumbo untuk void tangga, ruang tamu berplafon tinggi (>3.8 meter), serta ballroom. Memberikan nuansa megah istana.', '[{"label": "Bahan", "value": "Casting Gypsum Padat + Sisipan Roving Ganda"}, {"label": "Panjang Efektif", "value": "2.10 Meter / Batang"}, {"label": "Lebar Muka", "value": "18 cm"}, {"label": "Ketebalan", "value": "20 mm"}, {"label": "Berat Batang", "value": "± 3.8 kg"}]'),
(6, 'GP-301', 'Shadowline / Tali Air Drop Ceiling 3x3 cm', 'shadowline', '3 x 3 cm', 3, '2.40 meter', 2.4, 15000, 12500, 50, 4.9, 3400, 1, 1, 'assets/images/shadowline.jpg', 'List profil pembentuk celah bayangan (shadowline / tali air) modern. Pilihan utama arsitek masa kini untuk tepian plafon tanpa list sudut konvensional, serta untuk cove drop ceiling lampu LED strip.', '[{"label": "Bahan", "value": "Gypsum Roving Presisi Ekstra Keras"}, {"label": "Panjang Efektif", "value": "2.40 Meter / Batang"}, {"label": "Ukuran Celah", "value": "3 cm x 3 cm"}, {"label": "Kelebihan", "value": "Garis Lurus Sempurna, Mudah Dipasang Bersama Rangka Hollow"}]'),
(7, 'GP-302', 'Shadowline Drop Celah Lebar 4x4 cm', 'shadowline', '4 x 4 cm', 4, '2.40 meter', 2.4, 18000, 15000, 50, 4.8, 1100, 0, 0, 'assets/images/shadowline.jpg', 'Solusi sempurna untuk drop ceiling dengan housing lampu tersembunyi (hidden LED indirect light). Ruang celah leluasa untuk sirkulasi panas lampu.', '[{"label": "Bahan", "value": "High Grade Gypsum Plaster"}, {"label": "Panjang Efektif", "value": "2.40 Meter / Batang"}, {"label": "Dimensi Celah", "value": "4 cm x 4 cm"}, {"label": "Kelebihan", "value": "Hasil Siku Rapi Tanpa Gelombang"}]'),
(8, 'GP-401', 'List Wall Moulding Pigura Dinding 4cm', 'wall-moulding', '4 cm', 4, '2.20 meter', 2.2, 12500, 9500, 50, 4.9, 4100, 1, 1, 'assets/images/wall-moulding.jpg', 'List profil ramping khusus dekorasi dinding (wall moulding frame/pigura). Mempercantik backdrop TV, dinding headboard tempat tidur, dan lorong rumah dengan aksen mewah ala interior Pinterest.', '[{"label": "Bahan", "value": "Gypsum Roving Padat Presisi Halus"}, {"label": "Panjang Efektif", "value": "2.20 Meter / Batang"}, {"label": "Lebar Muka", "value": "4 cm"}, {"label": "Ketebalan", "value": "10 mm"}, {"label": "Pemasangan", "value": "Lem Sealant / Kompon Perekat"}]'),
(9, 'GP-402', 'List Wall Moulding Pigura Dinding 6cm', 'wall-moulding', '6 cm', 6, '2.20 meter', 2.2, 15500, 12500, 50, 4.9, 2800, 1, 0, 'assets/images/wall-moulding.jpg', 'Profil bingkai dinding ukuran medium dengan kurva ganda yang lembut. Memberikan kedalaman visual elegan pada dinding cat polos.', '[{"label": "Bahan", "value": "Gypsum Casting Roving High Grade"}, {"label": "Panjang Efektif", "value": "2.20 Meter / Batang"}, {"label": "Lebar Muka", "value": "6 cm"}, {"label": "Ketebalan", "value": "12 mm"}]'),
(10, 'GP-501', 'Ornamen Plafon Ceiling Rose Motif Bunga 60cm', 'ornamen', 'Diameter 60 cm', 60, '1 Pcs', 0.6, 85000, 70000, 10, 5.0, 670, 1, 1, 'assets/images/ornamen-center.jpg', 'Ornamen tengah plafon bundar (medallion/ceiling rose) untuk dudukan lampu gantung atau kipas angin plafon. Motif relief kembang artistik cetakan super detail.', '[{"label": "Bahan", "value": "Casting Gypsum Tebal + Rangka Serat Anyaman"}, {"label": "Diameter Luar", "value": "60 cm"}, {"label": "Diameter Lubang Kabel", "value": "3 - 5 cm (Bisa disesuaikan)"}, {"label": "Ketebalan Relief", "value": "25 - 35 mm"}]'),
(11, 'GP-502', 'Ornamen Plafon Center Panel Klasik 80cm', 'ornamen', 'Diameter 80 cm', 80, '1 Pcs', 0.8, 135000, 115000, 5, 4.9, 380, 0, 0, 'assets/images/ornamen-center.jpg', 'Center panel jumbo untuk ruang utama dan chandelier besar. Memberi daya tarik visual pusat ruangan yang megah dan berkelas.', '[{"label": "Bahan", "value": "Gypsum Casting Padat + Multi-layer Roving"}, {"label": "Diameter", "value": "80 cm"}, {"label": "Finishing", "value": "Halus Siap Cat / Efek Gold Leafing"}]'),
(12, 'GP-601', 'Tepung Kompon Perekat Gypsum Adhesive 20kg', 'aksesoris', 'Zak 20 Kg', 0, 'Sak', 1, 75000, 68000, 10, 4.9, 5200, 1, 1, 'assets/images/aksesoris.jpg', 'Bubuk lem kompon perekat khusus list profil & compound sambungan gypsum board. Daya rekat super kuat, cepat kering (setting time 15-20 menit), tidak mudah retak rambut.', '[{"label": "Kemasan", "value": "Sak Kertas Kraft 20 Kg"}, {"label": "Daya Rekat", "value": "Ekstra Kuat (Adhesive Bond Max)"}, {"label": "Daya Sebar", "value": "± 35 - 45 Batang List Profil per Sak"}, {"label": "Setting Time", "value": "15 - 25 Menit"}]'),
(13, 'GP-602', 'Kasa Lakban Serat Fiber Roving Mesh Roll 50m', 'aksesoris', 'Lebar 5 cm', 0, 'Roll 50 Meter', 50, 28000, 23000, 20, 4.8, 3800, 0, 0, 'assets/images/aksesoris.jpg', 'Pita serat jaring fiber berperekat (fiber mesh tape) untuk penguat sambungan list gypsum, pertemuan plafon dan dinding agar tahan getaran dan anti retak.', '[{"label": "Dimensi", "value": "Lebar 50 mm x Panjang 50 Meter"}, {"label": "Bahan", "value": "Fiberglass Mesh Self-Adhesive"}, {"label": "Fungsi", "value": "Mencegah Retak Sambungan List & Plafon"}]')
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`);
