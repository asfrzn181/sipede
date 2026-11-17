ALTER TABLE `kegiatan_peserta`
ADD COLUMN `pendamping_id` INT UNSIGNED NULL AFTER `penerima_id`,
ADD KEY `idx_kp_pendamping` (`pendamping_id`),
ADD CONSTRAINT `fk_kp_pendamping` FOREIGN KEY (`pendamping_id`) REFERENCES `pendamping` (`id`) ON DELETE SET NULL;
