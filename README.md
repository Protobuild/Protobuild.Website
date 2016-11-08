# Protobuild Index

This is the ASP.NET Core application which implements the [Protobuild website](https://protobuild.org) and package index.  It uses Google Cloud Datastore and Google Cloud Storage for storage of packages.

**Note:** This is a rewrite of the original PHP implementation, as Google made breaking changes to their Datastore APIs and it was no longer feasible to update the PHP application to suit.

## Usage

The public Protobuild index is available at [https://protobuild.org](https://protobuild.org).

To run your own private Protobuild index, run this as a Docker container:

```
docker run \
  --rm \
  -p 80:80 \
  -p 443:443 \
  -v /path/to/ssl:/srv/nginx/ssl \
  -v /path/to/gcp-creds:/srv/gcp \
  -e DOMAIN=https://yourdomain.com \
  -e GOOGLE_PROJECT_ID=your-gcp-project-id \
  -e GOOGLE_SERVICE_ACCOUNT_JSON_PATH=/srv/gcp/service.json \
  -e GOOGLE_OAUTH_CLIENT_JSON_PATH=/srv/gcp/oauth.json
```

### Domain Configuration

You must supply a value for `DOMAIN` so that the container knows the public URL that this index runs at.  For example, for the public Protobuild index, the value of
this environment variable is `https://protobuild.org`.  The value should not have a trailing slash.

### SSL Configuration

You can optionally enable SSL by mounting a volume to `/srv/nginx/ssl`.  If no volume is mounted, SSL will not be served from the container.

In this folder, you should have:

- `ssl.key`
- `ssl.cert`

The format of the key and certificate should be for Nginx.  In particular, you must include any intermediary certificates in `ssl.cert` so that the Protobuild command line
tool will be able to establish an SSL connection to the server.

### Data Storage Configuration

Because this container uses Google Cloud Storage and Google Cloud Datastore as it's data storage mechanisms, you need to provide the Project ID, and map the credential
files into the container.  On your host you should have a folder like this:

- `/path/to/gcp-creds`
  - `service.json`
  - `oauth.json`

`service.json` should be the JSON file you downloaded from the Google Cloud web console when creating a new service account key.  You can get a service JSON file
from [the credentials page](https://console.cloud.google.com/apis/credentials).

`oauth.json` should be a JSON file that looks like this:

```
{
  "clientID": "...",
  "clientSecret": "..."
}
```

where the client ID and secret are associated with an OAuth credential that you also created at [the Google Cloud web console](https://console.cloud.google.com/apis/credentials).
