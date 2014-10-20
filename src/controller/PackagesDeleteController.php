<?php

final class PackagesDeleteController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    list($user, $package) = $this->loadOwnerAndPackageFromRequestAndRequireEdit($data);
    
    $versions = id(new VersionModel())->loadAllForPackage($user, $package);
    $branches = id(new BranchModel())->loadAllForPackage($user, $package);
    
    if (count($versions) !== 0 || count($branches) !== 0) {
      // TODO show appropriate error
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user, $package);
    $breadcrumbs->addBreadcrumb('Delete');
    
    if (isset($_POST['__submit__'])) {
      $package->delete();
      
      header('Location: '.$user->getURI());
      die();
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(phutil_tag(
          'p',
          array(),
          phutil_tag(
            'strong', 
            array(),
            'WARNING: Deleting this package will prevent any projects that '.
            'are currently using it from resolving packages correctly!')
        ))
        ->appendChild(phutil_tag(
          'p',
          array(),
          'Deletion of packages is permanent and can only be done once the '.
          'package has no branches or versions present.'
        ))
        ->appendChild(phutil_tag(
          'p',
          array(),
          phutil_tag('strong', array(), 'Really delete this package?')
        ))
        ->appendChild(id(new FormSubmit())
          ->setText('Delete this package permanently!')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}