<?php

class dCache {

	// object instance
	// instance of the cache class
	private static
		$instance,
		$cache,
		$lifetime = null;

	// The protected construct prevents instantiating the class externally.  The construct can be
	// empty, or it can contain additional instructions...
	protected function __construct() {
		if(sfConfig::get('sf_cache')){
			try{
				return self::$cache = new sfMemcacheCache(sfConfig::get('app_server_memcache_init'));
			} catch (sfInitializationException $e){
				return self::$cache = new sfFileCache(array_merge(sfConfig::get('app_server_filecache_init'), array(
					'cache_dir' => sfConfig::get('sf_app_base_cache_dir').DIRECTORY_SEPARATOR.$cache_key,
				)));
			}
		}else{
			return self::$cache = new sfNoCache();
		}
	}

	// The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
	// thus eliminating the possibility of duplicate objects.  The methods can be empty, or
	// can contain additional code (most probably generating error messages in response
	// to attempts to call).
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Deserializing is not allowed.', E_USER_ERROR);
	}

	//This method must be static, and must return an instance of the object if the object
	//does not already exist.
	public static function getInstance() {
		if (!self::$instance instanceof self) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function getCache(){
		return self::$cache;
	}

	public static function getLifetime(){
		return self::$lifetime;
	}

	public static function setLifetime($lifetime){
		self::$lifetime = $lifetime;
	}

	/**
	 * This routes requests through memcached.
	 * Used to get single values from foreign tables.
	 *
	 * @param string $peername
	 * @param string $FK
	 * @param string $key_value
	 * @param string $foreign_getter
	 * @param string $foreign_select
	 * @return string
	 *
	 * @example
	 * 		$state_id = 15;
	 * 		$state_abbrev = dCache::getInstance()->getForeignValue('StatesPeer', 'STATE_ID', $state_id, 'getAbbrev');
	 */
	public function getForeignValue($peername, $FK_key, $FK_value, $foreign_getter, $foreign_selector='doSelectOne'){
		if($FK_value==null){
			// we don't need to look because we have no value here
			return null;
		}
		// peername::FK, this->key, that->getter
		$FK_name = constant($peername.'::'.$FK_key);
		$cache_key = $FK_name.$FK_value.$foreign_getter;

		if(!$output = self::getCache()->get($cache_key)){
			sfContext::getInstance()->getLogger()->notice('memcached MISS: '.$cache_key);
			$c = new Criteria();
			$c->add($FK_name,$FK_value);
			$rs = call_user_func($peername.'::'.$foreign_selector,$c);
			//$rs = call_user_method('doSelectOne',$peername,$c);
			$output = $rs!=null ? $rs->$foreign_getter() : null;
			self::getCache()->set($cache_key, $output, self::$lifetime);
		}else{
			sfContext::getInstance()->getLogger()->info('memcached HIT: '.$cache_key);
		}
		return $output;
	}

	public function doSelectStmt($peername, $cache_key){
		if(!$rows = unserialize(dCache::getInstance()->getCache()->get($cache_key))){
			sfContext::getInstance()->getLogger()->notice('memcached MISS: '.$cache_key);
			dUtils::debug('building cache...');
			$stmt = call_user_func($peername.'::doSelectStmt', $c);
			while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$rows[] = $row;
			}
			dCache::getInstance()->getCache()->set($cache_key, serialize($rows));
		}else{
			sfContext::getInstance()->getLogger()->info('memcached HIT: '.$cache_key);
		}
		return $rows;
	}

	/**
	 * A wrapper for sfFunctionCache and sfMemcacheCache
	 *
	 * @param string $call The function to call
	 * @param array $arguments The array of arguments to send to the function
	 * @return mixed Returns the output of the function
	 *
	 * @example
	 */
	public function memcacheFunctionCache($call, $arguments = array()){
		$fc = new sfFunctionCache(self::getCache());
		return $fc->call($call, $arguments);
	}
}
