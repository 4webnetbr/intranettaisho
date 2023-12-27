<?php namespace Config;

use App\Controllers\Config;
use CodeIgniter\Config\Services;
// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Config
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();
$routes->setAutoRoute(true);

// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'home::index');
$routes->get('/home_config', 'Configur\Home_config::index');

$routes->get('/CfgModulo', 'Configur\CfgModulo::index');
$routes->get('/CfgModulo/(:any)', 'Configur\CfgModulo::$1');
$routes->post('/CfgModulo/(:any)', 'Configur\CfgModulo::$1');

$routes->get('/CfgTela', 'Configur\CfgTela::index');
$routes->get('/CfgTela/(:any)', 'Configur\CfgTela::$1');
$routes->post('/CfgTela/(:any)', 'Configur\CfgTela::$1');

$routes->get('/CfgMenu', 'Configur\CfgMenu::index');
$routes->get('/CfgMenu/(:any)', 'Configur\CfgMenu::$1');
$routes->post('/CfgMenu/(:any)', 'Configur\CfgMenu::$1');

$routes->get('/CfgDicionario', 'Configur\CfgDicionario::index');
$routes->get('/CfgDicionario/(:any)', 'Configur\CfgDicionario::$1');
$routes->post('/CfgDicionario/(:any)', 'Configur\CfgDicionario::$1');

$routes->get('/CfgFuncoes', 'Configur\CfgFuncoes::index');
$routes->get('/CfgFuncoes/(:any)', 'Configur\CfgFuncoes::$1');
$routes->post('/CfgFuncoes/(:any)', 'Configur\CfgFuncoes::$1');

$routes->get('/CfgPerfil', 'Configur\CfgPerfil::index');
$routes->get('/CfgPerfil/(:any)', 'Configur\CfgPerfil::$1');
$routes->post('/CfgPerfil/(:any)', 'Configur\CfgPerfil::$1');

$routes->get('/CfgUsuario', 'Configur\CfgUsuario::index');
$routes->get('/CfgUsuario/(:any)', 'Configur\CfgUsuario::$1');
$routes->post('/CfgUsuario/(:any)', 'Configur\CfgUsuario::$1');

$routes->match(['get', 'post'], '/CfgModulo/(:any)/(:any)', 'Configur\CfgModulo::$1::$2');

// $routes->get('/(:any)', '$1');
// $routes->post('/(:any)/(:any)', '$1::$2');


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
