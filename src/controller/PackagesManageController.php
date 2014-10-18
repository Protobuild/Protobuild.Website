<?php

final class PackagesManageController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb('Manage');
    
    $packages = id(new PackageModel())->loadAllForUser($this->getUser());
    
    if (count($packages) === 0) {
      $content = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-warning', 'role' => 'alert'),
          'You have no packages listed.');
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
                  array('href' => '/'.$this->getUser()->getUser().'/'.$package->getName()),
                  $package->getName()))),
            phutil_tag(
              'div',
              array('class' => 'panel-body'),
              $package->getFormattedDescription())
          ));
      }
      
      $content = $items;
    }
    
    $new_package = phutil_tag(
      'a',
      array(
        'type' => 'button',
        'class' => 'btn btn-primary',
        'href' => '/packages/new'
      ),
      'New Package'
    );
    
    $new_package = phutil_tag('p', array(), $new_package);
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $content,
      $new_package,
    ));
  }
  
}