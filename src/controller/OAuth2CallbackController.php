<?php

final class OAuth2CallbackController extends ProtobuildController {
  
  public function processRequest(array $data) {
    $return = $_SESSION['return'];
    unset($_SESSION['return']);
    throw new ProtobuildRedirectException($return);
  }
  
}