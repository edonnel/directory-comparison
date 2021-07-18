<?
	$table_staging_files_ignored = array(
		'name'    => 'staging_files_ignored',
		'columns' => array(
			'file_path' => array(
				'type'      => 'varchar(500)',
				'null'      => false,
				'extra'     => '',
			),
			'type' => array(
				'type'      => 'enum(\'file\',\'dir\')',
				'null'      => false,
				'extra'     => 'DEFAULT \'file\'',
			),
		),
		'insert'  => array(
			array(
				'file_path' => '/.htaccess',
				'type'      => 'file',
			),
			array(
				'file_path' => '/robots.txt',
				'type'      => 'file',
			),
			array(
				'file_path' => '/sitemap.xml',
				'type'      => 'file',
			),
			array(
				'file_path' => '/error_log',
				'type'      => 'file',
			),
		),
	);

	$table_staging_files_pushed = array(
		'name'    => 'staging_files_pushed',
		'columns' => array(
			'file_path' => array(
				'type'      => 'varchar(500)',
				'null'      => false,
				'extra'     => '',
			),
			'type' => array(
				'type'      => 'enum(\'file\',\'dir\')',
				'null'      => false,
				'extra'     => 'DEFAULT \'file\'',
			),
			'from' => array(
				'type'      => 'enum(\'file\',\'dir\')',
				'null'      => false,
				'extra'     => 'DEFAULT \'file\'',
			),
			'deleted' => array(
				'type'      => 'tinyint(1)',
				'null'      => false,
				'extra'     => '',
			),
			'timestamp' => array(
				'type'      => 'tinyint(1)',
				'null'      => false,
				'extra'     => 'DEFAULT CURRENT_TIMESTAMP',
			),
		),
	);

	$table_staging_files_settings = array(
		'name'    => 'staging_files_settings',
		'columns' => array(
			'notes' => array(
				'type'      => 'text',
				'null'      => false,
				'extra'     => '',
			),
		),
		'insert'  => array(
			array(
				'notes' => '',
			),
		),
	);

	$tables[] = $table_staging_files_ignored;
	$tables[] = $table_staging_files_pushed;
	$tables[] = $table_staging_files_settings;