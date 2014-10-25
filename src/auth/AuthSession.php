<?php

final class AuthSession {
  
  private $client;
  private $id;
  private $token;
  private $realName;
  private $authenticated = false;
  
  public function start($is_api) {
    if ($is_api) {
      
      $api_key = idx($_POST, '__apikey__');
      
      // TODO: Implement API keys
      
    } else {
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
    
      $this->id = idx($_SESSION, 'id');
      $this->token = idx($_SESSION, 'token');
      $this->realName = idx($_SESSION, 'realName');
      $this->authenticated = isset($_SESSION['token']);
    }
  }
  
  public function logout() {
    $this->client->revokeToken();
    unset($_SESSION['token']);
  }
  
  public function getRealName() {
    return $this->realName;
  }
  
  public function getUserID() {
    return $this->id;
  }
  
  public function isAuthenticated() {
    return $this->authenticated;
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