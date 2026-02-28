<?php
require __DIR__ . '/vendor/autoload.php';
// boot CodeIgniter environment
$routes = \Config\Services::routes();
// dump all GET routes patterns
foreach ($routes->getRoutesForMethod('get') as $r) {
     echo $r->getOriginalRoute() . "\n";
}
$uri = new \CodeIgniter\HTTP\URI('backend/pdf/download/45');
$route = $routes->getRoute($uri);
var_dump($route);
