<?php

function init_script() {
  error_reporting(E_ALL | E_STRICT);
  ini_set('display_errors', 1);

  $include_path = ini_get('include_path');
  ini_set(
    'include_path',
    $include_path.PATH_SEPARATOR.dirname(__FILE__).'/../../');
  @include_once 'libphutil/scripts/__init_script__.php';
  if (!@constant('__LIBPHUTIL__')) {
    echo "ERROR: Unable to load libphutil. Update your PHP 'include_path' to ".
         "include the parent directory of libphutil/.\n";
    exit(1);
  }
}

init_script();
