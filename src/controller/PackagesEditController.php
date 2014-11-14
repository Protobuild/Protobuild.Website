<?php

final class PackagesEditController extends ProtobuildController {
  
  public function processRequest(array $data) {
    $current_name = idx($data, 'package');
    $is_new = $current_name === null;
    
    if ($is_new) {
      $user = $this->loadOwnerFromRequestAndRequireEdit($data);
    } else {
      list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    }
    
    $error_type = null;
    $error_name = null;
    $error_git = null;
    $error_desc = null;
    
    $value_type = null;
    $value_name = null;
    $value_git = null;
    $value_desc = null;
    
    $caption_name = '';
    if (!$is_new) {
      $caption_name = 'Packages can not be renamed after they are created.';
    }
    
    $caption_type = '';
    if (!$is_new) {
      $caption_type = 'Packages can not change type after they are created.';
    }
    
    $current = null;
    if (!$is_new) {
      $current = id(new PackageModel())
        ->loadByUserAndName($user, $current_name);
      
      if ($current === null) {
        throw new Protobuild404Exception(CommonErrors::PACKAGE_NOT_FOUND);
      } else {
        $value_name = $current->getName();
        $value_type = $current->getType();
        $value_git = $current->getGitURL();
        $value_desc = $current->getDescription();
      }
    }
    
    if ($is_new) {
      $breadcrumbs = $this->createBreadcrumbs($user);
      $breadcrumbs->addBreadcrumb('New Package');
    } else {
      $breadcrumbs = $this->createBreadcrumbs($user, $current);
      $breadcrumbs->addBreadcrumb('Edit Package');
    } 
    
    $success = false;
    if (isset($_POST['__submit__'])) {  
      
      if ($is_new) {
        $value_name = $_POST['name'];
        $value_type = $_POST['type'];
      } else {
        $value_name = $current->getName();
        $value_type = $current->getType();
      } 
      
      $value_git = $_POST['git'];
      $value_desc = $_POST['desc'];
      
      $existing = id(new PackageModel())
        ->loadByUserAndName($user, $value_name);
      
      if ($existing === null || ($current !== null && $value_name === $current->getName())) {
        if (preg_match('/^[a-zA-Z0-9-\.]+$/', $value_name, $matches) === 1) {
          
          if ($current === null) {
            $package = id(new PackageModel())
              ->setName($value_name)
              ->setType($value_type)
              ->setGitURL($value_git)
              ->setDescription($value_desc)
              ->setGoogleID($user->getGoogleID())
              ->create();
            
            try {
              id(new SearchConnector())->reindexPackage($package);
            } catch (Exception $ex) {
              // Ignore re-indexing errors for now
            }
            
            throw new ProtobuildRedirectException($package->getURI($user));
          } else {
            $current
              ->setGitURL($value_git)
              ->setDescription($value_desc);
            $current->update();
            
            try {
              id(new SearchConnector())->reindexPackage($package);
            } catch (Exception $ex) {
              // Ignore re-indexing errors for now
            }
          }
          
          $success = true;
          
        } else {
          $error_name = 
            'Package names can only contain letters, numbers, dashes and dots';
        } 
      } else {
        $error_name = 'You already have a package with the same name';
      }
    }
    
    $message = null;
    if ($success) {
      $message = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-success', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Success!'),
            '  Your package has been saved successfully.'));
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(id(new FormTextInput())
          ->setName('name')
          ->setLabel('Package Name')
          ->setValue($value_name)
          ->setError($error_name)
          ->setPlaceholder('Enter package name (letters, numbers, dashes and dots only)')
          ->setDisabled(!$is_new)
          ->setCaption($caption_name))
        ->appendChild(id(new FormSelectInput())
          ->setName('type')
          ->setLabel('Package Type')
          ->setValue($value_type)
          ->setError($error_type)
          ->setDisabled(!$is_new)
          ->setCaption($caption_type)
          ->setOptions(array(
            PackageModel::TYPE_LIBRARY => 'Library',
            PackageModel::TYPE_TEMPLATE => 'Template',
          )))
        ->appendChild(id(new FormTextInput())
          ->setName('git')
          ->setLabel('Git Source URL')
          ->setValue($value_git)
          ->setError($error_git)
          ->setPlaceholder('Full URL to the Git repository (optional)'))
        ->appendChild(id(new FormTextareaInput())
          ->setName('desc')
          ->setLabel('Description')
          ->setValue($value_desc)
          ->setError($error_desc))
        ->appendChild(phutil_tag(
          'p',
          array(),
          'You will be able to upload a package file after creating your package.'))
        ->appendChild(id(new FormSubmit())
          ->setText('Save')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $message,
      $form,
    ));
  }
  
}