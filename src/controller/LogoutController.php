<?php

final class LogoutController extends ProtobuildController {
  
  public function processRequest(array $data) {
    $this->getSession()->logout();
    header('Location: /index');
    die();
  }
  
}