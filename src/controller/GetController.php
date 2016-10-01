<?php

final class GetController extends ProtobuildController {
 
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    switch (idx($data, 'platform')) {
      case 'windows':
        $uri = "https://s3.amazonaws.com/redpointx/ProtobuildWebInstall.exe";
        break;
      case 'mac':
        $uri = "https://s3.amazonaws.com/redpointx/ProtobuildMacOSInstall.sh";
        break;
      case 'linux':
        $uri = "https://s3.amazonaws.com/redpointx/ProtobuildLinuxInstall.sh";
        break;
      default:
        $uri = "https://github.com/Protobuild/Protobuild/raw/master/Protobuild.exe";
        break;
    }
  
    throw new ProtobuildRedirectException($uri);
  }
  
}
