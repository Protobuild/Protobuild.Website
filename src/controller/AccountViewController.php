<?php

final class AccountViewController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $username = idx($data, 'owner');
    
    if ($username === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $user = id(new UserModel())
      ->loadByName($username);
    
    if ($user === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user);
    
    $packages = id(new PackageModel())->loadAllForUser($user);
    
    if (count($packages) === 0) {
      $content = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-warning', 'role' => 'alert'),
          'This '.$user->getTerm().' hasn\'t uploaded any packages.');
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
                  array('href' => '/'.$user->getCanonicalName().'/'.$package->getName()),
                  $package->getName()))),
            phutil_tag(
              'div',
              array('class' => 'panel-body'),
              $package->getFormattedDescription())
          ));
      }
      
      $content = $items;
    }
    
    $buttons = array();
    
    if ($this->canEdit($user)) {
      $buttons[] = phutil_tag(
        'a',
        array(
          'type' => 'button',
          'class' => 'btn btn-primary',
          'href' => $user->getURI('new'),
        ),
        'New Package'
      );
      
      $buttons[] = phutil_tag(
        'a',
        array(
          'type' => 'button',
          'class' => 'btn btn-default',
          'href' => $user->getURI('rename'),
        ),
        'Rename Account'
      );
    }
    
    if ($this->getUser() !== null && 
      $user->getUniqueName() === $this->getUser()->getUniqueName()) {
      
      // Only show New Organisation when we're look at our own account.
      $buttons[] = phutil_tag(
        'a',
        array(
          'type' => 'button',
          'class' => 'btn btn-default',
          'href' => '/organisation/new'
        ),
        'New Organisation'
      );
    }
    
    $buttons = array(
      phutil_tag('div', array('class' => 'btn-group'), array(
        $buttons)),
      phutil_tag('br', array(), null),
      phutil_tag('br', array(), null));
        
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $content,
      $buttons,
    ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}