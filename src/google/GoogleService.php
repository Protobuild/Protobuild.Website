<?php

final class GoogleService extends Phobject {
  
  public function getGoogleWebClient() {
    $client = new Google_Client();
    $client->setApplicationName('Protobuild Index');
    $client->setClientId(ProtobuildEnv::get("google.web.clientID"));
    $client->setClientSecret(ProtobuildEnv::get("google.web.clientSecret"));
    $client->setDeveloperKey(ProtobuildEnv::get("google.developerKey"));
    $client->setRedirectUri("http://protobuild.org/oauth2callback");
    return $client;
  }
  
  public function getGoogleServiceClient() {
    $client = new Google_Client();
    $client->setApplicationName('Protobuild Index');
    $client->setAssertionCredentials(new Google_Auth_AssertionCredentials(
      ProtobuildEnv::get("google.service.emailAddress"),
      array(
        "https://www.googleapis.com/auth/datastore",
        "https://www.googleapis.com/auth/userinfo.email",
        "https://www.googleapis.com/auth/devstorage.full_control",
      ),
      ProtobuildEnv::get("google.service.privateKey"),
      null));
    $client->setDeveloperKey(ProtobuildEnv::get("google.developerKey"));
    return $client;
  }
  
  public function getGoogleCloudDatastore() {
    return new Google_Service_Datastore($this->getGoogleServiceClient());
  }
  
  public function getGoogleCloudStorage() {
    return new Google_Service_Storage($this->getGoogleServiceClient());
  }
  
}
