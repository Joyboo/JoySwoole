CREATE TABLE IF NOT EXISTS `admin_node` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '上一级ID',
  `issys` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统菜单',
  `name` varchar(30) NOT NULL COMMENT '显示名称',
  `tip` varchar(40) NOT NULL COMMENT '菜单名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '9' COMMENT '排序(越小越前)',
  `clsname` varchar(50) NOT NULL COMMENT '样式的class值',
  `url` varchar(50) NOT NULL COMMENT '链接',
  `isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `isglb` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否全局',
  `gids` text COMMENT '游戏id,用逗号隔开',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`) USING BTREE,
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='认证规则表';