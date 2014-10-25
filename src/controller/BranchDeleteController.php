<?php

final class BranchDeleteController extends ProtobuildController {
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $branch_name = idx($data, 'name');
    $branch = id(new BranchModel())
      ->loadAllForPackage($user, $package);
    $branch = mpull($branch, null, 'getBranchName');
    $branch = idx($branch, $branch_name);
    
    if ($branch === null) {
      throw new Protobuild404Exception(CommonErrors::BRANCH_NOT_FOUND);
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Delete Branch');
    
    if (isset($_POST['__submit__'])) {
      $branch->delete();
      throw new ProtobuildRedirectException($package->getURI($user));
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