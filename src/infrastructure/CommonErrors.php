<?php

final class CommonErrors extends Phobject {
  
  const PAGE_NOT_FOUND = 'The requested page was not found.';
  
  const USER_NOT_FOUND = 'User not found.';
  const PACKAGE_NOT_FOUND = 'Package not found.';
  const BRANCH_NOT_FOUND = 'Branch not found.';
  const VERSION_NOT_FOUND = 'Version not found.';
  
  const ACCESS_DENIED = 'You don\'t have permission to perform that operation.';
  
  const USER_IS_NOT_ORGANISATION = 'The specified user is not an organisation.';
  
  const PACKAGE_HAS_NO_VERSIONS = 'Package has no available versions.';
  const PACKAGE_STILL_HAS_BRANCHES_OR_VERSIONS = 'Package still has branches or versions.';
  
  const MISSING_INFORMATION = 'The request is missing information.';
  
}