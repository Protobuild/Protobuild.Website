<?php

final class AccountNameController extends ProtobuildController {
  
  protected function requiresAccountName() {
    return false;
  }
  
  public function processRequest(array $data) {
    
    $current = $this->getUser();
    
    $error = null;
    $success = false;
    if (isset($_POST['username'])) {
      $existing = id(new GoogleToUserMappingModel())
        ->loadByName($_POST['username']);
      
      if ($existing === null) {
        if (preg_match('/[a-z0-9-]+/', $_POST['username'], $matches) === 1) {
          
          if ($current === null) {
            $mapping = id(new GoogleToUserMappingModel())
              ->setUser($_POST['username'])
              ->setGoogleID($this->getSession()->getUserID())
              ->create();
              
            // On first set, redirect to manage packages.
            header('Location: /manage');
            die();
              
          } else {
            $current->setUser($_POST['username']);
            $current->update();
          }
          
          $success = true;
          
        } else {
          $error = 
            'Usernames can only contain lowercase letters, numbers and dashes';
        } 
      } elseif ($_POST['username'] !== $current->getUser()) {
        $error = 'Another user already has that username';
      }
    }
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Account');
    $breadcrumbs->addBreadcrumb('Set Username');
    
    if ($current === null) {
      $desc = array(
        phutil_tag('p', array(), <<<EOF
To start managing packages, you need to set an account username.  This name is
used in package URLs.
EOF
        ),
        phutil_tag('p', array(), <<<EOF
You can change your account name later, but changing your username later will
change all URLs for your registered packages.  No redirects will be set up if
you change your account name later.
EOF
        ),
      );
    } else {
      $desc = array(
        phutil_tag('p', array(), 'You can change your account name below.'),
        phutil_tag(
          'div', 
          array('class' => 'alert alert-warning', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Warning!'),
            '  All of your existing package URLs will no longer work!  No '.
            'redirects will be set up when you change your account name.')),
      );
    }
    
    if ($success) {
      $desc = array(
        $desc,
        phutil_tag(
          'div', 
          array('class' => 'alert alert-success', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Success!'),
            '  Your account name has been set successfully.')),
      );
    }
    
    $form = <<<EOF
<div class="panel panel-default">
  <div class="panel-body">
    <form role="form" method="POST">
      <div class="form-group %s">
        <label class="control-label" for="username">Username%s</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username (lowercase letters, numbers and dashes only)" value="%s">
      </div>
      <button type="submit" class="btn btn-default">Save</button>
    </form>
  </div>
</div>
EOF
;
    $form = hsprintf(
      $form,
      $error !== null ? 'has-error' : '',
      $error !== null ? (' ('.$error.')') : '',
      $current !== null ? ($current->getUser()) : '');
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $desc,
      $form,
    ));
  }
  
}