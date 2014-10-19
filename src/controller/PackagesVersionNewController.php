<?php

final class PackagesVersionNewController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);

    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Create New Version');
    
    $error_version = null;
    $error_platform = null;
    $error_file = null;
    
    $value_version = null;
    $value_platform = null;
    
    if (isset($_POST['version'])) {
      $value_version = $_POST['version'];
      $value_platform = $_POST['platform'];
      
      if (strlen($value_version) === 0) {
        $error_version = 'No Git hash specified';
      }
      
      if (preg_match('/^[0-9a-f]{40}$/', $value_version) !== 1) {
        $error_version = 'Git hash is not an SHA1 hash';
      }
      
      // TODO Validate platform
      
      $existing = id(new VersionModel())
        ->loadByPackagePlatformAndVersion(
          $user,
          $package,
          $value_platform,
          $value_version);
      if ($existing !== null) {
        $error_version = 'Another version already exists with this Git hash and platform';
        $error_platform = 'Another version already exists with this Git hash and platform';
      }
      
      if ($error_file === null && $error_platform === null && $error_version === null) {
        $version = id(new VersionModel())
          ->setGoogleID($this->getSession()->getUserID())
          ->setPackageName($package->getName())
          ->setPlatformName($value_platform)
          ->setVersionName($value_version)
          ->create();
        
        header('Location: '.$package->getURI($user, 'version/upload/'.$version->getKey()));
        die();
      }
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(id(new FormTextInput())
          ->setName('version')
          ->setLabel('Git Hash')
          ->setValue($value_version)
          ->setError($error_version)
          ->setPlaceholder('Enter Git hash (full SHA1 Git hash only)'))
        ->appendChild(id(new FormSelectInput())
          ->setName('platform')
          ->setLabel('Platform')
          ->setValue($value_platform)
          ->setError($error_platform)
          ->setOptions(array(
            'Android' => 'Android',
            'iOS' => 'iOS',
            'Linux' => 'Linux',
            'MacOS' => 'MacOS',
            'Ouya' => 'Ouya',
            'PCL' => 'PCL',
            'PlayStation4' => 'PlayStation4',
            'PSMobile' => 'PSMobile',
            'Windows' => 'Windows',
            'Windows8' => 'Windows8',
            'WindowsPhone' => 'WindowsPhone',
            'WindowsPhone81' => 'WindowsPhone81',
            'Web' => 'Web',
          )))
        ->appendChild(phutil_tag(
          'p',
          array(),
          'You will be able to upload a package file in the next step.'))
        ->appendChild(id(new FormSubmit())
          ->setText('Create Version and continue to next step')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}