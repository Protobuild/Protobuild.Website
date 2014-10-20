<?php

final class OrganisationNewController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    $error_name = null;
    $value_name = null;
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb('New Organisation');
    
    $success = false;
    if (isset($_POST['__submit__'])) {  
      $value_name = $_POST['name'];
      
      $existing = id(new UserModel())->loadByName($value_name);
      
      if ($existing === null) {
        if (preg_match('/^[A-Za-z0-9-]+$/', $value_name, $matches) === 1) {
          $org_id = 'org:'.strtolower($value_name).':'.time();
          
          $organisation = id(new UserModel())
            ->setGoogleID($org_id)
            ->setName($value_name)
            ->setIsOrganisation(true)
            ->create();
          
          id(new OwnershipModel())
            ->setOwnerGoogleID($this->getUser()->getGoogleID())
            ->setOrganisationGoogleID($org_id)
            ->create();
          
          // On first set, redirect to manage packages.
          header('Location: '.$organisation->getURI());
          die();
        } else {
          $error_name = 
            'Organisation names can only contain '.
            'letters, numbers and dashes';
        }
      } else {
        $error_name = 'Another user already has that username';
      }
    }
    
    $form = id(new Panel())
      ->appendChild(id(new Form())
        ->appendChild(id(new FormTextInput())
          ->setName('name')
          ->setLabel('Organisation Name')
          ->setValue($value_name)
          ->setError($error_name)
          ->setPlaceholder('Enter organisation name (letters, numbers and dashes only)'))
        ->appendChild(phutil_tag(
          'p',
          array(),
          'You will automatically be added as an owner of the organisation.'))
        ->appendChild(id(new FormSubmit())
          ->setText('Create Organisation')));
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}