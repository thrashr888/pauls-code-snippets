<?php

class addYmlFilter extends sfFilter
{
  public function execute($filterChain)
  {
    // Execute this filter only once
    $files = $this->getParameter('files',null);
    if ($this->isFirstCall() && is_array($files))
    {
      // Filters don't have direct access to the request and user objects.
      // You will need to use the context object to get them
      foreach($files as $file){
        @include(sfContext::getInstance()->getConfigCache()->checkConfig('config/'.$file.'.yml'));
      }
    }
    // Execute next filter
    $filterChain->execute();
  }
}
