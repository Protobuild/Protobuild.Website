<?php

final class IndexPackageController extends ProtobuildController {
  
  protected function allowPublicAccess() {
    return true;
  } 
 
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequest($data);
    
    $platform_name = idx($data, 'platform');
    $version_name = idx($data, 'version');
    
    if ($platform_name === null || $version_name === null) {
      throw new Protobuild404Exception(CommonErrors::MISSING_INFORMATION);
    }
      
    // Attempt to resolve version_name as a branch.
    $branches = id(new BranchModel())->loadAllForPackage($user, $package);
    $branches = mpull($branches, 'getVersionName', 'getBranchName');
    $version_name = idx($branches, $version_name, $version_name);
    
    $version = id(new VersionModel())->loadByPackagePlatformAndVersion(
      $user,
      $package,
      $platform_name,
      $version_name);
    
    if ($version === null) {
      throw new Protobuild404Exception(CommonErrors::VERSION_NOT_FOUND);
    }
    
    $id = $version->getKey();
    
    $filename = $id.'.tar.gz';
    
    $object = new Google_Service_Storage_StorageObject();
    $object->setName($filename);
    
    $client = id(new GoogleService())->getGoogleServiceClient();
    $storage = id(new GoogleService())->getGoogleCloudStorage();
    $result = $storage->objects->get('protobuild-packages', $object);
    
    $download_url = 'https://storage.googleapis.com/protobuild-packages/'.$filename;
    throw new ProtobuildRedirectException($download_url);
  }
  
}
