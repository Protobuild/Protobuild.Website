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
      '/packages/version/new/(?P<name>[^/]+)' => 'PackagesVersionNewController',
      '/packages/version/upload/(?P<id>[^/]+)' => 'PackagesVersionUploadController',
      '/packages/manage' => 'PackagesManageController',
      '/(?P<user>[^/]+)' => 'AccountViewController',
      '/(?P<user>[^/]+)/' => 'AccountViewController',
      '/(?P<user>[^/]+)/(?P<name>[^/]+)' => 'PackagesViewController',
      '/(?P<user>[^/]+)/(?P<name>[^/]+)/' => 'PackagesViewController',
      '/(?P<user>[^/]+)/(?P<name>[^/]+)/index' => 'IndexIndexController',
      '/(?P<user>[^/]+)/(?P<name>[^/]+)/(?P<version>[^/]+)/platforms' => 'IndexPlatformsController',
      '/(?P<user>[^/]+)/(?P<name>[^/]+)/(?P<version>[^/]+)/(?P<platform>[^/]+)\.tar\.gz' => 'IndexPackageController',
    );
  }
  
  public function getControllerAndDataForUri($path) {
    $mapper = new AphrontURIMapper($this->getRoutes());
    return $mapper->mapPath($path);
  }
  
}