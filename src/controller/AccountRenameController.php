<?php

final class AccountRenameController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  protected function requiresAccountName() {
    return false;
  }
  
  public function processRequest(array $data) {
    $owner_name = idx($data, 'owner');
    
    if ($owner_name === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $user = id(new UserModel())
      ->loadByName($owner_name);
    
    if ($user === null) {
      if ($owner_name === $this->getSession()->getUserID()) {
        // It is the current user attempting to set their name
        // for the first time.
      } else {
        // TODO Show 404 user not found
        header('Location: /index');
        die();
      }
    }
    
    $error = null;
    $success = idx($_GET, 'success', 'false') === 'true';
    if (isset($_POST['username'])) {
      $existing = id(new UserModel())
        ->loadByName($_POST['username']);
      
      if ($existing === null || strtolower($_POST['username']) === $user->getUniqueName()) {
        if (preg_match('/^[A-Za-z0-9-]+$/', $_POST['username'], $matches) === 1) {
          
          if ($user === null) {
            $user = id(new UserModel())
              ->setName($_POST['username'])
              ->setGoogleID($this->getSession()->getUserID())
              ->create();
              
            // On first set, redirect to manage packages.
            header('Location: '.$user->getURI());
            die();
          } else {
            $did_rename = false;
            if ($_POST['username'] !== $user->getCanonicalName()) {
              $did_rename = true;
            }
            
            $user->setName($_POST['username']);
            $user->update();
            
            if ($did_rename) {
              header('Location: '.$user->getURI('rename?success=true'));
              die();
            }
          }
          
          $success = true;
          
        } else {
          $error = 
            'Usernames can only contain letters, numbers and dashes';
        } 
      } elseif (strtolower($_POST['username']) !== $user->getUniqueName()) {
        $error = 'Another user already has that username';
      }
    }
    
    if ($user === null) {
      $breadcrumbs = new Breadcrumbs();
      $breadcrumbs->addBreadcrumb('Account');
      $breadcrumbs->addBreadcrumb('Set Username');
    } else {
      $breadcrumbs = $this->createBreadcrumbs($user);
      $breadcrumbs->addBreadcrumb('Rename');
    }
    
    if ($user === null) {
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
        phutil_tag('p', array(), 'You can change the name of this account below.'),
        phutil_tag(
          'div', 
          array('class' => 'alert alert-warning', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Warning!'),
            '  All existing package URLs will no longer work!  No '.
            'redirects will be set up when you change the account name.')),
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
            '  The account name has been changed successfully.')),
      );
    }
    
    $type_upper = 'Account';
    $type_lower = 'account';
    if ($user !== null && $user->getIsOrganisation()) {
      $type_upper = 'Organisation';
      $type_lower = 'organisation';
    }
    
    $form = <<<EOF
<div class="panel panel-default">
  <div class="panel-body">
    <form role="form" method="POST">
      <div class="form-group %s">
        <label class="control-label" for="username">%s Name%s</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Enter %s name (letters, numbers and dashes only)" value="%s">
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
      $type_upper,
      $error !== null ? (' ('.$error.')') : '',
      $type_lower,
      $user !== null ? ($user->getCanonicalName()) : '');
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $desc,
      $form,
    ));
  }
  
}