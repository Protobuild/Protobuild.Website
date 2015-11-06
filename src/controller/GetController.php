<?php

final class GetController extends ProtobuildController {
 
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    throw new ProtobuildRedirectException("https://github.com/hach-que/Protobuild/raw/master/Protobuild.exe");
  }
  
}
