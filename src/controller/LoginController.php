<?php

final class LoginController extends ProtobuildController {
  
  public function processRequest(array $data) {
    // This only occurs after the user is authenticated.
    throw new ProtobuildRedirectException($this->getUser()->getURI());
  }
  
}