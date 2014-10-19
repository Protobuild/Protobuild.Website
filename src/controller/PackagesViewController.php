<?php

final class PackagesViewController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $username = idx($data, 'user');
    $name = idx($data, 'name');
    
    if ($username === null || $name === null) {
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
    
    $package = id(new PackageModel())->loadByUserAndName($user, $name);
    
    if ($package === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $can_edit = 
      $this->getUser() !== null && 
      $this->getUser()->getUser() === $user->getUser();
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb(
      $user->getUser(),
      '/'.$user->getUser());
    $breadcrumbs->addBreadcrumb($package->getName());
    
    $header = phutil_tag('h2', array(), $package->getName());
    
    $is_windows = strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false;
    
    $add_module = <<<EOF
<div class="panel panel-default">
  <div class="panel-body">
    <form role="form" method="POST">
      <p>Add this package to your project by running:</p>
      <div class="form-group" style="margin-bottom: 0px;">
        <input type="text" class="form-control" disabled="disabled" value="%s">
      </div>
    </form>
  </div>
</div>
EOF;
    
    $prefix = '';
    if (!$is_windows) {
      $prefix = 'mono ';
    }

    $add_module = hsprintf(
      $add_module,
      $prefix.'Protobuild.exe --add http://protobuild.org/'.$user->getUser().'/'.$package->getName());
    
    $desc = phutil_tag('p', array(), $package->getFormattedDescription());
    
    $git = null;
    if (strlen($package->getGitURL()) !== 0) {
      $git = array(
        phutil_tag('h3', array(), 'Source Code'),
        hsprintf(<<<EOF
<p>The source code for this package resides at:</p>
<p><a href="%s"><strong>%s</strong></a></p>
EOF
        , $package->getGitURL(), $package->getGitURL()));
    }
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    
    if (count($versions) === 0) {
      $versions_html = array(
        phutil_tag('h3', array(), 'Binary Versions'),
        hsprintf(<<<EOF
<p>
  No binary versions are present.  Adding this module to 
  your project will clone a copy of the source code.
</p>
EOF
        ));
    } else {
      $versions_grouped = mgroup($versions, 'getVersionName');
      $versions_items = array();
      
      foreach ($versions_grouped as $version_name => $version_platforms) {
        
        $platforms = array();
        foreach ($version_platforms as $platform_entry) {
          
          $context = null;
          $badge = null;
          $target = '#';
          
          if ($can_edit) {
            if (!$platform_entry->getHasFile()) {
              $context = ' list-group-item-danger';
              $badge = phutil_tag(
                'span',
                array('class' => 'badge'),
                'Binary Missing');
              $target = '/packages/version/upload/'.$platform_entry->getKey();
            }
          }
          
          $platforms[] = phutil_tag(
            'a',
            array(
              'class' => 'list-group-item'.$context,
              'href' => $target,
            ),
            array(
              $badge,
              $platform_entry->getPlatformName()));
        }
        
        $versions_items[] = id(new Panel())
          ->setHeading($version_name)
          ->setNoBody(true)
          ->appendChild($platforms);
      }
      
      $versions_html = array(
        phutil_tag('h3', array(), 'Binary Versions'),
        $versions_items);
    }
    
    $edit_package = phutil_tag(
      'a',
      array(
        'type' => 'button',
        'class' => 'btn btn-default',
        'href' => '/packages/edit/'.$package->getName(),
      ),
      'Edit Package'
    );
    
    $upload_version = phutil_tag(
      'a',
      array(
        'type' => 'button',
        'class' => 'btn btn-primary',
        'href' => '/packages/version/new/'.$package->getName(),
      ),
      'Create and Upload New Version'
    );
    
    if ($can_edit) {
      $buttons = phutil_tag('p', array(), array(
        $upload_version,
        ' ',
        $edit_package));
    } else {
      $buttons = null;
    }
    
    $message = null;
    if (idx($_GET, 'uploaded', 'false') === 'true') {
      $message = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-success', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Success!'),
            '  Your package version has been uploaded successfully.'));
    }
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $add_module,
      $message,
      $header,
      $desc,
      $git,
      $versions_html,
      $buttons,
    ));
  }
  
  protected function getNavigationName() {
    return 'index';
  }
  
}