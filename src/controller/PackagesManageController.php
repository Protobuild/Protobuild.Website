<?php

final class PackagesManageController extends ProtobuildController {
  
  public function processRequest(array $data) {
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb('Manage');
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      new IndexBannerControl()
    ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}