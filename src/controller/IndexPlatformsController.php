<?php

final class IndexPlatformsController extends ProtobuildController {
  
  public function processRequest(array $data) {
    
    $username = idx($data, 'user');
    $name = idx($data, 'name');
    
    if ($username === null || $name === null) {
      // TODO 404
      die();
    }
    
    $user = id(new GoogleToUserMappingModel())
      ->loadByName($username);
    
    if ($user === null) {
      // TODO 404
      die();
    }
    
    $package = id(new PackageModel())->loadByUserAndName($user, $name);
    
    if ($package === null) {
      // TODO 404
      die();
    }
    
    header('Content-Type: text/plain');
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    
    if (count($versions) !== 0) {
      $versions_grouped = mgroup($versions, 'getVersionName');
      $versions_items = array();
      
      foreach ($versions_grouped as $version_name => $version_platforms) {
        if ($version_name !== idx($data, 'version')) {
          continue;
        }
        
        foreach ($version_platforms as $platform) {
          echo $platform->getPlatformName()."\r\n";
        }
        
        return '';
      }
    }
    
    return '';
  }
  
}