<?php

final class ProtobuildRedirectException extends Exception {
  
  private $uri;
  
  public function __construct($uri) {
    $this->uri = $uri;
  }
  
  public function getURI() {
    return $this->uri;
  }
  
}