CREATE TABLE `staging_files_pushed` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `file_path` varchar(500) NOT NULL,
    `type` enum('file','dir') NOT NULL DEFAULT 'file',
    `from` enum('stag','prod') NOT NULL DEFAULT 'stag',
    `deleted` tinyint(1) NOT NULL,
    `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;