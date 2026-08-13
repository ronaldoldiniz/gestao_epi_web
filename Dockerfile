FROM php:8.1-apache

# Habilita mod_rewrite do Apache (útil para URLs amigáveis futuras)
RUN a2enmod rewrite

# Copia todos os arquivos da aplicação para o diretório público do Apache
COPY . /var/www/html/

# Define permissões corretas para o servidor web no container
RUN chown -R www-data:www-data /var/www/html/

# Expõe a porta padrão do Apache
EXPOSE 80
