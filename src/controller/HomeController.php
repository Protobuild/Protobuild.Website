<?php

final class HomeController extends ProtobuildController {
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    return $this->buildApplicationPage(
      array(
        new HomeBannerControl(),
        new HomeFeaturesControl(),
      ));
  }
  
  protected function getNavigationName() {
    return 'home';
  }
  
}