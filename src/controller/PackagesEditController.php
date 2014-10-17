<?php

final class PackagesEditController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $current_name = idx($data, 'name');
    $is_new = $current_name === null;
    
    $user = $this->getUser();
    
    $error_name = null;
    $error_git = null;
    $error_desc = null;
    
    $value_name = null;
    $value_git = null;
    $value_desc = null;
    
    $current = null;
    if (!$is_new) {
      $current = id(new PackageModel())
        ->loadByUserAndName($user, $current_name);
      
      if ($current === null) {
        $error_name = 'Package was not found';
      } else {
        $value_name = $current->getName();
        $value_git = $current->getGitURL();
        $value_desc = $current->getDescription();
      }
    }
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb('Manage', '/packages/manage');
    $breadcrumbs->addBreadcrumb($is_new ? 'New' : 'Edit '.$current_name);
    
    $success = idx($_GET, 'success', 'false') === 'true';
    
    if (isset($_POST['name'])) {  
      $value_name = $_POST['name'];
      $value_git = $_POST['git'];
      $value_desc = $_POST['desc'];
      
      $existing = id(new PackageModel())
        ->loadByUserAndName($user, $value_name);
      
      if ($existing === null || ($current !== null && $value_name === $current->getName())) {
        if (preg_match('/^[a-zA-Z0-9-\.]+$/', $value_name, $matches) === 1) {
          
          if ($current === null) {
            $package = id(new PackageModel())
              ->setName($value_name)
              ->setGitURL($value_git)
              ->setDescription($value_desc)
              ->setGoogleID($this->getSession()->getUserID())
              ->create();
              
            // TODO: Redirect to package's page instead of manage list
            header('Location: /packages/manage');
            die();
              
          } else {
            $changed_name = $current->getName() !== $value_name;
            
            $current
              ->setName($value_name)
              ->setGitURL($value_git)
              ->setDescription($value_desc);
            $current->update();
            
            if ($changed_name) {
              header('Location: /packages/edit/'.$value_name.'?success=true');
              die();
            }
          }
          
          $success = true;
          
        } else {
          $error_name = 
            'Package names can only contain letters, numbers, dashes and dots';
        } 
      } else {
        $error_name = 'You already have a package with the same name';
      }
    }
    
    $message = null;
    if ($success) {
      $message = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-success', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Success!'),
            '  Your package has been saved successfully.'));
    }
    
    $form = <<<EOF
<div class="panel panel-default">
  <div class="panel-body">
    <form role="form" method="POST">
      <div class="form-group %s">
        <label class="control-label" for="name">Package Name%s</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter package name (letters, numbers, dashes and dots only)" value="%s">
      </div>
      <div class="form-group">
        <label class="control-label" for="git">Git Source URL</label>
        <input type="text" class="form-control" id="git" name="git" placeholder="Full URL to the Git repository (optional)" value="%s">
      </div>
      <div class="form-group">
        <label class="control-label" for="desc">Description</label>
        <textarea class="form-control" id="desc" name="desc" rows="6">%s</textarea>
      </div>
      <p>
        You will be able to upload binaries for your package after creating it.
      </p>
      <button type="submit" class="btn btn-default">Save</button>
    </form>
  </div>
</div>
EOF
;
    $form = hsprintf(
      $form,
      $error_name !== null ? 'has-error' : '',
      $error_name !== null ? (' ('.$error_name.')') : '',
      $value_name,
      $value_git,
      $value_desc);
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $message,
      $form,
    ));
  }
  
}