#!/bin/bash

if [ -d /srv/nginx/ssl ]; then
  /usr/sbin/nginx -c /srv/nginx/nginx.ssl.conf
else
  /usr/sbin/nginx -c /srv/nginx/nginx.conf
fi