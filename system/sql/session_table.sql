SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `session_table`
-- ----------------------------
DROP TABLE IF EXISTS `session_table`;
CREATE TABLE `session_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(25) NOT NULL DEFAULT '',
  `ip_address` varchar(50) DEFAULT NULL,
  `last_seen` varchar(50) DEFAULT NULL,
  `user_data` text,
  PRIMARY KEY (`id`,`token`)
) DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of session_table
-- ----------------------------
