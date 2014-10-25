#!/usr/bin/env php
<?php

$root = dirname(dirname(__FILE__));
require_once $root.'/example/__init_script__.php';

/*
 * This is an example script for pushing packages to the Protobuild package
 * index.  Note that this logic only applies to the offical Protobuild Index at
 * http://protobuild.org/; other Protobuild package indexes are free to
 * implement their own upload mechanism and API (the package resolving API
 * being the only standard API required).
 */

$args = new PhutilArgumentParser($argv);
$args->setTagline('push new package versions');
$args->setSynopsis(<<<EOSYNOPSIS
**push_version.php** [__options__]
    Push new packages versions to the official Protobuild index.

EOSYNOPSIS
  );
$args->parseStandardArguments();
$args->parse(
  array(
    array(
      'name'  => 'key',
      'param' => 'key',
      'help'  => 'Your API key for authentication.',
    ),
    array(
      'name'  => 'user',
      'param' => 'user',
      'help'  => 'The name of the user or organisation that owns the package.',
    ),
    array(
      'name'  => 'package',
      'param' => 'package',
      'help'  => 'The name of the package.',
    ),
    array(
      'name'  => 'version',
      'param' => 'version',
      'help'  => 'The new version\'s Git hash.',
    ),
    array(
      'name'  => 'platform',
      'param' => 'platform',
      'help'  => 'The new version\'s platform.',
    ),
    array(
      'name'  => 'file',
      'param' => 'file',
      'help'  => 'The path to the file to upload.',
    ),
    array(
      'name'  => 'branch-update',
      'param' => 'branch',
      'help'  => '(Optional) Specify the branch to point to this new version.',
    ),
  ));

// ========== Get Arguments ===========
  
$api_key = $args->getArg('key');
if (!$api_key) {
  throw new PhutilArgumentUsageException(
    'Specify your API key with `--key`.');
}

$package_user = $args->getArg('user');
if (!$package_user) {
  throw new PhutilArgumentUsageException(
    'Specify the name of the user or '.
    'organisation that owns the package with `--user`.');
}

$package_name = $args->getArg('package');
if (!$package_name) {
  throw new PhutilArgumentUsageException(
    'Specify the package name with `--package`.');
}

$package_version = $args->getArg('version');
if (!$package_version) {
  throw new PhutilArgumentUsageException(
    'Specify the new version\'s Git hash with `--version`.');
}

$package_platform = $args->getArg('platform');
if (!$package_platform) {
  throw new PhutilArgumentUsageException(
    'Specify the new version\'s platform with `--platform`.');
}

$package_file = $args->getArg('file');
if (!$package_file) {
  throw new PhutilArgumentUsageException(
    'Specify the new version\'s file with `--file`.');
}

$package_branch = $args->getArg('branch-update');

$uri = 'http://protobuild.org/api/'.$package_user.'/'.$package_name;

// ========== Check File ===========

if (!file_exists($package_file)) {
  throw new PhutilArgumentUsageException(
    'The specified file does not exist.');
}

$file_contents = file_get_contents($package_file);

// ========== Create Version ===========

echo phutil_console_format("Creating new package version...\n");
    
list($status, $body, $headers) = id(new HTTPSFuture($uri.'/version/new'))
  ->setMethod('POST')
  ->setData(array(
    '__apikey__' => $api_key,
    'version' => $package_version,
    'platform' => $package_platform,
    ))
  ->resolve();

$json = phutil_json_decode($body);

if ($json['has_error']) {
  throw new Exception($json['error']);
}

$upload_target = $json['result']['uploadUrl'];
$finalize_target = $json['result']['finalizeUrl'];

// ========== Upload Version ===========

echo phutil_console_format("Uploading package file to Google Cloud Storage...\n");

list($body, $headers) = id(new HTTPSFuture($upload_target))
  ->setMethod('PUT')
  ->setData($file_contents)
  ->resolvex();

// ========== Finalize Version ===========

echo phutil_console_format("Finalizing package version...\n");

list($status, $body, $headers) = id(new HTTPSFuture($finalize_target))
  ->setMethod('POST')
  ->setData(array(
    '__apikey__' => $api_key,
    ))
  ->resolve();

$json = phutil_json_decode($body);

if ($json['has_error']) {
  throw new Exception($json['error']);
}

if ($package_branch) {
  echo phutil_console_format(
    "Updating branch %s to point to %s...\n",
    $package_branch,
    $package_version);
  
  list($status, $body, $headers) = id(new HTTPSFuture($uri.'/branch/edit/'.$package_branch))
    ->setMethod('POST')
    ->setData(array(
      '__apikey__' => $api_key,
      'name' => $package_branch,
      'git' => $package_version
      ))
    ->resolve();

  $json = phutil_json_decode($body);

  if ($json['has_error']) {
    throw new Exception($json['error']);
  }
}

echo phutil_console_format("Package version pushed successfully.\n");
