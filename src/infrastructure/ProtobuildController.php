<?php

abstract class ProtobuildController extends Phobject {
  
  private $session;
  private $user;
  
  public function beginRequest(array $data) {
    $this->session = new AuthSession();
    $this->session->start();
    
    if (!$this->allowPublicAccess() && !$this->session->isAuthenticated()) {
      $this->session->authenticate();
      return;
    }

    if ($this->session->isAuthenticated()) {
      $this->user = id(new UserModel())
        ->load($this->getSession()->getUserID());
      
      if ($this->requiresAccountName()) {
        if ($this->user === null || $this->user->getCanonicalName() === null) {
          header('Location: /'.$this->getSession()->getUserID().'/rename');
          die();
        }
      }
    }
  }
  
  protected function getSession() {
    return $this->session;
  }
  
  protected function getUser() {
    return $this->user;
  }
  
  protected function canEdit(UserModel $target) {
    if ($this->getUser() === null) {
      return false;
    }
    
    if ($target->getIsOrganisation()) {
      $owners = id(new OwnershipModel())
        ->loadOwnersForOrganisationGoogleID($target->getGoogleID());
      $owners = mpull($owners, 'getOwnerGoogleID', 'getOwnerGoogleID');
      
      return idx($owners, $this->getUser()->getGoogleID()) !== null;
    } else {
      return $this->getUser()->getUniqueName() === $target->getUniqueName();
    }
  }
  
  protected function enforceRequireEdit(
    UserModel $user) {
    
    if (!$this->canEdit($user)) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    return $user;
  }
  
  protected function loadOwnerFromRequest(array $data) {
    $owner_name = idx($data, 'owner');
    
    if ($owner_name === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $owner = id(new UserModel())
      ->loadByName($owner_name);
    
    if ($owner === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    return $owner;
  }
  
  protected function loadOwnerAndPackageFromRequest(array $data) {
    $owner = $this->loadOwnerFromRequest($data);
    
    $package_name = idx($data, 'package');
    
    if ($package_name === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $package = id(new PackageModel())
      ->loadByUserAndName($owner, $package_name);
    
    if ($package === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    return array($owner, $package);
  }
  
  protected function loadOwnerFromRequestAndRequireEdit(array $data) {
    $owner = $this->loadOwnerFromRequest($data);
    
    $this->enforceRequireEdit($owner);
    
    return $owner;
  }
  
  protected function loadOwnerAndPackageFromRequestAndRequireEdit(array $data) {
    list($owner, $package) = $this->loadOwnerAndPackageFromRequest($data);
    
    $this->enforceRequireEdit($owner);
    
    return array($owner, $package);
  }
  
  protected function createBreadcrumbs($user = null, $package = null) {
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    if ($user !== null) {
      $breadcrumbs->addBreadcrumb($user->getCanonicalName(), $user->getURI());
    }
    if ($user !== null && $package !== null) {
      $breadcrumbs->addBreadcrumb($package->getName(), $package->getURI($user));
    }
    return $breadcrumbs;
  }
  
  protected function allowPublicAccess() {
    return false;
  }
  
  protected function requiresAccountName() {
    return true;
  }
  
  abstract function processRequest(array $data);
  
  protected function getNavigationName() {
    return null;
  }
  
  protected function showInDevelopmentWarning() {
    return false;
  }
  
  protected function buildApplicationPage($content) {
    $navigation = array(
      'home' => array('uri' => '/', 'title' => 'Home'),
      'index' => array('uri' => '/index', 'title' => 'Package Index'),
      'docs' => array(
        'uri' => 'https://github.com/hach-que/Protobuild/wiki',
        'title' => 'Documentation'),
      'src' => array(
        'uri' => 'https://github.com/hach-que/Protobuild',
        'title' => 'Source Code'),
      'support' => array(
        'uri' => 'https://github.com/hach-que/Protobuild/issues',
        'title' => 'Support'),
    );
    
    $navigation_tags = array();
    foreach ($navigation as $name => $info) {
      if ($name === $this->getNavigationName()) {
        $active = array('class' => 'active');
      } elseif ($name !== 'home' && $name !== 'index') {
        $active = array('class' => 'hidden-xs');
      } else {
        $active = array();
      }
      
      $title = $info['title'];
      if ($name === 'index') {
        $title = array(
          phutil_tag('span', array('class' => 'hidden-xs'), 'Package Index'),
          phutil_tag('span', array('class' => 'hidden-sm hidden-md hidden-lg'), 'Index'));
      }
      
      $navigation_tags[] = phutil_tag(
        'li',
        $active,
        phutil_tag(
          'a',
          array('href' => $info['uri']),
          $title));
    }
    
    $auth = null;
    if ($this->session->isAuthenticated()) {
      $auth = array(
        'Logged in as '.$this->session->getRealName(),
        phutil_safe_html(' &bull; '),
        phutil_tag(
          'a',
          array('href' => $this->getUser()->getURI()),
          'My Account'),
        phutil_safe_html(' &bull; '),
        phutil_tag(
          'a',
          array('href' => '/logout'),
          'Logout'));
    } else {
      $auth = array(
        phutil_tag(
        'a',
        array('href' => '/login'),
        'Login'));
    }
    
    $in_dev = null;
    if ($this->showInDevelopmentWarning()) {
      $in_dev = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-warning', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'In Development!'),
            '  This functionality is still under heavy development '.
            'and is not expected to work yet.'));
    }
    
    return hsprintf(<<<EOF
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protobuild</title>

    <!-- Bootstrap -->
    <link href="/rsrc/css/bootstrap.min.css" rel="stylesheet">

    <!-- Jumbotron narrow styles -->
    <link href="/rsrc/css/jumbotron-narrow.css" rel="stylesheet">
  </head>
  <body>

    <div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">
          %s
        </ul>
        <h3 class="text-muted">Protobuild</h3>
      </div>

      %s
      
      %s
      
      <div class="footer">
        <p>%s</p>
      </div>
    </div> <!-- /container -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="/rsrc/js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/rsrc/js/bootstrap.min.js"></script>
    <script src="/rsrc/js/upload.js"></script>
  </body>
</html>
EOF
    ,
    $navigation_tags,
    $in_dev,
    $content,
    $auth);
  }
  
}
