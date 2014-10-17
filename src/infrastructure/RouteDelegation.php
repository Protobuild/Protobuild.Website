<?php

final class RouteDelegation {
  
  public function getRoutes() {
    return array(
      '/' => 'HomeController',
      '/index' => 'IndexController',
      '/oauth2callback' => 'OAuth2CallbackController',
      '/logout' => 'LogoutController',
      '/account/name' => 'AccountNameController',
      '/packages/new' => 'PackagesEditController',
      '/packages/edit/(?P<name>[^/]+)' => 'PackagesEditController',
      '/packages/manage' => 'PackagesManageController',
      '/(?P<user>[^/]+)' => 'AccountViewController',
      '/(?P<user>[^/]+)/' => 'AccountViewController',
    );
  }
  
  public function getControllerAndDataForUri($path) {
    $mapper = new AphrontURIMapper($this->getRoutes());
    return $mapper->mapPath($path);
  }
  
}