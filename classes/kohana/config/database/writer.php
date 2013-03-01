<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Database writer for the config system
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @author     Safet Hočkić
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Config_Database_Writer extends Config_Database_Reader implements Kohana_Config_Writer
{
	protected $_loaded_keys = array();

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
		$config = parent::load($group);

		if ($config !== FALSE)
		{
			$this->_loaded_keys[$group] = array_combine(array_keys($config), array_keys($config));
		}

		return $config;
	}

	/**
	 * Writes the passed config for $group
	 *
	 * Returns chainable instance on success or throws 
	 * Kohana_Config_Exception on failure
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public function write($group, $key, $config)
	{
		$config = serialize($config);

		// Check to see if we've loaded the config from the table already
		if (isset($this->_loaded_keys[$group][$key]))
		{
			$this->_update($group, $key, $config);
		}
		else
		{
			// Attempt to run an insert query
			// This may fail if the config key already exists in the table
			// and we don't know about it
			try
			{
				$this->_insert($group, $key, $config);
			}
			catch (Exception $e)
			{
				// Attempt to run an update instead
				$this->_update($group, $key, $config);
			}
		}

		return TRUE;
	}

	/**
	 * Insert the config values into the table
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The serialized configuration to write
	 * @return boolean
	 */
	protected function _insert($group, $key, $config)
	{
		$key_count = DB::instance()->handle("
						SELECT config_key FROM {$this->_table_name}
						WHERE group_name = :group_name
						AND config_key = :config_key", array(
							':group_name'	=> $group,
							':config_key'	=> $key
					))->rowCount();

		// Check if key already exists
		if($key_count != 0)
			throw new Exception();

		DB::instance()->handle("
			INSERT INTO {$this->_table_name} (group_name, config_key, config_value)
			VALUES (:group_name, :config_key, :config_value)", array(
				':group_name'	=> $group,
				':config_key'	=> $key,
				':config_value'	=> $config
		));

		return $this;
	}

	/**
	 * Update the config values in the table
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The serialized configuration to write
	 * @return boolean
	 */
	protected function _update($group, $key, $config)
	{
		DB::instance()->handle("
			UPDATE {$this->_table_name} SET config_value = :config_value
			WHERE group_name = :group_name AND config_key = :config_key", array(
				':group_name'	=> $group,
				':config_key'	=> $key,
				':config_value'	=> $config
		));

		return $this;
	}
}
