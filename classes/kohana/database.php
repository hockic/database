<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Wrapper class for PDO
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
		$this->config = Kohana::$config->load('database.pdo');
		
		// Connect to the database
		parent::__construct($this->config['dsn'] , $this->config['username'], $this->config['password']);
		
		// Force PDO to use exceptions for all errors
		parent::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		// Set fetch mode
		parent::setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ );
		
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
		
		$result = Kohana::cache($cache_key, NULL, $lifetime);
		
		// Try to get from cache
		if( $result == NULL )
		{
			// Get from database
			$stmt = parent::prepare($sql);
			$stmt->execute($params);
			$result = ( $multiple ) ? $stmt->fetchAll() : $stmt->fetch();
			
			// Cache the result
			Kohana::cache($cache_key, $result, $lifetime);
		}
		
		return $result;
	}
}