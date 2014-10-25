<?php

final class PackagesVersionDeleteController extends ProtobuildController {
  
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
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Delete Version');
    
    if (isset($_POST['__submit__'])) {
      
      if ($version->getHasFile()) {
        // Delete the file from Google Cloud Storage first.
        $storage_delete = id(new StorageDelete());
        
        $filename = $version->getKey().'.tar.gz';
        
        try {
          $storage_delete->deleteFile($filename); 
        } catch (Exception $ex) {
          throw new ProtobuildException(
            'Error while deleting '.$filename.' from Google Storage: '.
            $ex->getMessage());
        }
      
        $version
          ->setHasFile(false)
          ->update();
      }
      
      $version->delete();
      
      throw new ProtobuildRedirectException($package->getURI($user));
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