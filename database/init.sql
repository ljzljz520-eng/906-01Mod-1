-- 创建数据库
CREATE DATABASE IF NOT EXISTS torrent_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 授权给 baobei 用户
GRANT ALL PRIVILEGES ON torrent_search.* TO 'baobei'@'%';
FLUSH PRIVILEGES;

USE torrent_search;

-- 创建搜索历史表
CREATE TABLE IF NOT EXISTS search_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  keyword VARCHAR(50) NOT NULL COMMENT '搜索源',
  query VARCHAR(255) NOT NULL COMMENT '搜索关键词',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  INDEX idx_created_at (created_at),
  INDEX idx_keyword (keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='搜索历史表';

-- 创建来源站点表
CREATE TABLE IF NOT EXISTS providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL COMMENT '来源站点名称',
  slug VARCHAR(50) NOT NULL COMMENT '来源标识',
  credibility ENUM('trusted', 'normal', 'pending') NOT NULL DEFAULT 'normal' COMMENT '可信度等级: trusted可信来源, normal普通来源, pending待复核来源',
  maintainer VARCHAR(100) COMMENT '维护人',
  authorization VARCHAR(500) COMMENT '授权说明',
  official_url VARCHAR(500) COMMENT '官方地址',
  description TEXT COMMENT '站点描述',
  is_active TINYINT(1) DEFAULT 1 COMMENT '是否启用',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  UNIQUE INDEX idx_slug (slug),
  INDEX idx_credibility (credibility),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='来源站点表';

-- 创建收藏表（扩展字段）
CREATE TABLE IF NOT EXISTS favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(500) NOT NULL COMMENT 'Torrent 名称',
  magnet TEXT NOT NULL COMMENT '磁力链接',
  size VARCHAR(50) COMMENT '文件大小',
  seeders INT DEFAULT 0 COMMENT '做种数',
  leechers INT DEFAULT 0 COMMENT '下载数',
  category VARCHAR(100) COMMENT '分类',
  source VARCHAR(50) COMMENT '来源站点标识',
  source_name VARCHAR(100) COMMENT '来源站点名称',
  maintainer VARCHAR(100) COMMENT '维护人',
  authorization VARCHAR(500) COMMENT '授权说明',
  mirror_health ENUM('healthy', 'warning', 'unhealthy', 'unknown') NOT NULL DEFAULT 'unknown' COMMENT '镜像健康: healthy健康, warning警告, unhealthy异常, unknown未知',
  last_checked_at TIMESTAMP NULL COMMENT '最近检查时间',
  credibility ENUM('trusted', 'normal', 'pending') NOT NULL DEFAULT 'normal' COMMENT '可信度等级: trusted可信来源, normal普通来源, pending待复核来源',
  status ENUM('active', 'delisted', 'pending_review', 'violation') NOT NULL DEFAULT 'active' COMMENT '状态: active正常, delisted失效下架, pending_review待复核, violation疑似违规',
  delisted_reason VARCHAR(500) COMMENT '下架原因',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  INDEX idx_created_at (created_at),
  INDEX idx_source (source),
  INDEX idx_credibility (credibility),
  INDEX idx_status (status),
  UNIQUE INDEX idx_magnet (magnet(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收藏表';

-- 插入来源站点数据
INSERT INTO providers (name, slug, credibility, maintainer, authorization, official_url, description, is_active) VALUES
('1337x', '1337x', 'trusted', '1337x Official Team', '公开索引，用户自行上传内容，DMCA合规处理', 'https://1337x.to', '知名公开种子索引站，内容覆盖面广，审核机制完善', 1),
('YTS', 'yts', 'trusted', 'YTS Team', '专注于高清电影资源，遵守DMCA下架流程', 'https://yts.mx', '专业电影资源站，以高质量小体积影片著称', 1),
('The Pirate Bay', 'thepiratebay', 'normal', 'Community Maintained', '社区运营，无官方审核，用户需自行甄别', 'https://thepiratebay.org', '历史悠久的社区型资源站，内容混杂需谨慎', 1),
('Rarbg', 'rarbg', 'trusted', 'RARBG Team', '专业团队运营，严格内容审核，DMCA合规', 'https://rarbg.to', '高质量资源站，以专业的发布组和严格审核闻名', 1),
('Torrent9', 'torrent9', 'pending', 'Unknown', '来源可信度待复核，建议谨慎使用', 'https://torrent9.to', '法语区资源站，来源可靠性待验证', 1),
('EZTV', 'eztv', 'normal', 'EZTV Team', '专注电视剧资源，社区审核', 'https://eztv.re', '老牌电视剧资源站，剧集更新及时', 1);

-- 插入示例搜索历史数据
INSERT INTO search_history (keyword, query, created_at) VALUES
('1337x', 'Avengers', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('yts', 'Inception', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('eztv', 'Breaking Bad', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('1337x', 'The Matrix', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 插入示例收藏数据
INSERT INTO favorites (name, magnet, size, seeders, leechers, category, source, source_name, maintainer, authorization, mirror_health, last_checked_at, credibility, status, created_at) VALUES
(
  'Avengers: Endgame (2019) [1080p]',
  'magnet:?xt=urn:btih:EXAMPLE1234567890ABCDEF&dn=Avengers+Endgame',
  '2.5 GB',
  1250,
  85,
  'Movies',
  '1337x',
  '1337x',
  '1337x Official Team',
  '公开索引，用户自行上传内容，DMCA合规处理',
  'healthy',
  DATE_SUB(NOW(), INTERVAL 2 HOUR),
  'trusted',
  'active',
  DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
  'The Dark Knight (2008) [720p]',
  'magnet:?xt=urn:btih:EXAMPLE0987654321FEDCBA&dn=The+Dark+Knight',
  '1.8 GB',
  890,
  42,
  'Movies',
  'yts',
  'YTS',
  'YTS Team',
  '专注于高清电影资源，遵守DMCA下架流程',
  'healthy',
  DATE_SUB(NOW(), INTERVAL 30 MINUTE),
  'trusted',
  'active',
  DATE_SUB(NOW(), INTERVAL 3 DAY)
);

-- 显示表结构
SHOW TABLES;
SELECT '✅ Database initialized successfully!' AS status;
