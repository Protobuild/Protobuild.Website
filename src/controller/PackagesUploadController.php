<?php

final class PackagesUploadController extends ProtobuildController {
  
  protected function showInDevelopmentWarning() {
    return true;
  }
  
  public function processRequest(array $data) {
    
    $current_name = idx($data, 'name');
    
    $user = $this->getUser();
    
    $package = id(new PackageModel())
      ->loadByUserAndName($user, $current_name);
    
    if ($package === null) {
      // TODO Show 404 user not found
      header('Location: /index');
      die();
    }
    
    $breadcrumbs = new Breadcrumbs();
    $breadcrumbs->addBreadcrumb('Package Index', '/index');
    $breadcrumbs->addBreadcrumb('Manage', '/packages/manage');
    $breadcrumbs->addBreadcrumb($current_name, '/'.$user->getUser().'/'.$current_name);
    $breadcrumbs->addBreadcrumb('Upload New Version');
    
    $storage = id(new GoogleService())->getGoogleCloudStorage();
    
    if (isset($_POST['version']) && isset($_FILES['uploadFile']['tmp_name'])) {
      $version = id(new VersionModel())
        ->setGoogleID($this->getSession()->getUserID())
        ->setPackageName($package->getName())
        ->setPlatformName($_POST['platform'])
        ->setVersionName($_POST['version'])
        ->create();
      
      $contents = file_get_contents($_FILES['uploadFile']['tmp_name']);
      
      $filename = $version->getKey().'.tar.gz';
      
      $data = array(
        'name' => $filename,
        'data' => $contents,
        'uploadType' => 'media',
      );
      
      $object = new Google_Service_Storage_StorageObject();
      $object->setName($filename);
      
      $storage->objects->insert('protobuild-packages', $object, $data);
      
      $acl = new Google_Service_Storage_ObjectAccessControl();
      $acl->setEntity('allUsers');
      $acl->setRole('READER');

      $storage->objectAccessControls->insert('protobuild-packages', $filename, $acl);
      
      header('Location: /'.$this->getUser()->getUser().'/'.$package->getName().'/?uploaded=true');
      die();
    }
    
    $form = <<<EOF
<div class="panel panel-default">
  <div class="panel-body">
    <form role="form" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="control-label" for="version">Version Name</label>
        <input type="text" class="form-control" id="version" name="version" placeholder="Enter version name (letters, numbers and dashes only)" value="">
      </div>
      <div class="form-group">
        <label class="control-label" for="version">Platform Name</label>
        <select name="platform" class="form-control">
          <option value="Android">Android</option>
          <option value="iOS">iOS</option>
          <option value="Linux">Linux</option>
          <option value="MacOS">MacOS</option>
          <option value="Ouya">Ouya</option>
          <option value="PCL">PCL</option>
          <option value="PlayStation4">PlayStation4</option>
          <option value="PSMobile">PSMobile</option>
          <option value="Windows" selected="selected">Windows</option>
          <option value="Windows8">Windows8</option>
          <option value="WindowsPhone">WindowsPhone</option>
          <option value="WindowsPhone81">WindowsPhone81</option>
          <option value="Web">Web</option>
        </select>
      </div>
      <div class="form-group">
        <label class="control-label" for="uploadFile">Package File (*.tar.gz)</label>
        <input type="file" name="uploadFile" accept="application/x-gtar">
        <p class="help-block">This file should contain the appropriate Protobuild project structure.</p>
      </div>
      <button type="submit" class="btn btn-default">Upload</button>
    </form>
  </div>
</div>
EOF
;
    $form = hsprintf($form);
    
    return $this->buildApplicationPage(array(
      $breadcrumbs,
      $form,
    ));
  }
  
}