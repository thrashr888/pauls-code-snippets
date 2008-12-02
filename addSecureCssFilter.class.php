<?php

class addSecureCssFilter extends sfFilter
{
  public function execute($filterChain)
  {
    // Execute this filter only once
    $files = $this->getParameter('files',null);
    if ($this->isFirstCall() && is_array($files))
    {
      $context = $this->getContext();
      $request = $context->getRequest();
      $response = $context->getResponse();

      // only add if we actually have an https request
      if($request->isSecure())
      {
        // request is SSL secured
        foreach($files as $file){
          $response->addStylesheet($file);
        }
      }
    }
    // Execute next filter
    $filterChain->execute();
  }
}
