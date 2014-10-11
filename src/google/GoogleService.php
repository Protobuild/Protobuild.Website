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
  
}
