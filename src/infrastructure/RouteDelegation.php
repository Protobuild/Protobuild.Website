<?php

final class RouteDelegation {
  
  public function getRoutes() {
    return array(
      // Public pages
      '/' => 'HomeController',
      '/index' => 'IndexController',
      
      // Login and account setup
      '/oauth2callback' => 'OAuth2CallbackController',
      '/login' => 'LoginController',
      '/logout' => 'LogoutController',
      '/account/name' => 'AccountNameController',
      
      // Package create
      '/packages/new' => 'PackagesEditController',
      
      // Viewing users and packages
      '/(?P<owner>[^/]+)(/?)' => 'AccountViewController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)(/?)' => 'PackagesViewController',
      
      // Package admin
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/edit(/?)' => 'PackagesEditController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/delete(/?)' => 'PackagesDeleteController',
      
      // Version admin
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/version/new(/?)' => 'PackagesVersionNewController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/version/upload/(?P<id>[^/]+)(/?)' => 'PackagesVersionUploadController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/version/delete/(?P<id>[^/]+)(/?)' => 'PackagesVersionDeleteController',
      
      // Branch admin
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/branch/new(/?)' => 'BranchEditController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/branch/edit/(?P<name>[^/]+)(/?)' => 'BranchEditController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/branch/delete/(?P<name>[^/]+)(/?)' => 'BranchDeleteController',
      
      // API for Protobuild itself
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/index' => 'IndexIndexController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/(?P<version>[^/]+)/platforms' => 'IndexPlatformsController',
      '/(?P<owner>[^/]+)/(?P<package>[^/]+)/(?P<version>[^/]+)/(?P<platform>[^/]+)\.tar\.gz' => 'IndexPackageController',
    );
  }
  
  public function getControllerAndDataForUri($path) {
    $mapper = new AphrontURIMapper($this->getRoutes());
    return $mapper->mapPath($path);
  }
  
}