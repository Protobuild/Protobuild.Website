<?php

final class OwnershipRemoveController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    $user = $this->loadOwnerFromRequestAndRequireEdit($data);
    
    if (!$user->getIsOrganisation()) {    
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $to_remove_name = idx($data, 'remove');
    
    if ($to_remove_name === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $to_remove = id(new UserModel())
      ->loadByName($to_remove_name);
    
    if ($to_remove === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    if (!$user->canRemoveOwner($to_remove, $this->getUser())) {
      // TODO Show 404 user unable to be removed
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = $this->createBreadcrumbs($user);
    $breadcrumbs->addBreadcrumb('Remove Owner');
    $breadcrumbs->addBreadcrumb($to_remove->getCanonicalName());
    
    $success = false;
    if (isset($_POST['__submit__'])) {
      $ownerships = id(new OwnershipModel())
        ->loadOwnersForOrganisationGoogleID($user->getGoogleID());
      
      foreach ($ownerships as $ownership) {
        if ($ownership->getOwnerGoogleID() === $to_remove->getGoogleID()) {
          $ownership->delete();
          header('Location: '.$user->getURI());
          die();
        }
      }
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(phutil_tag(
          'p',
          array(),
          'Really remove '.$to_remove->getCanonicalName().
          ' as an owner of this organisation?'))
        ->appendChild(id(new FormSubmit())
          ->setText('Remove Owner')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}