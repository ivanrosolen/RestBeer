<?php

require 'vendor/autoload.php';
 
use Respect\Rest\Router;
 
// do not use this!
function checkLogin($user, $pass) {
	return $user === 'admin' && $pass === 'admin';
}
 
$r = new Router();
 
$r->get('/', function () {
	return 'RestBeer';
});
 
$r->get('/admin', function () {
	return 'RestBeer Admin Protected!';
})->authBasic('Secret Area', function ($user, $pass) {
	return checkLogin($user, $pass);
});