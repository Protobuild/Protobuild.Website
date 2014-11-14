Protobuild Index
====================

This is a PHP application which implements the Protobuild website and package index.  It uses Google Cloud Datastore and Google Cloud Storage for storage of packages.

Installation
-------------

To install this application, find an empty directory, and run the following commands:

```
git clone https://github.com/phacility/libphutil
pushd libphutil
git checkout 160eeba602bf5329ec7fee0fc0834e924557d9b3
popd
git clone https://github.com/hach-que/Protobuild.Website protobuild
```

Configuration
--------------

You will need to create a project on [https://cloud.google.com/](Google Cloud Platform).  This application will be used for package storage, as well as hosting the [https://github.com/hach-que/Protobuild.Website.Search](full text searching application).

Once you have created a project, you need to turn on the Google Cloud Datastore API and Google Cloud Storage API.

Next under "Credentials", create OAuth tokens for a service account and for a web account.  For the service account, click "Generate new JSON key".  Create a new key for public API access as well.

Now you need to create a JSON file under `conf/local.json` (in the root of the repository), and in it, place JSON with the appropriate values:

```json
{
  "google.web.clientID": "<Client ID for Web Account>",
  "google.web.emailAddress": "<Email Address for Web Account>",
  "google.web.clientSecret": "<Client Secret for Web Account>",
  "google.service.clientID": "<Client ID for Service Account>",
  "google.service.emailAddress": "<Email Address for Service Account>",
  "google.service.privateKeyId": "<private_key_id from downloaded JSON file>",
  "google.service.privateKey": "<private_key from downloaded JSON file>",
  "google.developerKey": "<API key for Public API access>",
  "domain": "<Domain that your package index will be hosted on>",
  "search.endpoint": "<The full text search endpoint>"
}
```

For the offical Protobuild index, the full text search endpoint is `http://api.protobuild-index.appspot.com`.  You'll probably need to fork the [https://github.com/hach-que/Protobuild.Website.Search](full text searching application), change the application name, and redeploy it to your own project using the Google Developer console.  More information about pushing new App Engine applications can be found in Google's documentation.

