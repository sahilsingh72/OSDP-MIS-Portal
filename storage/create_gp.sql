CREATE TABLE gram_panchayats (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  block_id bigint(20) unsigned NOT NULL,
  name varchar(255) NOT NULL,
  code varchar(20) DEFAULT NULL,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY gram_panchayats_block_id_name_unique (block_id, name),
  KEY gram_panchayats_block_id_index (block_id),
  CONSTRAINT gram_panchayats_block_id_foreign FOREIGN KEY (block_id) REFERENCES blocks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

