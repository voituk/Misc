<?php
	/**
	* My database layer class
	* I've tried to implement compatible with PEAR::DB_Common class to perform smooth migration from PEAR::DB packacge to modern PDO 
	*/
	
	class Database extends PDO {
		
		const FETCH_DEFAULT  = 0;
		
		/* These values are inherited from parent PDO-class */
		//const FETCH_ASSOC    = PDO::FETCH_ASSOC;
		//const FETCH_NUM      = PDO::FETCH_NUM;
		//const FETCH_OBJ      = PDO::FETCH_OBJ;
		//const FETCH_KEY_PAIR = PDO::FETCH_KEY_PAIR;
		//const FETCH_CLASS    = PDO::FETCH_CLASS;
		//const FETCH_UNIQUE   = PDO::FETCH_UNIQUE;
		
		
		private $dsn;
		private $user;
		private $pass;
		private $prefix;
		private $fetchMode;
		
		/* connection cache */
		private static $instances;
		
		/**
		* @param $useCache - specify to use the inprocess connection cache 
		*/
		public static function connectDefault($useCache = true) {
			global $Config;
			
			$key = md5(serialize($Config->database));
			if ($useCache && self::$instances[$key] instanceof Database)
				return self::$instances[$key];
			return self::$instances[$key] = new Database($Config->database->dsn, $Config->database->user, $Config->database->pass, $Config->database->options);
		}
		
		/**
		* @param $options - Connection options
		* 		prefix    - Database tables prefix. ##_ will be replaced by this prefix
		*		charset   - Connection charset, <code>UTF8</code> by default
		*/
		public function __construct($dsn, $user, $pass, $options=array() ) {
			parent::__construct($dsn, $user, $pass);
			
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->setFetchMode( isset($options['fetchmode']) ? $options['fetchmode'] : PDO::FETCH_ASSOC );
			
			$this->dsn    = $dsn;
			$this->user   = $user;
			$this->pass   = $pass;
			$this->prefix = empty($options['prefix']) ? null : $options['prefix'];
			
			if (!empty($options['charset']))
				$this->exec('SET NAMES '.$options['charset']);
		}
		
		
		public function setFetchMode($fetchMode) {
			$this->fetchMode = $fetchMode;
		}
		
		
		/**
		* Returns the value of the first col in first row of result set
		* @param string    $sql - SQL statement
		* @param array|int $arr - list of params
		* @example $database->getOne('SELECT * FROM table WHERE a=? AND b=?', 1, 2)
		*/
		public function getOne($sql) {
			if (func_num_args()==2) 
				$arr = func_get_arg(1);
			else {
				$arr = func_get_args();
				array_shift($arr);
			}
			$arr = is_null($arr) || $arr===false ? array() : $arr;
			settype($arr, 'array');
			$stmt = $this->prepare( $sql );
			$this->bindParams($stmt, $arr);
			$stmt->execute();
			$r = $stmt->fetch(PDO::FETCH_NUM);
			$stmt->closeCursor();
			unset($stmt);
			return $r[0];
		}
		
		public function getCol($sql) {
			if (func_num_args()==2) 
				$arr = func_get_arg(1);
			else {
				$arr = func_get_args();
				array_shift($arr);
			}
			$arr = is_null($arr) || $arr===false ? array() : $arr;
			settype($arr, 'array');
			
			$stmt = $this->prepare($sql);
			$this->bindParams($stmt, $arr);
			$stmt->execute();
			$r = $stmt->fetchAll(PDO::FETCH_COLUMN);
			$stmt->closeCursor();
			unset($stmt);
			return $r;
		}
		
		
		public function getRow( $sql, $arr=null, $fetchMode=Database::FETCH_DEFAULT ) {
			$arr = is_null($arr) || $arr===false ? array() : $arr;
			settype( $arr, 'array' );
			$stmt = $this->prepare( $sql );
			$this->bindParams($stmt, $arr);
			$stmt->execute();
			$r = $stmt->fetch( $this->fetchMode($fetchMode) );
			$stmt->closeCursor();
			unset($stmt);
			return $r;
		}
		
		
		/**
		* 
		*/
		public function getAll($sql, $arr=null, $fetchMode=Database::FETCH_DEFAULT ) {
			$arr = is_null($arr) || $arr===false ? array() : $arr;
			settype( $arr, 'array' );
			$stmt = $this->prepare( $sql );
			$this->bindParams($stmt, $arr);
			$stmt->execute();
			$r = $stmt->fetchAll( $this->fetchMode($fetchMode) );
			$stmt->closeCursor();
			unset($stmt);
			return $r;
		}
		
		
		/**
		* Executes an SQL statement, returning a result set as a PDOStatement object
		* @return PDOStatement
		*/
		public function query($sql, $arr=null, $fetchMode = Database::FETCH_DEFAULT) {
			$arr = is_null($arr) || $arr===false ? array() : $arr;
			settype($arr, 'array');
			$fetchMode = $this->fetchMode($fetchMode);
			
			// parent class interface
			if (count($arr)==0)
				return parent::query($sql, $fetchMode);
			
			$stmt = $this->prepare( $sql );
			$stmt->setFetchMode($fetchMode);
			$this->bindParams($stmt, $arr);
			$stmt->execute();
			return $stmt;
		}
		
		
		/**
		* Wrapper over the PDO::exec(string $stmt) function
		* This method should be used for INSERT/UPDATE/DELETE statements
		* @param string $stmt
		* @param array|list ... - Params
		* @return int - Number of affected rows during INSERT/DELETE/UPDATE statement
		*/
		public function exec( $sql ) {
			settype($sql, 'string');
			if ( func_num_args()==2 ) {
				$arr = func_get_arg(1);
			} else {
				$arr = func_get_args();
				array_shift($arr);
			}
			$arr = is_null($arr) || $arr===false ? array() : $arr;
			settype( $arr, 'array' );
			// Let`s save previous native behavior
			if (count($arr) == 0)
				return parent::exec( $sql );
			$st = $this->prepare( $sql );
			$this->bindParams($st, $arr);
			$st->execute();
			$r = $st->rowCount();
			$st->closeCursor();
			unset($st);
			return $r;
		}
		
		
		/**
		* @param string - $sql
		* @param array|mixed - sql params [optional]
		* @param Closure
		* @return number of rows processed
		* 
		* @example
		*	$db->eachRow('show tables', 'print_r');
		*
		* @example
		* 	$db->eachRow("show tables like ? ", "%wap_%", function ($it) {
		*		print_r($it);
		*	});
		* 
		* @example
		*	$db->eachRow("select concat(?, ?, ?)", '100',' + ', '200', 'var_dump');
		*/
		public function eachRow($sql, $func) {
			$args = func_get_args();
			$sql  = array_shift($args);
			$func = array_pop($args);
			if (!is_callable($func))
				throw ErrorException('Invalid last argument type. It should be callable.');
			
			$n = 0;
			foreach($this->query($sql, $args, Database::FETCH_OBJ) as $it) {
				call_user_func($func, $it);
				$n++;
			}
			return $n;
		}
		
		/**
		* @param string $tableName - Database table name
		* @param array $data
		* @return number of rows affected
		*/
		public function insert($tableName, Array $data, $forceReplace=false) {
			$sql = '';
			$arr = array();
			foreach ($data as $fn=>$fv) {
				$sql   .= ($sql ? ', ': '' ) . "`$fn`=?";
				$arr[] = $fv;
			}
			return $this->exec( ($forceReplace ? "REPLACE" : "INSERT"). " INTO `$tableName` SET $sql", $arr);
		}

		
		
		//////////////// Non-public methods ////////////////////////////////////
		
		protected function fetchMode($fetchMode=Database::FETCH_DEFAULT) {
			return $fetchMode==Database::FETCH_DEFAULT ? $this->fetchMode : $fetchMode;
		}
		
		/**
		*  binds PDOStatement parameters with exact types
		*/
		private function bindParams(PDOStatement $st, $arr) {
			settype($arr, 'array');
			for ($len = count($arr), $i=0; $i<$len; ++$i)
				if (is_int($arr[$i]))
					$st->bindValue($i+1, $arr[$i], PDO::PARAM_INT);
				else if (is_null($arr[$i]))
					$st->bindValue($i+1, $arr[$i], PDO::PARAM_NULL);
				else
					$st->bindValue($i+1, $arr[$i]);
		}
		
	}
?>
