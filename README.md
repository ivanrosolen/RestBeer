# RestBeer


#### Preparar Ambiente

Criar um vhost (exemplo apache):

  <VirtualHost *:80>
  
      ServerName "restbeer.local"
      DocumentRoot "/caminho_do_projeto/restBeer/"
    
      <Directory "/caminho_do_projeto/restBeer">
          Options -Indexes FollowSymLinks
          AllowOverride All
          Order Allow,Deny
          Allow from all
    </Directory>        
        
        CustomLog /caminho_dos_logs/restbeer-access_log combined
        ErrorLog /caminho_dos_logs/restbeer-error_log
  </VirtualHost>

Criar o host na sua máquina:

  127.0.0.1 restbeer.local

Instalar o composer.phar: 

<https://getcomposer.org/doc/00-intro.md#downloading-the-composer-executable>

  $ curl -sS https://getcomposer.org/installer | php


Exemplo de .htaccess

<https://github.com/Respect/Rest/tree/develop/public>

  RewriteEngine On

  # Redirect all requests not pointing at an actual file to index.php
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule . index.php [L] 


##### Composer.json

Utilizamos composer para carregar os projetos que vamos usar

* Respect/Rest
* Respect/Validation
* Respect/Relational
* Respect/Config

#### Install

* Rodar o php composer.phar install no diretório do projeto
* Criar o arquivo index.php e rotas OU baixar do github
* Ver palestra <http://slideshare.com/ivanrosolen/>
* Manual Respect/Rest em português <http://www.cssexperts.net/respect-rest-docs-br/>