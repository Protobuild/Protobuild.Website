<?php

final class BranchModel {
  
  private $key;
  private $googleID;
  private $packageName;
  private $branchName;
  private $versionName;
  
  const KIND = 'branch';
  
  public function __construct() {
    $this->datastore = id(new GoogleService())->getGoogleCloudDatastore();
  }
  
  public function getKey() {
    return $this->key;
  }
  
  public function setKey($key) {
    $this->key = $key;
    return $this;
  }
  
  public function getGoogleID() {
    return $this->googleID;
  }
  
  public function setGoogleID($googleID) {
    $this->googleID = $googleID;
    return $this;
  }
  
  public function getPackageName() {
    return $this->packageName;
  }
  
  public function setPackageName($name) {
    $this->packageName = $name;
    return $this;
  }
  
  public function getVersionName() {
    return $this->versionName;
  }
  
  public function setVersionName($name) {
    $this->versionName = $name;
    return $this;
  }
  
  public function getBranchName() {
    return $this->branchName;
  }
  
  public function setBranchName($name) {
    $this->branchName = $name;
    return $this;
  }
  
  public function getIsAutoBranch() {
    return $this->_isAutoBranch;
  }
  
  public function setIsAutoBranch($autobranch) {
    $this->_isAutoBranch = $autobranch;
    return $this;
  }
  
  public function getJSONArray() {
    return array(
      'ownerID' => $this->getGoogleID(),
      'packageName' => $this->getPackageName(),
      'branchName' => $this->getBranchName(),
      'versionName' => $this->getVersionName(),
    );
  }
  
  private function mapProperties() {
    $mappings = array(
      'googleID' => $this->getGoogleID(),
      'packageName' => $this->getPackageName(),
      'branchName' => $this->getBranchName(),
      'versionName' => $this->getVersionName(),
    );
    
    $indexes = array(
      'googleID' => true,
      'branchName' => true,
      'packageName' => true,
    );
    
    $results = array();
    
    foreach ($mappings as $name => $value) {
      $prop = new Google_Service_Datastore_Property();
      $prop->setStringValue($value);
      $prop->setIndexed(array_key_exists($name, $indexes));
      
      $results[$name] = $prop;
    }
    
    return $results;
  }
  
  private static function unmapProperties($entity, $model) {
    $props = $entity->getProperties();
    
    $props_googleID = idx($props, 'googleID');
    $props_packageName = idx($props, 'packageName');
    $props_branchName = idx($props, 'branchName');
    $props_versionName = idx($props, 'versionName');
    
    $value_googleID = null;
    $value_packageName = null;
    $value_branchName = null;
    $value_versionName = null;
    
    if ($props_googleID !== null) {
      $value_googleID = $props_googleID->getStringValue();
    }
    
    if ($props_packageName !== null) {
      $value_packageName = $props_packageName->getStringValue();
    }
    
    if ($props_branchName !== null) {
      $value_branchName = $props_branchName->getStringValue();
    }
    
    if ($props_versionName !== null) {
      $value_versionName = $props_versionName->getStringValue();
    }
    
    $model
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setGoogleID($value_googleID)
      ->setPackageName($value_packageName)
      ->setBranchName($value_branchName)
      ->setVersionName($value_versionName);
      
    return $model;
  }
  
  public function create() {
    if ($this->getIsAutoBranch()) {
      throw new Exception('Automatic branches can not be created.');
    }

    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId(null);

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties($this->mapProperties());

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setInsertAutoId(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->commit($dataset_id, $req);
    
    $mutation = $result->getMutationResult();
    $insertedIds = $mutation->getInsertAutoIdKeys();
    
    $this->setKey(head(head($insertedIds)->getPath())->getId());
    return $this;
  }
  
  public function update() {
    if ($this->getIsAutoBranch()) {
      throw new Exception('Automatic branches can not be updated.');
    }

    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($this->getKey());

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));
    
    $entity = new Google_Service_Datastore_Entity();
    $entity->setKey($key);
    $entity->setProperties($this->mapProperties());

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setUpdate(array($entity));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
    return $this;
  }
  
  public function delete() {
    if ($this->getIsAutoBranch()) {
      throw new Exception('Automatic branches can not be deleted.');
    }

    $path = new Google_Service_Datastore_KeyPathElement();
    $path->setKind(self::KIND);
    $path->setId($this->getKey());

    $key = new Google_Service_Datastore_Key();
    $key->setPath(array($path));

    $mutation = new Google_Service_Datastore_Mutation();
    $mutation->setDelete(array($key));
    $req = new Google_Service_Datastore_CommitRequest();
    $req->setMode('NON_TRANSACTIONAL');
    $req->setMutation($mutation);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $dataset->commit($dataset_id, $req);
  }
  
  public function loadAllForPackage(
    UserModel $user,
    PackageModel $package) {
    
    if ($package->getGitURL() != null) {
      // Load the branches from Git instead of Google Cloud Datastore.
      list($out, $err) = execx('git ls-remote --heads %s', $package->getGitURL());
      $entries = phutil_split_lines($out);
      $results = array();
      for ($i = 0; $i < count($entries); $i++) {
        $s = explode("\t", $entries[$i]);
        if (count($s) >= 2) {
          $branch_name = trim($s[1]);
          if (substr($branch_name, 0, strlen("refs/heads/")) === "refs/heads/") {
            $branch_name = substr($branch_name, strlen("refs/heads/"));
          } else {
            continue;
          }
          $results[] = id(new BranchModel())
            ->setPackageName($package->getName())
            ->setBranchName($branch_name)
            ->setVersionName(trim($s[0]))
            ->setIsAutoBranch(true);
        }
      }
      return $results;
    }


    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($package->getName());
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM branch WHERE googleID = @id AND packageName = @name');
    $gql_query->setNameArgs(array($id_arg, $name_arg));
    
    $query = new Google_Service_Datastore_RunQueryRequest();
    $query->setGqlQuery($gql_query);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->runQuery($dataset_id, $query);
    
    $batch = $result->getBatch();
    $entities = $batch->getEntityResults();
    
    $results = array();
    
    foreach ($entities as $entity_result) {
      $entity = $entity_result->getEntity();
      
      $results[] = self::unmapProperties(
        $entity,
        id(new BranchModel()));
    }
    
    return $results;
  }
}