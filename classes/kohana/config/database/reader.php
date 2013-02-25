<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Database reader for the kohana config system
 *
 * @package    Kohana/Database
 * @category   Configuration
 * @author     Kohana Team
 * @author     Safet Hočkić
 * @copyright  (c) 2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Config_Database_Reader implements Kohana_Config_Reader
{
	protected $_table_name  = 'config';

	/**
	 * Constructs the database reader object
	 *
	 * @param array Configuration for the reader
	 */
	public function __construct(array $config = NULL)
	{
		if (isset($config['table_name']))
		{
			$this->_table_name = $config['table_name'];
		}
	}

	/**
	 * Tries to load the specificed configuration group
	 *
	 * Returns FALSE if group does not exist or an array if it does
	 *
	 * @param  string $group Configuration group
	 * @return boolean|array
	 */
	public function load($group)
	{
		/**
		 * Prevents the catch-22 scenario where the database config reader attempts to load the 
		 * database connections details from the database.
		 *
		 * @link http://dev.kohanaframework.org/issues/4316
		 */
		if ($group === 'database')
			return FALSE;

		$results = DB::instance()
					->handle("
						SELECT config_key, config_value
						FROM {$this->_table_name}
						WHERE group_name = :group_name", array(':group_name' => $group))
					->fetchAll();
		
		// Initialize array
		$config = array();
		
		foreach ($results as $temp)
		{
			$config[$temp->config_key] = unserialize($temp->config_value);
		}
		
		return $config;
	}
	
}
