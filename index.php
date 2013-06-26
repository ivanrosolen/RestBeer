<?php

/**
 * RestBeer _o/
 *         _.._..,_,_
 *        (          )
 *         ]~,"-.-~~[
 *       .=])' (;  ([
 *       | ]:: '    [
 *       '=]): .)  ([
 *         |:: '    |
 *          ~~----~~
*/

require 'vendor/autoload.php';
 
use Respect\Rest\Router;
use Respect\Config;
use Respect\Relational\Mapper;
use Respect\Data\Collections\Collection;

/** 
 * Ler arquivo de configuração
 */
$conf = new Container('config.ini');


/** 
 * Criar instância PDO com o SQLite usando as configs
 */
$mapper = new Mapper(new PDO($conf->dsn));


// Criar instância do router
$r = new Router();
 
/** 
 * Rota para qualquer tipo de request (any)
 */
$r->any('/', function () {
	return 'RestBeer!';
});
 
/** 
 * Rota com autenticação básica
 */

// do not use this!
function checkLogin($user, $pass) {
	return $user === 'admin' && $pass === 'admin';
}

$r->get('/admin', function () {
	return 'RestBeer Admin Protected!';
})->authBasic('Secret Area', function ($user, $pass) {
	return checkLogin($user, $pass);
});

// Rota para listar informações de uma cerveja
$app->get('/cervejas/*', function ($id) use ($mapper) {
    if ($id == null) {
        $cervejas = $mapper->cervejas->fetchAll();
        // como fazer isso no respect? ** olhar bot pro json e o retorno 200
        //return new Response (json_encode($cervejas), 200); 
        return json_encode($cervejas);
    }

    $cerveja = $mapper->cervejas[$id]->fetchAll();
    if ( !$cerveja ) {
    	// como fazer isso no respect? ** olhar bot pro json e o retorno 404
    	//return new Response (json_encode('Não encontrada'), 404); 
        return json_encode('Não encontrada'); 
    }

    return json_encode($cerveja); 
})


// fazer com o through??? ** definir pra geral que o tipo de resposta é json
/*$app->after(function (Request $request, Response $response) {
    $response->headers->set('Content-Type', 'text/json');
});*/