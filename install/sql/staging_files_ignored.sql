CREATE TABLE `staging_files_ignored` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`file_path` varchar(500) NOT NULL,
	`type` enum('file','dir') NOT NULL DEFAULT 'file'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `staging_files_ignored` (`file_path`, `type`) VALUES
('/.htaccess', 'dir'),
('/admin/modules', 'dir'),
('/git', 'file'),
('/robots.txt', 'file'),
('/sitemap.xml', 'file'),
('/error_log', 'file');