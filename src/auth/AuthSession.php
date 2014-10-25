<?php

final class AuthSession {
  
  private $client;
  
  public function start() {
    @session_start();
    
    $this->client = id(new GoogleService())->getGoogleWebClient();
    
    if (isset($_GET['code'])) {
      $this->client->authenticate($_GET['code']);
      $_SESSION['token'] = $this->client->getAccessToken();
      
      $oauth = new Google_Service_Oauth2($this->client);
      $_SESSION['realName'] = $oauth->userinfo->get()->name;
      $_SESSION['id'] = $oauth->userinfo->get()->id;
    }
    
    if (isset($_SESSION['token'])) {
      $this->client->setAccessToken($_SESSION['token']);
    }
  }
  
  public function logout() {
    $this->client->revokeToken();
    unset($_SESSION['token']);
  }
  
  public function getRealName() {
    return $_SESSION['realName'];
  }
  
  public function getUserID() {
    return $_SESSION['id'];
  }
  
  public function isAuthenticated() {
    return isset($_SESSION['token']);
  }
  
  public function authenticate() {
    if ($this->client->getAccessToken()) {
      return;
    }
    
    $_SESSION['return'] = $_REQUEST['__path__'];
    
    $this->client->setScopes(array('profile'));
    
    $auth = $this->client->createAuthUrl();
    throw new ProtobuildRedirectException($auth);
  }
  
}