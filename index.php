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
use Respect\Config\Container;
use Respect\Validation\Validator as v;
use Respect\Relational\Mapper;
use Respect\Data\Collections\Collection;

/** 
 * Ler arquivo de configuração
 */
$config = new Container('config.ini');

/** 
 * Criar instância PDO com o SQLite usando as configs
 */
//$mapper = new Mapper(new PDO($config->dsn));


// Criar instância do router
$router = new Router();
 
/** 
 * Rota para qualquer tipo de request (any)
 */
$router->any('/', function () {
    return 'RestBeer!';
});
 
/** 
 * Rota com autenticação básica
 */

// do not use this!
function checkLogin($user, $pass) {
    return $user === 'admin' && $pass === 'admin';
}

$router->get('/admin', function () {
    return 'RestBeer Admin Protected!';
})->authBasic('Secret Area', function ($user, $pass) {
    return checkLogin($user, $pass);
});

// Rota para listar informações de uma cerveja
$router->get('/cervejas/*', function ($nome) use ($mapper) {

    // Validar com negação se string esta preenchida
    if ( v::not(v::alnum()->notEmpty()->noWhitespace())->validate($nome) ) {

        $cervejas = $mapper->cervejas->fetchAll();
        // como fazer isso no respect? ** olhar bot pro json e o retorno 200
        //return new Response (json_encode($cervejas), 200);
        header('HTTP/1.1 200 Ok');
        return json_encode($cervejas);
    }

    // tratar os dados
    $nome = filter_var( $nome, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();
    if ( !$cerveja ) {
        // como fazer isso no respect? ** olhar bot pro json e o retorno 404
        //return new Response (json_encode('Não encontrada'), 404);
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada'; 
    }

    header('HTTP/1.1 200 Ok');
    return json_encode($cerveja); 
});


$router->post('/cervejas', function () use ($mapper) {
    
    //pega os dados
    parse_str(file_get_contents('php://input'), $data);

    if ( !isset($data['cerveja']) ) {
        // como fazer isso no respect? ** olhar bot pro json e o retorno 404
        //return new Response('Faltam parâmetros', 400);
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // Validar o input
    $validation = v::arr()                                                        // validar se é array                  
                 ->key('nome',   $rule = v::alnum()->notEmpty()->noWhitespace())  // validar a key 'nome' se não está vazia   
                 ->key('estilo', $rule)                                           // utilizando a mesma regra da key de cima      
                 ->validate($data['cerveja']);

    if ( !$validation ) {
        // como fazer isso no respect? ** olhar bot pro json e o retorno 404
        //return new Response('Faltam parâmetros', 400);
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $cerveja         = new stdClass();
    $cerveja->nome   = filter_var($data['cerveja']['nome'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cerveja->estilo = filter_var($data['cerveja']['estilo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $mapper->author->persist($cerveja);
    $mapper->flush();

    // verificar se gravou
    if ( !isset($cerveja->id) || empty($cerveja->id) ) {
        // ver qual melhor header aqui
        header('HTTP/1.1 500 Internal Server Error');
        return 'Erro ao inserir cerveja';
    }
    
    //redireciona para a nova cerveja
    // como fazer isso no respect direito return $app->redirect('/cervejas/' . $data['nome'], 201);
    header('HTTP/1.1 201 Created');  
    header('Location: /cervejas/'.$cerveja->nome);
});

$router->put('/cervejas/*', function ($nome) use ($mapper) {

    //pega os dados
    parse_str(file_get_contents('php://input'), $data);

    if ( !isset($data['cerveja']) ) {
        // como fazer isso no respect? ** olhar bot pro json e o retorno 404
        //return new Response('Faltam parâmetros', 400);
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // Validar o input
    $validation = v::arr()                                                        // validar se é array                  
                 ->key('nome',   $rule = v::alnum()->notEmpty()->noWhitespace())  // validar a key 'nome' se não está vazia   
                 ->key('estilo', $rule)                                           // utilizando a mesma regra da key de cima      
                 ->validate($data['cerveja']);

    if ( !$validation ) {
        // como fazer isso no respect? ** olhar bot pro json e o retorno 404
        //return new Response('Faltam parâmetros', 400);
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $nome   = filter_var( $data['cerveja']['nome'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $estilo = filter_var( $data['cerveja']['estilo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // verificar se existe a cerveja
    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();
    if ( !$cerveja ) {
        // como fazer isso no respect? ** olhar bot pro json e o retorno 404
        //return new Response (json_encode('Não encontrada'), 404);
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada'; 
    }

    //Persiste na base de dados ($mapper retorna objeto preenchido full)
    $cerveja->nome   = $nome;
    $cerveja->estilo = $estilo;
    $mapper->cervejas->persist($cerveja);
    $mapper->flush();

    header('HTTP/1.1 200 Ok');
    return 'Cerveja atualizada';
});

$router->delete('/cervejas/*', function ($nome) use ($mapper) {

    // tratar os dados
    $nome = filter_var( $data['cerveja']['nome'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // Validar com negação se string esta preenchida
    if ( v::not(v::alnum()->notEmpty()->noWhitespace())->validate($nome) ) {
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // verificar se existe a cerveja
    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();
    if ( !$cerveja ) {
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada'; 
    }

    $mapper->cervejas->remove($cerveja);
    $mapper->flush();

    $app['db']->delete('cervejas', array('id' => $cerveja['id']));
    
    header('HTTP/1.1 200 Ok');
    return 'Cerveja removida';
});

// usar o always pra mostar testes de se tem token
//$router->always('By', ?????);

// fazer com o through??? ** definir pra geral que o tipo de resposta é json
/*$app->after(function (Request $request, Response $response) {
    $response->headers->set('Content-Type', 'text/json');
});*/