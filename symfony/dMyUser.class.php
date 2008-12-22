<?php

class dMyUser extends sfGuardSecurityUser
{

  public function getAccountId(){
    return $this->getGuardUser()->getProfile()->getAccountId();
  }

  public function getEmail(){
    return $this->getGuardUser()->getUsername();
  }

  public function signOut()
  {
    $this->removeAttributes('symfony/user/sfUser/attributes');
    $this->removeAttributes();
    parent::signOut();
  }

  public function setError($message){
    sfContext::getInstance()->getLogger()->err($message);
    return $this->setFlash('error',$message);
  }

  public function setSuccess($message){
    sfContext::getInstance()->getLogger()->info($message);
    return $this->setFlash('success',$message);
  }

  /** User attribute parameters (stored in the session until removed) */
  public function removeAttribute($name, $ns = null)
  {
    $this->getAttributeHolder()->remove($name, $ns);
  }

  public function getAttributes($ns = null)
  {
    return $this->getAttributeHolder()->getAll($ns);
  }

  public function removeAttributes($ns = null)
  {
    $this->getAttributeHolder()->removeNamespace($ns);
  }

  /** User parameter parameters (erased after every request) */
  public function removeParameter($name, $ns = null)
  {
    $this->getParameterHolder()->remove($name, $ns);
  }

  public function getParameters($ns = null)
  {
    return $this->getParameterHolder()->getAll($ns);
  }

  public function removeParameters($ns = null)
  {
    $this->getParameterHolder()->removeNamespace($ns);
  }

}
