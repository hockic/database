<?php defined('SYSPATH') or die('No direct script access.');


return array
(
	'pdo'	=> array(
		/**
		 * The following options are available for PDO:
		 * 
		 * string	username	Database username
		 * string	password	Database password
		 * string	dsn			Data Source Name
		 * boolean	persistent	Use persistent connections?
		 */
		'username'		=> '',
		'password'		=> '',
		'dsn'			=> 'mysql:host=localhost;dbname=db_name;charset=utf8',
		'persistent'	=> FALSE,
	),
);