<?php

final class StorageDelete extends Phobject {
  
  public function deleteFile($filename) {
    $uri = 
      'https://www.googleapis.com/storage/v1/b/protobuild-packages/o/'.$filename;
      
    $future = new HTTPSFuture($uri);
    $future->setMethod('DELETE');
    $future->addHeader('Authorization', $this->getAuthorizationHeader($filename));
    
    list($body, $headers) = $future->resolvex();
  }
  
  private function getAuthorizationHeader($filename) {
    $client = id(new GoogleService())->getGoogleServiceClient();
    
    $httpRequest = new Google_Http_Request(
        'https://www.googleapis.com/storage/v1/b/protobuild-packages/o/'.$filename,
        'DELETE',
        null,
        null
    );
    $httpRequest = $client->getAuth()->sign($httpRequest);
    
    $headers = $httpRequest->getRequestHeaders();
    return idx($headers, 'authorization');
  }
  
}