<?php

final class OwnershipAddController extends ProtobuildController {
  
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
    
    $breadcrumbs = $this->createBreadcrumbs($user);
    $breadcrumbs->addBreadcrumb('Add Owner');
    
    $error_name = null;
    $value_name = null;
    
    $success = false;
    if (isset($_POST['__submit__'])) {  
      $value_name = $_POST['username'];
      
      $to_add = id(new UserModel())->loadByName($value_name);
      
      if ($to_add === null) {
        $error_name = 'No such user exists';
      } else if ($to_add->getIsOrganisation()) {
        $error_name = 'Can\'t add other organisations as owners';
      } else {
        $owners = id(new OwnershipModel())
          ->loadOwnersForOrganisationGoogleID($user->getGoogleID());
        $owners = mpull($owners, 'getOwnerGoogleID', 'getOwnerGoogleID');
      
        if (idx($owners, $to_add->getGoogleID()) !== null) {
          $error_name = 'That user is already an owner';
        } else {
          id(new OwnershipModel())
            ->setOwnerGoogleID($to_add->getGoogleID())
            ->setOrganisationGoogleID($user->getGoogleID())
            ->create();
          
          header('Location: '.$user->getURI());
          die();
        }
      }
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(id(new FormTextInput())
          ->setName('username')
          ->setLabel('User to Add')
          ->setValue($value_name)
          ->setError($error_name)
          ->setPlaceholder('Enter username (letters, numbers and dashes only)')
          ->setCaption('This user will have full access to the organisation\'s account.'))
        ->appendChild(id(new FormSubmit())
          ->setText('Add Owner')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}