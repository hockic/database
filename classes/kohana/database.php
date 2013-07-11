<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Wrapper class for PDO
 * 
 * @package		Kohana_Database
 * @author		Safet Hočkić - q7eb2a
 * @copyright		(c)2013 Safet Hočkić
 * @license		http://www.opensource.org/licenses/isc-license.txt
 * @link		https://github.com/q7eb2a/database
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

		if( $this->config->persistent !== FALSE )
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
	 */
	public function handle($sql, array $params = NULL)
	{
		$stmt = parent::prepare($sql);
		$stmt->execute($params);

		return $stmt;
	}

	/**
	 * Shorhand method for INSERT statements
	 * 
	 * [!] This method is not tested so be careful
	 * 
	 * @param   string     $table       table to insert into
	 * @param   array      $parameters  list of column names and values
	 */
	public function insert($table, array $parameters)
	{
		// Join keys
		$keys = implode(',', array_keys($parameters));
		
		// Prepend array keys with a colon
		$value_keys = ':' . implode(',:', array_keys($parameters));
		
		// Start a insertion query
		$query = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $value_keys . ")";

		// Initiate array for the final parameters
		$final_parameters = array();

		foreach($parameters as $key => $value)
		{
			// Prefix param keys with colons
			$final_parameters[':'.$key] = $value;
		}

		// Prepare the statement
		$stmt = parent::prepare($query);

		// Execute prepared statement
		$stmt->execute($final_parameters);

		return $stmt;
	}

	/**
	 * Shorhand method for UPDATE statements
	 * 
	 * [!] This method is not tested so be careful
	 * 
	 * @param   string     $table       table name
	 * @param   array      $parameters  list of column names and values
	 * @param   array      $where       where clause keys
	 */
	public function update($table, array $parameters, array $where = NULL)
	{
		// Start a deletion query
		$query = "UPDATE " . $table . " SET ";

		foreach($parameters as $key => $value)
		{
			// Generate column list
			$query .= $key . " = :" . $key;
			
			end($parameters);
			$query .= ($key === key($parameters)) ? " " : ", ";
		}

		// Generate where clause
		$query .= "WHERE 1";

		if($where !== NULL)
		{
			foreach($where as $key => $value)
			{
				// Add deletion conditions
				$query .= " AND " . $key . " = :where_".$key;
			}
			
			// Initiate array for the final parameters
			$final_where_parameters = array();
	
			foreach($where as $key => $value)
			{
				// Prefix param keys with colons
				$final_where_parameters[':where_'.$key] = $value;
			}
			
			$parameters = $parameters + $final_where_parameters;
		}


		// Prepare the statement
		$stmt = parent::prepare($query);

		// Execute prepared statement
		$stmt->execute($parameters);

		return $stmt;
	}

	/**
	 * Shorhand method for DELETE statements
	 * 
	 * [!] This method is not tested so be careful
	 * 
	 * @param   string       $table        table to delete from
	 * @param   array        $conditions   deletion conditions
	 * @param   int|boolean  $limit        row limit
	 */
	public function delete($table, array $conditions = NULL, $limit = 1)
	{
		// Start a deletion query
		$query = "DELETE FROM " . $table . " WHERE 1";

		if($conditions !== NULL)
		{
			foreach($conditions as $key => $value)
			{
				// Add deletion conditions
				$query .= " AND " . $key . " = :".$key;
			}
		}
		
		if($limit !== FALSE)
		{
			// Add limiting
			$query .= " LIMIT " . $limit;
		}

		// Prepare the statement
		$stmt = parent::prepare($query);

		// Execute prepared statement
		$stmt->execute($conditions);

		return $stmt;
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
	public function cached($sql, array $params = NULL, $multiple = TRUE, $lifetime = NULL, $key = NULL)
	{
		// Set the cache key based on the database instance name and SQL
		$cache_key = ($key === NULL) ? 'DB::cached('.serialize(array($sql,$params,$multiple,$lifetime)).')' : $key;
		
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
