<?php

final class IndexIndexController extends ProtobuildController {
  
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
    echo $package->getGitURL()."\r\n";
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    
    if (count($versions) !== 0) {
      $versions_grouped = mgroup($versions, 'getVersionName');
      $versions_items = array();
      
      foreach ($versions_grouped as $version_name => $version_platforms) {
        echo $version_name."\r\n";
      }
    }
    
    return '';
  }
  
}