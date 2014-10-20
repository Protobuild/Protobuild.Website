<?php

final class PackagesVersionDeleteController extends ProtobuildController {
  
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
    
    if ($user->getGoogleID() !== $version->getGoogleID() ||
      $package->getName() !== $version->getPackageName()) {
      
      // TODO Show 404 user not found
      // This happens if you use an upload ID for a different
      // user than the one you're looking at
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Delete Version');
    
    if (isset($_POST['__submit__'])) {
      
      if ($version->getHasFile()) {
        // Delete the file from Google Cloud Storage first.
        $storage = id(new GoogleService())->getGoogleCloudStorage();
        
        $filename = $version->getKey().'.tar.gz';
    
        $object = new Google_Service_Storage_StorageObject();
        $object->setName($filename);
      
        $storage->objects->delete('protobuild-packages', $object); 
      
        $version
          ->setHasFile(false)
          ->update();
      }
      
      $version->delete();
      
      header('Location: '.$package->getURI($user));
      die();
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(phutil_tag(
          'p',
          array(),
          'Package versions can\'t be changed once they have been created.  '.
          'The only option is to delete the package version.'
        ))
        ->appendChild(phutil_tag(
          'p',
          array(),
          '(Note that if you need an alias for actual version numbers, you '.
          'can use branches for that purpose)'
        ))
        ->appendChild(phutil_tag(
          'p',
          array(),
          phutil_tag('strong', array(), 'Really delete this package version?')
        ))
        ->appendChild(id(new FormSubmit())
          ->setText('Delete this package version permanently!')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}