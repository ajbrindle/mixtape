FROM php:5.6-apache
RUN apt-get update && apt-get upgrade -y && apt-get install -y nano
RUN a2enmod headers \
    && sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "*"\n\1\2/g' /etc/apache2/sites-available/*.conf
COPY . /var/www/html
EXPOSE 80/tcp
EXPOSE 443/tcp
