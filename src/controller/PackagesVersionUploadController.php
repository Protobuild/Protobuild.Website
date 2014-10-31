<?php

final class PackagesVersionUploadController extends ProtobuildController {
  
  public function processApi(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $current_id = idx($data, 'id');
    $version = id(new VersionModel())
      ->loadByKey($current_id);
    
    if ($version === null) {
      throw new Protobuild404Exception(CommonErrors::VERSION_NOT_FOUND);
    }
  
    if ($user->getGoogleID() !== $version->getGoogleID() ||
      $package->getName() !== $version->getPackageName()) {
      throw new ProtobuildException(CommonErrors::ACCESS_DENIED);
    }
    
    if ($version->getHasFile()) {
      throw new ProtobuildException(CommonErrors::VERSION_ALREADY_HAS_FILE);
    }
    
    $filename = $version->getFilenameForStorage();
  
    $storage = id(new GoogleService())->getGoogleCloudStorage();
    
    // Allow public access.
    $acl = new Google_Service_Storage_ObjectAccessControl();
    $acl->setEntity('allUsers');
    $acl->setRole('READER');

    $storage->objectAccessControls->insert('protobuild-packages', $filename, $acl);     
    
    // Mark as uploaded.
    $version
      ->setHasFile(true)
      ->setArchiveType($_POST['archiveType'])
      ->update();
      
    return 'File marked as uploaded.';
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $current_id = idx($data, 'id');
    $version = id(new VersionModel())
      ->loadByKey($current_id);
    
    if ($version === null) {
      throw new Protobuild404Exception(CommonErrors::VERSION_NOT_FOUND);
    }
    
    if ($user->getGoogleID() !== $version->getGoogleID() ||
      $package->getName() !== $version->getPackageName()) {
      throw new ProtobuildException(CommonErrors::ACCESS_DENIED);
    }
    
    if ($version->getHasFile()) {
      throw new ProtobuildException(CommonErrors::VERSION_ALREADY_HAS_FILE);
    }
    
    $filename = $version->getFilenameForStorage();
    
    if (isset($_POST['uploaded']) && isset($_POST['archiveType'])) {
      
      $storage = id(new GoogleService())->getGoogleCloudStorage();
      
      // Allow public access.
      $acl = new Google_Service_Storage_ObjectAccessControl();
      $acl->setEntity('allUsers');
      $acl->setRole('READER');

      $storage->objectAccessControls->insert('protobuild-packages', $filename, $acl);     
      
      // Mark as uploaded.
      $version
        ->setHasFile(true)
        ->setArchiveType($_POST['archiveType'])
        ->update();
      die('Marked file as present');
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Upload Package File');
    
    $resume_uri = id(new ResumableUpload())->getResumableURI($filename);
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(id(new FormProgressFileUpload())
          ->setLabel('Package File')
          ->setName('uploadFile')
          ->setTargetURI($resume_uri)
          ->setRedirectURI($package->getURI($user))
          ->setCaption('Must be a .tar.gz file file with the appropriate project structure ')));
    
    $lzma_not_supported = 
      phutil_tag(
        'div', 
        array('class' => 'alert alert-warning', 'role' => 'alert'),
        'Due to browser limitations, you can not upload LZMA compressed '.
        'TAR files (.tar.lzma) through the web interface.  Use '.
        'Protobuild from the command line to push LZMA compressed packages.');
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
      $lzma_not_supported,
    ));
  }
  
}