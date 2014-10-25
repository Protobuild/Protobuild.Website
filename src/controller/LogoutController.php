<?php

final class LogoutController extends ProtobuildController {
  
  public function processRequest(array $data) {
    $this->getSession()->logout();
    throw new ProtobuildRedirectException('/index');
  }
  
}