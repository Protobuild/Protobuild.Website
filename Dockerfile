FROM microsoft/dotnet:latest

RUN apt-get update
RUN apt-get install -y nginx nodejs npm git supervisor
RUN npm install -g bower

ADD src /srv/protobuild/src
WORKDIR /srv/protobuild/src/Protobuild.Website
RUN dotnet restore

RUN dotnet publish -c Release -o ../../pkg

ADD extra/nginx /srv/nginx
ADD extra/run.sh /srv/extra/run.sh
ADD extra/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -pv /var/log/nginx /var/log/supervisor

RUN chmod a+X /srv/extra/run.sh

ENV ASPNETCORE_ENVIRONMENT Production
ENV ASPNETCORE_URLS http://localhost:5000
WORKDIR /srv/protobuild/pkg
ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
