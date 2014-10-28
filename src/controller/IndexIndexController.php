<?php

final class IndexIndexController extends ProtobuildController {
 
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequest($data);
    
    header('Content-Type: text/plain');
    echo $package->getGitURL()."\r\n";
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    
    if (count($versions) !== 0) {
      $versions_grouped = mgroup($versions, 'getVersionName');
      $versions_items = array();
      
      foreach ($versions_grouped as $version_name => $version_platforms) {
        echo $version_name." ".$version_name."\r\n";
      }
    }
    
    $branches = id(new BranchModel())->loadAllForPackage($user, $package);
    
    if (count($branches) !== 0) {
      foreach ($branches as $branch) {
        echo $branch->getBranchName()." ".$branch->getVersionName()."\r\n";
      }
    }
    
    return '';
  }
  
}
