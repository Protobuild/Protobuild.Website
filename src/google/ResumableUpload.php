<?php

final class ResumableUpload extends Phobject {
  
  public function getResumableURI($filename) {
    $uri = 
      'https://www.googleapis.com/upload/storage/v1/b/protobuild-packages/o?uploadType=resumable';
      
    $future = new HTTPSFuture($uri);
    $future->setMethod('POST');
    $future->addHeader('Authorization', $this->getAuthorizationHeader());
    $future->addHeader('Content-Type', 'application/json');
    $future->setData(json_encode(array('name' => $filename)));
    
    list($body, $headers) = $future->resolvex();
    
    foreach ($headers as $header_arr) {
      $key = $header_arr[0];
      $value = $header_arr[1];
      
      if ($key === 'Location') {
        return $value;
      }
    }
    
    return null;
  }
  
  private function getAuthorizationHeader() {
    $client = id(new GoogleService())->getGoogleServiceClient();
    
    $httpRequest = new Google_Http_Request(
        'https://www.googleapis.com/upload/storage/v1/b/protobuild-packages/o?uploadType=resumable',
        'POST',
        null,
        null
    );
    $httpRequest = $client->getAuth()->sign($httpRequest);
    
    $headers = $httpRequest->getRequestHeaders();
    return idx($headers, 'authorization');
  }
  
}