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
      throw new Protobuild404Exception(CommonErrors::USER_NOT_FOUND);
    }
    
    $user = id(new UserModel())
      ->loadByName($username);
    
    if ($user === null) {
      throw new Protobuild404Exception(CommonErrors::USER_NOT_FOUND);
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
    
    $owners_list = null;
    if ($user->getIsOrganisation()) {
      $owners_list = array();
      
      $owner_ids = id(new OwnershipModel())
        ->loadOwnersForOrganisationGoogleID($user->getGoogleID());
      $owner_ids = mpull($owner_ids, 'getOwnerGoogleID');
      
      $owners = id(new UserModel())
        ->loadAllForIDs($owner_ids);
      
      foreach ($owners as $owner) {
        $remove_option = null;
        
        if ($user->canRemoveOwner($owner, $this->getUser())) {
          $remove_option = array(
            ' (',
            phutil_tag(
              'a',
              array('href' => $user->getURI('owner/remove/'.$owner->getCanonicalName())),
              'Remove'),
            ')');
        }
        
        $owners_list[] = phutil_tag(
          'li',
          array(),
          array(
            phutil_tag(
              'a',
              array('href' => $owner->getURI()),
              $owner->getCanonicalName()),
            $remove_option));
      }
      
      $owners_list = array(
        phutil_tag('h3', array(), 'Organisation Owners'),
        phutil_tag('p', array(), 'This organisation is owned by:'),
        phutil_tag('ul', array(), $owners_list));
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
      
      if ($user->getIsOrganisation()) {
        $buttons[] = phutil_tag(
          'a',
          array(
            'type' => 'button',
            'class' => 'btn btn-default',
            'href' => $user->getURI('owner/add'),
          ),
          'Add Owner'
        );
      } else {
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
    }
    
    if ($this->getUser() !== null && 
      $user->getUniqueName() === $this->getUser()->getUniqueName()) {
    }
    
    if (count($buttons) > 0) {
      $buttons = array(
        phutil_tag('div', array('class' => 'btn-group'), array(
          $buttons)),
        phutil_tag('br', array(), null),
        phutil_tag('br', array(), null));
    }
        
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $content,
      $owners_list,
      $buttons,
    ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}