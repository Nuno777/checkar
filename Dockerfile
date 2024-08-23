# Use a imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala as extensões necessárias para a sua aplicação PHP, por exemplo, pdo e pdo_pgsql para PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Copia os arquivos da sua aplicação para o diretório padrão do Apache
COPY . /var/www/html/

# Exponha a porta 80 para acessar a aplicação
EXPOSE 80

# Comando para iniciar o servidor Apache
CMD ["apache2-foreground"]