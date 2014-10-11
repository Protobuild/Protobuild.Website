<?php

$protobuild_root = dirname(dirname(__FILE__));
require_once $protobuild_root.'/support/ProtobuildStartup.php';

require_once $protobuild_root.'/externals/Google/Config.php';
require_once $protobuild_root.'/externals/Google/Client.php';
require_once $protobuild_root.'/externals/Google/Exception.php';
require_once $protobuild_root.'/externals/Google/Model.php';
require_once $protobuild_root.'/externals/Google/Utils.php';
require_once $protobuild_root.'/externals/Google/Service.php';
require_once $protobuild_root.'/externals/Google/Service/Resource.php';
require_once $protobuild_root.'/externals/Google/Service/Oauth2.php';
require_once $protobuild_root.'/externals/Google/Auth/Abstract.php';
require_once $protobuild_root.'/externals/Google/Auth/Exception.php';
require_once $protobuild_root.'/externals/Google/Auth/OAuth2.php';
require_once $protobuild_root.'/externals/Google/Http/Request.php';
require_once $protobuild_root.'/externals/Google/Http/CacheParser.php';
require_once $protobuild_root.'/externals/Google/Http/REST.php';
require_once $protobuild_root.'/externals/Google/IO/Abstract.php';
require_once $protobuild_root.'/externals/Google/IO/Curl.php';

ProtobuildStartup::didStartup();

$show_unexpected_traces = false;
try {
  ProtobuildStartup::loadCoreLibraries();

  $delegation = new RouteDelegation();
  list($controller_class, $request) = 
    $delegation->getControllerAndDataForUri($_REQUEST['__path__']);
  
  $controller = new $controller_class();
  $controller->beginRequest($request);
  echo $controller->processRequest($request);

} catch (Exception $ex) {
  ProtobuildStartup::didEncounterFatalException(
    'Core Exception',
    $ex,
    $show_unexpected_traces);
}
