<?php

/**
 * Subclass for representing a row from the 'billing' table.
 *
 *
 *
 * @package lib.model
 */
class Billing extends BaseBilling
{

  /**
   * Gets the state abbreviation from the states table, based on state_id
   *
   * @return unknown
   */
  function getShipStateAbbrev(){
    return $this->getForeignId('StatesPeer','STATE_ID','getShipState','getAbbrev');
  }
  
  /**
   * This routes requests through memcached.
   * Used to get single values from foreign tables.
   *
   * @param string $peername
   * @param string $FK
   * @param string $local_getter
   * @param string $foreign_getter
   * @param string $foreign_select
   * @return string
   */
  public function getForeignId($peername,$FK,$local_getter,$foreign_getter,$foreign_selector='doSelectOne'){
    // peername::FK, this->key, that->getter
    $FK_name = constant($peername.'::'.$FK);
    $key_value = $this->$local_getter();
    $cache_key = $FK_name.$key_value.$foreign_getter;
    $cache = new sfMemcacheCache(sfConfig::get('app_server_memcache_init'));
    if(!$output = $cache->get($cache_key)){
      $c = new Criteria();
      $c->add($FK_name,$key_value);
      $rs = call_user_func($peername.'::'.$foreign_selector,$c);
      //$rs = call_user_method('doSelectOne',$peername,$c);
      $output = $rs!=null ? $rs->$foreign_getter() : null;
      $cache->set($cache_key, $output);
    }
    return $output;
  }

  public function save($con = null){
    if ($con === null) {
      $con = Propel::getConnection(sfConfig::get('app_db_master'));
    }
    parent::save($con);
  }

  public function delete($con = null)
  {
    if ($con === null) {
      $con = Propel::getConnection(sfConfig::get('app_db_master'));
    }
    parent::delete($con);
  }

}
