<?php

final class PackageModel {
  
  private $key;
  private $googleID;
  private $name;
  private $gitURL;
  private $description;
  private $type;
  
  const KIND = 'package';
  
  const TYPE_LIBRARY = 'library';
  const TYPE_TEMPLATE = 'template';
  const TYPE_GLOBAL_TOOL = 'global-tool';
  
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
  
  public function getName() {
    return $this->name;
  }
  
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  public function getGitURL() {
    return $this->gitURL;
  }
  
  public function setGitURL($git_url) {
    $this->gitURL = $git_url;
    return $this;
  }
  
  public function getDescription() {
    return $this->description;
  }
  
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }
  
  public function getType() {
    return $this->type;
  }
  
  public function setType($type) {
    $this->type = $type;
    return $this;
  }
  
  public function getDefaultBranch() {
    if ($this->defaultBranch === null || strlen($this->defaultBranch) === 0) {
      return 'master';
    }
    
    return $this->defaultBranch;
  }
  
  public function setDefaultBranch($default_branch) {
    $this->defaultBranch = $default_branch;
    return $this;
  }
  
  public function getURI(UserModel $owner, $path = null) {
    if ($path === null) {
      return '/'.$owner->getCanonicalName().'/'.$this->getName();
    } else {
      return '/'.$owner->getCanonicalName().'/'.$this->getName().'/'.$path;
    }
  }
  
  public function getFormattedDescription() {
    return self::getStaticFormattedDescription($this->description);
  }
  
  public static function getStaticFormattedDescription($description) {
    $html = array();
    $lines = phutil_split_lines($description);
    foreach ($lines as $line) {
      $html[] = phutil_tag('br', array(), null);
      $html[] = $line;
    }
    array_shift($html);
    return $html;
  }
  
  public function getJSONArray(UserModel $owner) {
    $git_url = $this->getGitURL();
    if ($git_url == '') {
      $git_url = null;
    }
    
    return array(
      'ownerID' => $this->getGoogleID(),
      'name' => $this->getName(),
      'type' => $this->getType(),
      'moduleUrl' => ProtobuildEnv::get('domain').$this->getURI($owner),
      'apiUrl' => make_api_url(ProtobuildEnv::get('domain').$this->getURI($owner)),
      'gitUrl' => $git_url,
      'description' => $this->getDescription(),
      'defaultBranch' => $this->getDefaultBranch(),
    );
  }
  
  private function mapProperties() {
    $mappings = array(
      'name' => $this->getName(),
      'googleID' => $this->getGoogleID(),
      'gitURL' => $this->getGitURL(),
      'description' => $this->getDescription(),
      'type' => $this->getType(),
      'defaultBranch' => $this->getDefaultBranch(),
    );
    
    $indexes = array(
      'name' => true,
      'googleID' => true,
      'type' => true,
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
    
    $props_name = idx($props, 'name');
    $props_type = idx($props, 'type');
    $props_googleID = idx($props, 'googleID');
    $props_gitURL = idx($props, 'gitURL');
    $props_description = idx($props, 'description');
    $props_defaultBranch = idx($props, 'defaultBranch');
    
    $value_name = null;
    $value_type = null;
    $value_googleID = null;
    $value_gitURL = null;
    $value_description = null;
    $value_defaultBranch = null;
    
    if ($props_name !== null) {
      $value_name = $props_name->getStringValue();
    }
    
    if ($props_type !== null) {
      $value_type = $props_type->getStringValue();
    }
    
    if ($props_googleID !== null) {
      $value_googleID = $props_googleID->getStringValue();
    }
    
    if ($props_gitURL !== null) {
      $value_gitURL = $props_gitURL->getStringValue();
    }
    
    if ($props_description !== null) {
      $value_description = $props_description->getStringValue();
    }
    
    if ($props_defaultBranch !== null) {
      $value_defaultBranch = $props_defaultBranch->getStringValue();
    }
        
    $model
      ->setKey(head($entity->getKey()->getPath())->getId())
      ->setName($value_name)
      ->setType($value_type)
      ->setGoogleID($value_googleID)
      ->setGitURL($value_gitURL)
      ->setDescription($value_description)
      ->setDefaultBranch($value_defaultBranch);
    
    return $model;
  }
  
  public function create() {
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
    
    $dataset->commit($dataset_id, $req);
    return $this;
  }
  
  public function update() {
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
  
  public function loadAllForUser(UserModel $user) {
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString('SELECT * FROM package WHERE googleID = @id');
    $gql_query->setNameArgs(array($id_arg));
    
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
      $props = $entity->getProperties();
      
      $results[] = 
        self::unmapProperties($entity, new PackageModel());
    }
    
    return $results;
  }
  
  public function loadByUserAndName(UserModel $user, $name) {
    $name_value = new Google_Service_Datastore_Value();
    $name_value->setStringValue($name);
    
    $name_arg = new Google_Service_Datastore_GqlQueryArg();
    $name_arg->setName('name');
    $name_arg->setValue($name_value);
    
    $id_value = new Google_Service_Datastore_Value();
    $id_value->setStringValue($user->getGoogleID());
    
    $id_arg = new Google_Service_Datastore_GqlQueryArg();
    $id_arg->setName('id');
    $id_arg->setValue($id_value);
    
    $gql_query = new Google_Service_Datastore_GqlQuery();
    $gql_query->setQueryString(
      'SELECT * FROM package WHERE googleID = @id AND name = @name');
    $gql_query->setNameArgs(array($id_arg, $name_arg));
    
    $query = new Google_Service_Datastore_RunQueryRequest();
    $query->setGqlQuery($gql_query);
    
    $dataset = $this->datastore->datasets;
    $dataset_id = "protobuild-index";
    
    $result = $dataset->runQuery($dataset_id, $query);
    
    $batch = $result->getBatch();
    $entities = $batch->getEntityResults();
    
    if (count($entities) === 0) {
      return null;
    }
    
    $entity = head($entities);
    $entity = $entity->getEntity();
    
    self::unmapProperties($entity, $this);
    
    return $this;
  }
  
}