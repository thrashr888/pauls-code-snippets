<?php

class setupVarsFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $vars = $this->getParameter('vars',null);
    for ($i=0; $i < $this->getContext()->getActionStack()->getSize(); $i++) {
      foreach($vars as $key=>$value){
        $this->getContext()->getActionStack()->getEntry($i)->getActionInstance()->$key = $value;
      }
    }
    // Execute next filter
    $filterChain->execute();
  }
}
