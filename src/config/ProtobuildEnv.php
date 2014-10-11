<?php

final class ProtobuildEnv {
  
  private static $config;
  
  public static function get($key) {
    if (self::$config == null) {
      $path = phutil_get_library_root('protobuild').'/../conf/local.json';
      if (!Filesystem::pathExists($path)) {
        echo 'config file not found at '.$path;
        die();
      } else {
        self::$config = phutil_json_decode(file_get_contents($path));
      }
    }
    
    return idx(self::$config, $key);
  }
  
}