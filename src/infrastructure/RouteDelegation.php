<?php

final class RouteDelegation {
  
  public function getRoutes() {
    return array(
      '/' => 'HomeController',
      '/index' => 'IndexController',
      '/oauth2callback' => 'OAuth2CallbackController',
      '/manage' => 'PackagesManageController',
      '/logout' => 'LogoutController',
    );
  }
  
  public function getControllerAndDataForUri($path) {
    $mapper = new AphrontURIMapper($this->getRoutes());
    return $mapper->mapPath($path);
  }
  
}