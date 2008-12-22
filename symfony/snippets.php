<?php

$cache = new sfMemcacheCache(sfConfig::get('app_server_memcache_init'));
if(!$email_settings = $cache->get('sf_app_config_email_subjects')){
	$email_settings = sfYaml::load(sfConfig::get('sf_app_config_dir')."/email.yml");
	$cache->set('sf_app_config_email_subjects', $email_settings);
}

$fc = new sfFunctionCache(new sfMemcacheCache(sfConfig::get('app_server_memcache_init')));
return $fc->call('StatesPeer::getAbbrevById',array($this->getState()));

$key = sfConfig::get('model_'.get_class($this).$this->getPrimaryKey().'getStateAbbrev');
$cache = new sfMemcacheCache(sfConfig::get('app_server_memcache_init'));
if(!$email_settings = $cache->get($key)){
	$output = StatesPeer::getAbbrevById($this->getState());
	$cache->set($key, $output);
}
return $output;

return dUtils::memcacheFunctionCache('StatesPeer::getAbbrevById',array($this->getState()));

class dUtils{
	/**
	 * A wrapper for sfFunctionCache and sfMemcacheCache
	 *
	 * @param string $call The function to call
	 * @param array $arguments The array of arguments to send to the function
	 * @return mixed Returns the output of the function
	 */
	public static function memcacheFunctionCache($call, $arguments = array()){
		$fc = new sfFunctionCache(new sfMemcacheCache(sfConfig::get('app_server_memcache_init')));
		return $fc->call($call,$arguments);
	}
}