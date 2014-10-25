<?php

final class Protobuild404Exception extends Exception {
  
  private $protobuildMessage;
  
  public function __construct($message) {
    $this->protobuildMessage = $message;
  }
  
  public function getProtobuildMessage() {
    return $this->protobuildMessage;
  }
  
}