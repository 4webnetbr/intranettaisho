<?php namespace Config;

use App\Controllers\Setup;
use CodeIgniter\Config\Services;
// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
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
$routes->get('/home_setup', 'Setup\Home_setup::index');

$routes->get('/SetModulo', 'Setup\SetModulo::index');
$routes->get('/SetModulo/(:any)', 'Setup\SetModulo::$1');
$routes->post('/SetModulo/(:any)', 'Setup\SetModulo::$1');

$routes->get('/SetClasse', 'Setup\SetClasse::index');
$routes->get('/SetClasse/(:any)', 'Setup\SetClasse::$1');
$routes->post('/SetClasse/(:any)', 'Setup\SetClasse::$1');

$routes->get('/SetMenu', 'Setup\SetMenu::index');
$routes->get('/SetMenu/(:any)', 'Setup\SetMenu::$1');
$routes->post('/SetMenu/(:any)', 'Setup\SetMenu::$1');

$routes->get('/SetDicionario', 'Setup\SetDicionario::index');
$routes->get('/SetDicionario/(:any)', 'Setup\SetDicionario::$1');
$routes->post('/SetDicionario/(:any)', 'Setup\SetDicionario::$1');

$routes->get('/SetFuncoes', 'Setup\SetFuncoes::index');
$routes->get('/SetFuncoes/(:any)', 'Setup\SetFuncoes::$1');
$routes->post('/SetFuncoes/(:any)', 'Setup\SetFuncoes::$1');

$routes->match(['get', 'post'], '/SetModulo/(:any)/(:any)', 'Setup\SetModulo::$1::$2');

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
