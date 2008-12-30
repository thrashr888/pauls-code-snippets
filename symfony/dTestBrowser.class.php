<?php

class dTestBrowser extends sfTestBrowser {

  public $data = array();
  public $in_if = false;

  public function home($find = 'Home'){
    return $this->getPage('/home/index', '_HOME', 'home', 'index', $find);
  }

  public function notInBody($message='This is a temporary page'){
    return $this->inBody($message, true);
  }

  public function inBody($message, $not=false){
    return $this->checkResponseElement('body', ($not?'!':'').'/'.addcslashes($message, "/'").'/');
  }

  public function isPage($message, $module, $action, $match=false){
    $this->info("PAGE: ".$message)
    ->isStatusCode(200)
    ->isRequestParameter('module', $module)
    ->isRequestParameter('action', $action)
    ->notInBody()
    ;

    if(!empty($match) && is_array($match)){
      $this->checkResponseElement($match[0], '!/'.$match[1].'/');
    }elseif(!empty($match)){
      $this->responseContains($match);
    }
    return $this;
  }

  public function is($message, $module, $action, $match=false){
    return $this->isPage($message, $module, $action, $match);
  }

  public function getPage($uri, $message, $module, $action, $match=false){
    return $this->get($uri)
    ->isPage($message, $module, $action, $match);
  }

  public function info($message){
    $this->test()->comment('INFO: '.$message);
    return $this;
  }

  public function space(){
    $this->test()->comment(' ');
    return $this;
  }

  public function debug($mixed){
    $this->info(debug($mixed, 0, 1));
    return $this;
  }

  public function out(){
    return $this->info($this->getResponse()->getContent());
  }

  public function has($message){
    return $this->responseContains($message);
  }

  public function findData($key, $match){
    preg_match($match, $this->getResponse()->getContent(), $data);
    if(array_key_exists(1, $data)){
      $this->info("FIND DATA $key: OK");
      $this->data[$key] = $data[1];
    }else{
      $this->info("FIND DATA $key: FAIL");
      $this->data[$key] = false;
    }
    return $this;
  }

  public function setData($key, $value){
    $this->info("SET DATA $key");
    $this->data[$key] = $value;
    return $this;
  }

  /**
   * Returns the value saved in $this->data
   * NOTE: does not return $this
   *
   * @param string $key
   * @return mixed $data
   */
  public function getData($key){
    if(array_key_exists($key,$this->data)){
      return $this->data[$key];
    }else{
      return null;
    }
  }

  public function printData($key){
    return $this->info("DATA $key = ".$this->getData($key));
  }

  public function listData(){
    $this->debug($this->data);
  }

  public function pause($seconds=5){
    sleep($seconds);
    return $this;
  }

  public function if_run($test, $action, $value=false, $message=false){
    if($test){
      $this->info("IF: TRUE");
      return $this->$action($value);
    }else{
      $this->info("IF: FALSE");
      $this->in_if = true;
    }
    if($message){
      $this->info("IF: $message");
    }
    return $this;
  }

  public function else_if_run($test, $action, $value=false, $message=false){
    if($this->in_if){
      if($test){
        $this->info("IF: TRUE");
        return $this->$action($value);
      }else{
        $this->info("IF: FALSE");
        $this->in_if = true;
      }
    }
    if($message){
      $this->info("IF: $message");
    }
    return $this;
  }

  public function else_run($action, $value=false){
    if($this->in_if){
      $this->info("ELSE");
      return $this->$action($value);
    }
    return $this;
  }

  public function end_run(){
    $this->info("END IF");
    $this->in_if = false;
    return $this;
  }

  public function stop($seconds=false){
    if($seconds){
      $this->info("SHUTTING DOWN")
      ->pause($seconds);
    }else{
      $this->info("SHUTTING DOWN");
    }
    $this->shutdown();
    return new dTestBrowserStop();
    throw new Exception('Stopped.');
  }

  /*public function isStatusCode($status_code = 200){
   if($status_code==302){
   return parent::isStatusCode($status_code)->followRedirect();
   }else{
   return parent::isStatusCode($status_code);
   }
   }*/
}

class dTestBrowserStop {
  public function __call($name, $args)
  {
    echo "$name skipped\n";
    return $this;
  }
}