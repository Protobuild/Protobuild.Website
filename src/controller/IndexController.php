<?php

final class IndexController extends ProtobuildController {
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    return $this->buildApplicationPage(
      array(
        new IndexBannerControl(),
        new IndexFeaturesControl(),
      ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}