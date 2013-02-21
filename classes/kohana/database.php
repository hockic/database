<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Wrapper class for PDO
 * 
 * @package Kohana_Database
 * @author Safet Hočkić - q7eb2a
 * @copyright (c)2013 Safet Hočkić
 * @see https://github.com/q7eb2a/kohana-database-module
 * @license http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_Database extends PDO {

	// Config
	protected $config;

	// Instances
	protected static $_instance;

	/**
	 * Singleton instance
	 * 
	 * @return DB
	 */
	public static function instance()
	{
		if ( ! DB::$_instance instanceof DB )
		{
			DB::$_instance = new DB();
		}

		return DB::$_instance;
	}

	public function __construct()
	{
		// Load database config
		$this->config = Kohana::$config->load('database');

		// Connect to the database
		parent::__construct($this->config->dsn , $this->config->username, $this->config->password);

		// Force PDO to use exceptions for all errors
		parent::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		// Set fetch mode
		parent::setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, $this->config->fetch_mode );

		if( $this->config['persistent'] !== FALSE )
		{
			// Make the connection persistent
			parent::setAttribute( PDO::ATTR_PERSISTENT, TRUE );
		}
	}

	/**
	 * Enables the query to be cached for a specified amount of time.
	 * 
	 * @param   string   $sql       query string
	 * @param   array    $params    list of parameters
	 * @param   boolean  $multiple  whether or not to fetch multiple results
	 * @param   integer  $lifetime  number of seconds to cache
	 * @uses    Kohana::cache
	 */
	public function cached($sql, array $params = NULL, $multiple = TRUE, $lifetime = NULL)
	{
		// Set the cache key based on the database instance name and SQL
		$cache_key = 'DB::cached('.serialize(array($sql,$params,$multiple,$lifetime)).')';
		
		// Try to get from cache
		$result = Kohana::cache($cache_key, NULL, $lifetime);

		if( $result == NULL )
		{
			// Get from database
			$stmt = parent::prepare($sql);
			$stmt->execute($params);
			$result = ( $multiple ) ? $stmt->fetchAll() : $stmt->fetch();

			if( Kohana::$caching )
			{
				// Cache the result
				Kohana::cache($cache_key, $result, $lifetime);
			}
		}

		return $result;
	}
}
