<?php

final class LoginController extends ProtobuildController {
  
  public function processRequest(array $data) {
    // This only occurs after the user is authenticated.
    header('Location: '.$this->getUser()->getURI());
    die();
  }
  
}