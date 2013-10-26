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
// diretório precisa ter permissão de escrita também
$mapper = new Mapper(new PDO($config->dsn));


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

// Rota para listar informações de uma cerveja ou todas
$router->get('/cervejas/*', function ($data = null) use ($mapper) {

    // Validar com negação se string esta preenchida
    if ( !isset($data) ) {

        $cervejas = $mapper->cervejas->fetchAll();
        header('HTTP/1.1 200 Ok');
        return $cervejas;
    }

    // tratar os dados
    $data = filter_var( $data, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // validar conteúdo
    if ( v::not(v::alnum()->notEmpty())->validate($data) ) {
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada';
    }

    // buscar cerveja por id
    if ( v::int()->validate( $data ) ) {
        // buscar cerveja por id
        $cerveja = $mapper->cervejas[$data]->fetch();
    } else {
        // buscar cerveja pelo nome
        $cerveja = $mapper->cervejas(array( 'nome' => $data ))->fetch();
    }

    if ( !$cerveja ) {
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada'; 
    }

    header('HTTP/1.1 200 Ok');
    return $cerveja;
});


$router->post('/cervejas', function () use ($mapper) {
    
    //pega os dados via $_POST

    if ( !isset($_POST) || !isset($_POST['cerveja']) || v::not(v::arr())->validate($_POST['cerveja']) ) {
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // Validar o input
    $validation = v::arr()                                                        // validar se é array                  
                 ->key('nome',   $rule = v::alnum()->notEmpty()->noWhitespace())  // validar a key 'nome' se não está vazia   
                 ->key('estilo', $rule)                                           // utilizando a mesma regra da key de cima      
                 ->validate($_POST['cerveja']);

    if ( !$validation ) {
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $cerveja         = new stdClass();
    $cerveja->nome   = filter_var($_POST['cerveja']['nome'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cerveja->estilo = filter_var($_POST['cerveja']['estilo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // buscar cerveja pelo nome para ver se já tem
    $check = $mapper->cervejas(array( 'nome' => $cerveja->nome ))->fetch();
    if ( $check ) {
        header('HTTP/1.1 409 Conflict');
        return 'Cerveja já existe no sistema'; 
    }

    // gravar nova cerveja
    $mapper->cervejas->persist($cerveja);
    $mapper->flush();

    // verificar se gravou
    if ( !isset($cerveja->id) || empty($cerveja->id) ) {
        header('HTTP/1.1 500 Internal Server Error');
        return 'Erro ao inserir cerveja';
    }
    
    //redireciona para a nova cerveja
    header('HTTP/1.1 201 Created');
    return 'Cerveja criada'; 
});

$router->put('/cervejas/*', function ($nome) use ($mapper) {

    //pega os dados
    parse_str(file_get_contents('php://input'), $data);

    if ( !isset($data) || !isset($data['cerveja']) || v::not(v::arr())->validate($data['cerveja']) ) {
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // Validar o input
    $validation = v::arr()                                                        // validar se é array                  
                 ->key('nome',   $rule = v::alnum()->notEmpty()->noWhitespace())  // validar a key 'nome' se não está vazia   
                 ->key('estilo', $rule)                                           // utilizando a mesma regra da key de cima      
                 ->validate($data['cerveja']);

    if ( !$validation ) {
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // tratar os dados
    $nome = filter_var( $nome, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // validar conteúdo
    if ( v::not(v::alnum()->notEmpty())->validate($nome) ) {
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada';
    }

    // buscar cerveja pelo nome
    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();

    // BONUS - podemos buscar por id também 
    // $cerveja = $mapper->cervejas[$id]->fetch();

    if ( !$cerveja ) {
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada'; 
    }

    // tratar os dados
    $newNome   = filter_var( $data['cerveja']['nome'],   FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $newEstilo = filter_var( $data['cerveja']['estilo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    //Persiste na base de dados ($mapper retorna objeto preenchido full)
    $cerveja->nome   = $newNome;
    $cerveja->estilo = $newEstilo;
    $mapper->cervejas->persist($cerveja);
    $mapper->flush();

    header('HTTP/1.1 200 Ok');
    return 'Cerveja atualizada';
});

$router->delete('/cervejas/*', function ($nome) use ($mapper) {

    // tratar os dados
    $nome = filter_var( $nome, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    // Validar com negação se string esta preenchida
    if ( !isset($nome) || v::not(v::alnum()->notEmpty())->validate($nome) ) {
        header('HTTP/1.1 400 Bad Request');
        return 'Faltam parâmetros'; 
    }

    // verificar se existe a cerveja pelo nome
    $cerveja = $mapper->cervejas(array( 'nome' => $nome ))->fetch();

    // BONUS - podemos buscar por id também 
    // $cerveja = $mapper->cervejas[$id]->fetch();
    
    if ( !$cerveja ) {
        header('HTTP/1.1 404 Not Found');
        return 'Não encontrada'; 
    }

    $mapper->cervejas->remove($cerveja);
    $mapper->flush();
    
    header('HTTP/1.1 200 Ok');
    return 'Cerveja removida';
});

$jsonRender = function ($data) {
    header('Content-Type: application/json');
    if ( v::string()->validate($data) ) {
        $data = array($data);
    }
    return json_encode($data,true);
};

$router->always('Accept', array('application/json' => $jsonRender));

// para debugar melhor as exceptions
//$router->run();
