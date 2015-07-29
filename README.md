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
          
          RewriteEngine On
          # Redirect all requests not pointing at an actual file to index.php
          RewriteCond %{REQUEST_FILENAME} !-f
          RewriteRule . index.php [L] 
    </Directory>        
        
      CustomLog /caminho_dos_logs/restbeer-access_log combined
      ErrorLog /caminho_dos_logs/restbeer-error_log
  </VirtualHost>

Criar o host na sua máquina:

	127.0.0.1 restbeer.local

Instalar o composer.phar: 

<https://getcomposer.org/doc/00-intro.md#downloading-the-composer-executable>

	$ curl -sS https://getcomposer.org/installer | php

##### Composer.json

Utilizamos composer para carregar os projetos que vamos usar

* [Respect/Rest](http://github.com/Respect/Rest)
* [Respect/Validation](http://github.com/Respect/Validation)
* [Respect/Relational](http://github.com/Respect/Relational)
* [Respect/Config](http://github.com/Respect/Config)

#### Install

* Rodar o php composer.phar install no diretório do projeto
* Criar o arquivo index.php e rotas OU baixar do github

#### Refs

* Ver palestra <http://www.slideshare.net/ivanrosolen/restbeer>
* Manual Respect/Rest em português <http://nandokstronet.github.io/respect-rest-docs-br/>
* Mais docs <http://respect.li/>
* Inpirado na palestra <https://github.com/eminetto/restbeer>
