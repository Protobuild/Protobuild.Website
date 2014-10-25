<?php

final class PackagesViewController extends ProtobuildController {
  
  protected function allowPublicAccess() {
    return true;
  }
  
  public function processApi(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequest($data);
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    $branches = id(new BranchModel())->loadAllForPackage($user, $package);
    
    $versions = mpull($versions, 'getJSONArray');
    $branches = mpull($branches, 'getJSONArray');
    
    return array(
      'user' => $user->getJSONArray(),
      'package' => $package->getJSONArray($user),
      'versions' => $versions,
      'branches' => $branches,
    );
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequest($data);
    $can_edit = $this->canEdit($user);
    
    $breadcrumbs = $this->createBreadcrumbs($user);
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

    $allow_delete = false;
    $add_module = hsprintf(
      $add_module,
      $prefix.'Protobuild.exe --add http://protobuild.org'.$package->getURI($user));
    
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
    $branches = id(new BranchModel())->loadAllForPackage($user, $package);
    $branches = mpull($branches, null, 'getBranchName');
    
    if (count($versions) === 0 && strlen($package->getGitURL()) === 0) {
      $add_module = id(new Panel())
        ->setType('danger')
        ->setHeading('No source URL or binaries present')
        ->appendChild(
          'This package can not be added to any projects as there is no '.
          'Git source URL configured, and no binary packages have been '.
          'uploaded.');
    }
    
    if (count($versions) === 0 && count($branches) === 0) {
      $allow_delete = true;
      
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
      $branches_items = array();
      
      foreach ($branches as $branch) {
        $links = null;
        if ($can_edit) {
          $links = array(
            phutil_tag(
              'a', 
              array('href' => $package->getURI($user, 'branch/edit/'.$branch->getBranchName())),
              'Edit Branch'),
            ' - ',
            phutil_tag(
              'a', 
              array('href' => $package->getURI($user, 'branch/delete/'.$branch->getBranchName())),
              'Delete Branch'),
          );
        }
        
        $message = 'Branch pointing to '.$branch->getVersionName().'.';
        $message = phutil_tag('p', array(), $message);
        if (idx($versions_grouped, $branch->getVersionName()) === null) {
          $message = array(
            $message,
            phutil_tag(
              'p',
              array(),
              phutil_tag(
                'strong',
                array(),
                'WARNING: The commit this branch points to is missing!')));
        }
        
        $branches_items[] = id(new Panel())
          ->setHeading($branch->getBranchName(). ' (branch)')
          ->setType('success')
          ->appendChild(
            array(
              $message,
              $links
            ));
      }
      
      foreach ($versions_grouped as $version_name => $version_platforms) {
        
        $platforms = array();
        foreach ($version_platforms as $platform_entry) {
          
          $context = null;
          $badge = null;
          $target = null;
          
          if ($can_edit) {
            if (!$platform_entry->getHasFile()) {
              $context = ' list-group-item-danger';
              $badge = phutil_tag(
                'span',
                array('class' => 'badge'),
                'Binary Missing');
              $target = $package->getURI($user, 'version/upload/'.$platform_entry->getKey());
            } else {
              $badge = phutil_tag(
                'span',
                array('class' => 'badge'),
                'Delete');
              $target = $package->getURI($user, 'version/delete/'.$platform_entry->getKey());
            }
          }
          
          $target_set = array();
          if ($target !== null) {
            $target_set['href'] = $target;
          }
          
          $platforms[] = phutil_tag(
            'a',
            array(
              'class' => 'list-group-item'.$context,
            ) + $target_set,
            array(
              $badge,
              $platform_entry->getPlatformName()));
        }
        
        $versions_items[] = id(new Panel())
          ->setHeading($version_name. ' (commit)')
          ->setNoBody(true)
          ->appendChild($platforms);
      }
      
      $master_warning = null;
      if ($can_edit && idx($branches, 'master') === null) {
        $master_warning = id(new Panel())
          ->setHeading('No "master" branch')
          ->setType('danger')
          ->appendChild(
            'You have not configured a "master" branch for this package.  '.
            'Adding new packages to a project defaults to the "master" branch '.
            'of those packages; without a "master" branch, projects will '.
            'always clone a source version by default.');
      }
      
      $versions_html = array(
        phutil_tag('h3', array(), 'Binary Versions'),
        $master_warning,
        $branches_items,
        $versions_items);
    }
    
    $upload_version = phutil_tag(
      'a',
      array(
        'type' => 'button',
        'class' => 'btn btn-primary',
        'href' => $package->getURI($user, 'version/new'),
      ),
      'Create and Upload New Version'
    );
    
    $new_branch = phutil_tag(
      'a',
      array(
        'type' => 'button',
        'class' => 'btn btn-default',
        'href' => $package->getURI($user, 'branch/new'),
      ),
      'New Branch'
    );
    if (count($versions) === 0) {
      $new_branch = phutil_tag(
        'a',
        array(
          'type' => 'button',
          'class' => 'btn btn-default',
          'disabled' => 'disabled',
        ),
        'New Branch'
      );
    }
    
    $edit_package = phutil_tag(
      'a',
      array(
        'type' => 'button',
        'class' => 'btn btn-default',
        'href' => $package->getURI($user, 'edit'),
      ),
      'Edit Package'
    );
    
    $delete_package = null;
    if ($allow_delete) {
      $delete_package = phutil_tag(
        'a',
        array(
          'type' => 'button',
          'class' => 'btn btn-danger',
          'href' => $package->getURI($user, 'delete'),
        ),
        'Delete Package'
      );
    }
    
    if ($can_edit) {
      $buttons = array(
        phutil_tag('div', array('class' => 'btn-group'), array(
          $upload_version,
          $new_branch,
          $edit_package,
          $delete_package)),
        phutil_tag('br', array(), null),
        phutil_tag('br', array(), null));
    } else {
      $buttons = null;
    }
    
    $message = array();
    
    if (idx($_GET, 'uploaded', 'false') === 'true') {
      $message[] = 
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
    
    if (idx($_GET, 'branch', 'false') === 'true') {
      $message[] = 
        phutil_tag(
          'div', 
          array('class' => 'alert alert-success', 'role' => 'alert'),
          array(
            phutil_tag(
              'strong',
              array(),
              'Success!'),
            '  Your branch has been created successfully.'));
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