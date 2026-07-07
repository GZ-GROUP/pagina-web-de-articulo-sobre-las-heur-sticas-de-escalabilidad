FROM php:8.2-apache

# Copiar todo el proyecto al directorio web de Apache
COPY . /var/www/html/

# Establecer permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Habilitar mod_rewrite (por si lo necesitas ahora o en el futuro)
RUN a2enmod rewrite

EXPOSE 80