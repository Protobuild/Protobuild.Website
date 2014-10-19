<?php

final class PackagesVersionUploadController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $current_id = idx($data, 'id');
    $version = id(new VersionModel())
      ->loadByKey($current_id);
    
    if ($version === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    if ($user->getGoogleID() !== $version->getGoogleID()) {
      // TODO Show 404 user not found
      // This happens if you use an upload ID for a different
      // user than the one you're looking at
      header('Location: /index');
      die();
    }
    
    $package = id(new PackageModel())
      ->loadByUserAndName($user, $version->getPackageName());
    
    if ($package === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    if ($version->getHasFile()) {
      // This version already has a file.
      header('Location: /'.$user->getUser().'/'.$package->getName());
      die();
    }
    
    $filename = $version->getKey().'.tar.gz';
    
    if (isset($_POST['uploaded'])) {
      $storage = id(new GoogleService())->getGoogleCloudStorage();
      
      // Allow public access.
      $acl = new Google_Service_Storage_ObjectAccessControl();
      $acl->setEntity('allUsers');
      $acl->setRole('READER');

      $storage->objectAccessControls->insert('protobuild-packages', $filename, $acl);     
      
      // Mark as uploaded.
      $version
        ->setHasFile(true)
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
          ->setRedirectURI('/'.$user->getUser().'/'.$package->getName())
          ->setCaption('Must be a .tar.gz file with the appropriate project structure ')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}