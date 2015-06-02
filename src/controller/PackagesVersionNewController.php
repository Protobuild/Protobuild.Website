<?php

final class PackagesVersionNewController extends ProtobuildController {
  
  public function processApi(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    if (isset($_POST['version']) && isset($_POST['platform'])) {
      $value_version = $_POST['version'];
      $value_platform = $_POST['platform'];
      
      if (strlen($value_version) === 0) {
        throw new ProtobuildException('No Git hash (version name) specified');
      }
      
      if (preg_match('/^[0-9a-f]{40}$/', $value_version) !== 1) {
        throw new ProtobuildException(
          'Git hash (version name) is not an SHA1 hash');
      }
      
      // TODO Validate platform
      
      $existing = id(new VersionModel())
        ->loadByPackagePlatformAndVersion(
          $user,
          $package,
          $value_platform,
          $value_version);
      if ($existing !== null) {
        if ($existing->getHasFile()) {
          throw new ProtobuildException(
            'Another version already exists with this Git hash and platform');
        } else {
          // Allow this API endpoint to be used to upload missing files as well.
          $version = $existing;
        }
      } else {
        $version = id(new VersionModel())
          ->setGoogleID($user->getGoogleID())
          ->setPackageName($package->getName())
          ->setPlatformName($value_platform)
          ->setVersionName($value_version)
          ->create();
      }
    
      $filename = $version->getFilenameForStorage();
      $resume_uri = id(new ResumableUpload())->getResumableURI($filename);
      
      $end_uri = $package->getURI($user, 'version/upload/'.$version->getKey());
      
      return array(
        'uploadUrl' => $resume_uri,
        'finalizeUrl' => make_api_url(ProtobuildEnv::get('domain').$end_uri),
      );
    }
    
    throw new ProtobuildException(CommonErrors::MISSING_INFORMATION);
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
          ->setGoogleID($user->getGoogleID())
          ->setPackageName($package->getName())
          ->setPlatformName($value_platform)
          ->setVersionName($value_version)
          ->create();
        
        throw new ProtobuildRedirectException(
          $package->getURI($user, 'version/upload/'.$version->getKey()));
      }
    }
    
    $options = array();
    switch ($package->getType()) {
      case PackageModel::TYPE_GLOBAL_TOOL:
        $options = array(
          'Linux' => 'Linux',
          'MacOS' => 'MacOS',
          'Windows' => 'Windows',
        );
        break;
      case PackageModel::TYPE_LIBRARY:
        $options = array(
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
        );
        break;
      case PackageModel::TYPE_TEMPLATE:
        $options = array('Template' => 'Template');
        break;
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
          ->setOptions($options))
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