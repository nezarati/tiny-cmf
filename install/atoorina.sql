-- 
-- Structure for table `comment`
-- 

DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned NOT NULL,
  `node` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `status` int(1) NOT NULL,
  `name` varchar(64) NOT NULL,
  `mail` varchar(64) NOT NULL,
  `homepage` varchar(255) NOT NULL,
  `hostname` varchar(128) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` varchar(2048) NOT NULL,
  PRIMARY KEY (`service`,`id`),
  KEY `parent` (`service`,`parent`),
  KEY `user` (`service`,`user`),
  KEY `node` (`service`,`node`,`status`,`created`),
  KEY `created` (`service`,`created`),
  KEY `status` (`service`,`status`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `domain`
-- 

DROP TABLE IF EXISTS `domain`;
CREATE TABLE `domain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `expire` int(10) unsigned NOT NULL,
  `host` varchar(64) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`parent`,`id`),
  UNIQUE KEY `host` (`host`),
  KEY `updated` (`parent`,`status`,`modified`),
  KEY `user` (`user`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `form`
-- 

DROP TABLE IF EXISTS `form`;
CREATE TABLE `form` (
  `token` varchar(255) NOT NULL,
  `data` longblob,
  `expire` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`token`),
  KEY `expire` (`expire`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `i18n`
-- 

DROP TABLE IF EXISTS `i18n`;
CREATE TABLE `i18n` (
  `module` int(10) unsigned NOT NULL,
  `language` varchar(5) NOT NULL,
  `msgid` varchar(255) NOT NULL,
  `msgstr` varchar(255) NOT NULL
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `layout`
-- 

DROP TABLE IF EXISTS `layout`;
CREATE TABLE `layout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '1',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `public` int(1) NOT NULL,
  `name` varchar(64) NOT NULL,
  `main` text NOT NULL,
  `block` text NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `link`
-- 

DROP TABLE IF EXISTS `link`;
CREATE TABLE `link` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `taxonomy` int(10) unsigned NOT NULL,
  `hit` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `status` int(1) NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` varchar(255) NOT NULL,
  `url` varchar(1024) NOT NULL,
  PRIMARY KEY (`service`,`id`),
  KEY `taxonomy` (`service`,`taxonomy`),
  KEY `hit` (`service`,`hit`),
  KEY `created` (`service`,`created`),
  KEY `publish` (`service`,`status`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `module`
-- 

DROP TABLE IF EXISTS `module`;
CREATE TABLE `module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `expire` int(10) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `poll`
-- 

DROP TABLE IF EXISTS `poll`;
CREATE TABLE `poll` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` int(10) unsigned NOT NULL,
  `expire` int(10) unsigned NOT NULL,
  `multiple` int(2) NOT NULL,
  `status` int(1) NOT NULL,
  `question` varchar(150) NOT NULL,
  `choices` text NOT NULL,
  PRIMARY KEY (`service`,`id`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `post`
-- 

DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `status` int(1) NOT NULL,
  `title` varchar(127) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`service`,`id`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `rate`
-- 

DROP TABLE IF EXISTS `rate`;
CREATE TABLE `rate` (
  `service` int(10) unsigned NOT NULL,
  `node` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `score` int(2) NOT NULL,
  `hostname` varchar(128) NOT NULL,
  `created` int(10) unsigned NOT NULL,
  KEY `user` (`service`,`node`,`user`),
  KEY `hostname` (`service`,`node`,`hostname`),
  KEY `created` (`service`,`node`,`created`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `registry`
-- 

DROP TABLE IF EXISTS `registry`;
CREATE TABLE `registry` (
  `module` int(10) unsigned NOT NULL,
  `service` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`service`,`module`,`name`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `role`
-- 

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `administer` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `permission` text,
  PRIMARY KEY (`service`,`id`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `service`
-- 

DROP TABLE IF EXISTS `service`;
CREATE TABLE `service` (
  `module` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dependence` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `domain` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`module`,`id`),
  KEY `domain` (`domain`),
  KEY `user` (`user`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `session`
-- 

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `domain` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `id` varchar(32) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `data` varchar(4096) NOT NULL,
  `hostname` varchar(128) NOT NULL,
  `userAgent` varchar(255) NOT NULL,
  PRIMARY KEY (`domain`,`id`),
  KEY `domain` (`domain`,`user`),
  KEY `timestamp` (`timestamp`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `storage`
-- 

DROP TABLE IF EXISTS `storage`;
CREATE TABLE `storage` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `length` int(10) unsigned NOT NULL,
  `md5` varchar(32) NOT NULL,
  `contentType` varchar(64) DEFAULT NULL,
  `user` int(10) unsigned NOT NULL,
  `taxonomy` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `accessed` int(10) unsigned NOT NULL,
  `weight` int(2) NOT NULL,
  `downloads` int(10) unsigned NOT NULL,
  `data` text,
  PRIMARY KEY (`service`,`id`),
  KEY `filename` (`service`,`filename`),
  KEY `length` (`service`,`length`),
  KEY `user` (`service`,`user`),
  KEY `taxonomy` (`service`,`taxonomy`),
  KEY `created` (`service`,`created`),
  KEY `weight` (`service`,`weight`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `taxonomy`
-- 

DROP TABLE IF EXISTS `taxonomy`;
CREATE TABLE `taxonomy` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned NOT NULL,
  `weight` int(2) DEFAULT NULL,
  `count` int(10) unsigned NOT NULL,
  `term` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`service`,`id`),
  KEY `parent_weight` (`service`,`parent`,`weight`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `user`
-- 

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` int(10) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `accessed` int(10) unsigned NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(34) NOT NULL,
  `mail` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`service`,`id`),
  UNIQUE KEY `username` (`service`,`username`),
  UNIQUE KEY `mail` (`service`,`mail`),
  UNIQUE KEY `name` (`service`,`name`),
  KEY `role` (`service`,`role`),
  KEY `created` (`service`,`created`),
  KEY `accessed` (`service`,`accessed`),
  KEY `status` (`service`,`status`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `widget`
-- 

DROP TABLE IF EXISTS `widget`;
CREATE TABLE `widget` (
  `service` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weight` int(2) NOT NULL,
  `status` int(1) NOT NULL,
  `module` varchar(64) NOT NULL,
  `callback` varchar(64) NOT NULL,
  `title` varchar(120) NOT NULL,
  `content` text,
  PRIMARY KEY (`service`,`id`),
  KEY `service` (`service`,`status`,`weight`),
  KEY `weight` (`service`,`weight`)
) ENGINE=TOKUDB DEFAULT CHARSET=utf8;

