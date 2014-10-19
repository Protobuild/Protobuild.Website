<?php

final class BranchDeleteController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $branch_name = idx($data, 'name');
    $branch = id(new BranchModel())
      ->loadAllForPackage($user, $package);
    $branch = mpull($branch, null, 'getBranchName');
    $branch = idx($branch, $branch_name);
    
    if ($branch === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Delete Branch');
    
    if (isset($_POST['__submit__'])) {
      $branch->delete();
      header('Location: '.$package->getURI($user));
      die();
    }
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    $versions = mpull($versions, 'getVersionName', 'getVersionName');
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(phutil_tag('p', array(),
          'Are you really sure you want to delete "'.$branch_name.'"?'))
        ->appendChild(id(new FormSubmit())->setText('Delete!')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}