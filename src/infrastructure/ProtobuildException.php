<?php

final class ProtobuildException extends Exception {
  
  private $protobuildMessage;
  
  public function __construct($message) {
    $this->protobuildMessage = $message;
  }
  
  public function getProtobuildMessage() {
    return $this->protobuildMessage;
  }
  
}