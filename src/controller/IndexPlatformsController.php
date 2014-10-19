<?php

final class IndexPlatformsController extends ProtobuildController {

  protected function allowPublicAccess() {
    return true;
  }

  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequest($data);
    
    header('Content-Type: text/plain');
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    $branches = id(new BranchModel())->loadAllForPackage($user, $package);
    $branches = mpull($branches, 'getVersionName', 'getBranchName');
    
    $resolved_name = idx($data, 'version');
    $resolved_name = idx($branches, $resolved_name, $resolved_name);
    
    if (count($versions) !== 0) {
      $versions_grouped = mgroup($versions, 'getVersionName');
      $versions_items = array();
      
      foreach ($versions_grouped as $version_name => $version_platforms) {
        if ($version_name !== $resolved_name) {
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
