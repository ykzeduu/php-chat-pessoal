FROM php:8.2-apache

# Copia os arquivos para o container
COPY . /var/www/html/

# Cria a pasta de uploads e dá as permissões corretas para o servidor web
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80