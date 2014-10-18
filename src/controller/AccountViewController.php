<?php

final class AccountViewController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $username = idx($data, 'user');
    
    if ($username === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $user = id(new GoogleToUserMappingModel())
      ->loadByName($username);
    
    if ($user === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb($user->getUser());
    
    $packages = id(new PackageModel())->loadAllForUser($user);
    
    if (count($packages) === 0) {
      $content = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-warning', 'role' => 'alert'),
          'This user hasn\'t uploaded any packages.');
    } else {
      $items = array();
      
      foreach ($packages as $package) {
        $items[] = phutil_tag(
          'div',
          array('class' => 'panel panel-default'),
          array(
            phutil_tag(
              'div',
              array('class' => 'panel-heading'),
              phutil_tag(
                'h3',
                array('class' => 'panel-title'),
                phutil_tag(
                  'a',
                  array('href' => '/'.$user->getUser().'/'.$package->getName()),
                  $package->getName()))),
            phutil_tag(
              'div',
              array('class' => 'panel-body'),
              $package->getFormattedDescription())
          ));
      }
      
      $content = $items;
    }
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $content,
    ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}