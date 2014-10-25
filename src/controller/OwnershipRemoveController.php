<?php

final class OwnershipRemoveController extends ProtobuildController {
  
  public function processRequest(array $data) {
    $user = $this->loadOwnerFromRequestAndRequireEdit($data);
    
    if (!$user->getIsOrganisation()) {    
      throw new ProtobuildException(CommonErrors::USER_IS_NOT_ORGANISATION);
    }
    
    $to_remove_name = idx($data, 'remove');
    
    if ($to_remove_name === null) {
      throw new ProtobuildException(CommonErrors::USER_NOT_FOUND);
    }
    
    $to_remove = id(new UserModel())
      ->loadByName($to_remove_name);
    
    if ($to_remove === null) {
      throw new ProtobuildException(CommonErrors::USER_NOT_FOUND);
    }
    
    if (!$user->canRemoveOwner($to_remove, $this->getUser())) {
      throw new ProtobuildException(CommonErrors::ACCESS_DENIED);
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
          throw new ProtobuildRedirectException($user->getURI());
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