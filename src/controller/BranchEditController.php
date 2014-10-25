<?php

final class BranchEditController extends ProtobuildController {
  
  public function processApi(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $branch_name = idx($data, 'name');
    $is_new = $branch_name === null;
    
    $branch = null;
    if (!$is_new) {
      $branch = id(new BranchModel())
        ->loadAllForPackage($user, $package);
      $branch = mpull($branch, null, 'getBranchName');
      $branch = idx($branch, $branch_name);
      
      if ($branch === null) {
        // Allow this endpoint to be used to create branches, with the
        // non-existing name as part of the URL.
      }
    }
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    $versions = mpull($versions, 'getVersionName', 'getVersionName');
    
    if (count($versions) === 0) {
      throw new ProtobuildException(CommonErrors::PACKAGE_HAS_NO_VERSIONS);
    }
    
    if (isset($_POST['name']) && isset($_POST['git'])) {  
      $value_name = $_POST['name'];
      $value_git = $_POST['git'];
      
      if (idx($versions, $value_git) === null) {
        throw new ProtobuildException(CommonErrors::VERSION_NOT_FOUND);
      } else {
        $existing = id(new BranchModel())
          ->loadAllForPackage($user, $package);
        $existing = mpull($existing, null, 'getBranchName');
        $existing = idx($existing, $value_name);
        
        if ($existing === null || ($branch !== null && $value_name === $branch->getBranchName())) {
          if (preg_match('/^[a-zA-Z0-9-]+$/', $value_git, $matches) === 1) {
            
            if ($branch === null) {
              $new_branch = id(new BranchModel())
                ->setGoogleID($this->getSession()->getUserID())
                ->setPackageName($package->getName())
                ->setBranchName($value_name)
                ->setVersionName($value_git)
                ->create();
            } else {
              $branch
                ->setBranchName($value_name)
                ->setVersionName($value_git)
                ->update();
            }
            
            return array('Branch updated successfully.');
          } else {
            throw new ProtobuildException(
              'Branch names can only contain letters, numbers and dashes');
          } 
        } else {
          throw new ProtobuildException(
            'You already have a branch with the same name');
        }
      }
    }
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $branch_name = idx($data, 'name');
    $is_new = $branch_name === null;
    
    $error_name = null;
    $error_git = null;
    
    $value_name = null;
    $value_git = null;
   
    $branch = null;
    if (!$is_new) {
      $branch = id(new BranchModel())
        ->loadAllForPackage($user, $package);
      $branch = mpull($branch, null, 'getBranchName');
      $branch = idx($branch, $branch_name);
      
      if ($branch === null) {
        throw new Protobuild404Exception(CommonErrors::BRANCH_NOT_FOUND);
      } else {
        $value_name = $branch->getBranchName();
        $value_git = $branch->getVersionName();
      }
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb($is_new ? 'New Branch' : 'Edit Branch');
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    $versions = mpull($versions, 'getVersionName', 'getVersionName');
    
    if (count($versions) === 0) {
      throw new ProtobuildException(CommonErrors::PACKAGE_HAS_NO_VERSIONS);
    }
    
    if (isset($_POST['name'])) {  
      $value_name = $_POST['name'];
      $value_git = $_POST['git'];
      
      if (idx($versions, $value_git) === null) {
        $error_git = 'The specified version does not exist';
      } else {
        $existing = id(new BranchModel())
          ->loadAllForPackage($user, $package);
        $existing = mpull($existing, null, 'getBranchName');
        $existing = idx($existing, $value_name);
        
        if ($existing === null || ($branch !== null && $value_name === $branch->getBranchName())) {
          if (preg_match('/^[a-zA-Z0-9-]+$/', $value_git, $matches) === 1) {
            
            if ($branch === null) {
              $new_branch = id(new BranchModel())
                ->setGoogleID($this->getSession()->getUserID())
                ->setPackageName($package->getName())
                ->setBranchName($value_name)
                ->setVersionName($value_git)
                ->create();
            } else {
              $branch
                ->setBranchName($value_name)
                ->setVersionName($value_git)
                ->update();
            }
            
            throw new ProtobuildRedirectException($package->getURI($user).'?branch=true');
          } else {
            $error_name = 
              'Branch names can only contain letters, numbers and dashes';
          } 
        } else {
          $error_name = 'You already have a branch with the same name';
        }
      }
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(id(new FormTextInput())
          ->setName('name')
          ->setLabel('Branch Name')
          ->setValue($value_name)
          ->setError($error_name)
          ->setPlaceholder('Enter branch name (letters, numbers and dashes only)'))
        ->appendChild(id(new FormSelectInput())
          ->setName('git')
          ->setLabel('Version (Git hash)')
          ->setOptions($versions)
          ->setValue($value_git)
          ->setError($error_git))
        ->appendChild(id(new FormSubmit())->setText('Save')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}