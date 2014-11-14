<?php

/*
 * In order to perform full text searching, we need to have a Google App Engine
 * application that can access and index datastore entities using the Search
 * API (which is not available over HTTP).
 * 
 * The endpoint for the search API is configured as the "search.endpoint"
 * configuration value.  The configuration value should not have a trailing
 * slash.
 */
final class SearchConnector extends Phobject {
  
  public function reindexPackage(PackageModel $package) {
    $encoded_id = urlencode($package->getKey());
    $uri = ProtobuildEnv::get('search.endpoint').'/reindex/'.$encoded_id;
    
    list($body, $headers) = id(new HTTPFuture($uri))->resolvex();
    
    $json = phutil_json_decode($body);
    if ($json['error']) {
      throw new Exception($json['message']);
    }
  }
  
  public function removePackage($package_key) {
    $encoded_id = urlencode($package_key);
    $uri = ProtobuildEnv::get('search.endpoint').'/remove/'.$encoded_id;
    
    list($body, $headers) = id(new HTTPFuture($uri))->resolvex();
    
    $json = phutil_json_decode($body);
    if ($json['error']) {
      throw new Exception($json['message']);
    }
  }
  
  public function performQuery($query) {
    $encoded_query = urlencode($query);
    $uri = ProtobuildEnv::get('search.endpoint').'/search/'.$encoded_query;
    
    list($body, $headers) = id(new HTTPFuture($uri))->resolvex();
    
    return phutil_json_decode($body);
  }
  
}