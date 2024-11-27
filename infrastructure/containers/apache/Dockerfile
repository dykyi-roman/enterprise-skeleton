# Arguments defined in docker-compose.yml
ARG APACHE_VERSION
FROM httpd:${APACHE_VERSION}-alpine

# Install required packages
RUN apk update && apk add --no-cache \
    apache2-proxy \
    apache2-ssl \
    curl

# Enable required modules
RUN sed -i \
    -e 's/^#\(LoadModule .*mod_proxy.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_proxy_fcgi.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_rewrite.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_ssl.so\)/\1/' \
    /usr/local/apache2/conf/httpd.conf

# Create SSL directory and set permissions
RUN mkdir -p /usr/local/apache2/ssl && \
    chown -R www-data:www-data /usr/local/apache2/ssl

# Copy configuration files
COPY apache.conf /usr/local/apache2/conf/extra/vhost.conf
COPY apache2.conf /usr/local/apache2/conf/httpd.conf
COPY ssl/server.crt /usr/local/apache2/ssl/
COPY ssl/server.key /usr/local/apache2/ssl/

# Set proper permissions
RUN chown -R www-data:www-data /usr/local/apache2/ssl

EXPOSE 80 443
