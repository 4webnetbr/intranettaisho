<?php

namespace Config;

use App\Controllers\Graph;
use App\Controllers\Rh\RhVale;
use App\Controllers\Rh\RhPonto;
use App\Controllers\Rh\RhSetor;
use App\Controllers\Rh\RhQuadro;
use App\Controllers\Rh\RhSolver;
use CodeIgniter\Config\Services;
use App\Controllers\Rh\RhGorjeta;
use App\Controllers\Rh\RhJornada;
use App\Controllers\Api\ApiConfig;
use App\Controllers\Rh\RhHolerite;
use App\Controllers\Api\ApiEstoque;
use App\Controllers\Config\CfgMenu;
use App\Controllers\Config\CfgTela;
use App\Controllers\Estoque\CfgApi;
use App\Controllers\Rh\RhPagamento;
use App\Controllers\Estoque\RhCargo;
use App\Controllers\Config\CfgModulo;
use App\Controllers\Config\CfgPerfil;
use App\Controllers\Config\CfgStatus;
use App\Controllers\Estoque\EstMarca;
use App\Controllers\Estoque\EstSaida;
use App\Controllers\Rh\RhColaborador;
use App\Controllers\Config\CfgEmpresa;
use App\Controllers\Config\CfgFuncoes;
use App\Controllers\Config\CfgUsuario;
use App\Controllers\Estoque\EstCompra;
use App\Controllers\Estoque\EstMinmax;
use App\Controllers\Estoque\EstPedido;
use App\Controllers\Config\CfgMensagem;
use App\Controllers\Config\Home_config;
use App\Controllers\Estoque\EstConsumo;
use App\Controllers\Estoque\EstCotacao;
use App\Controllers\Estoque\EstCotForn;
use App\Controllers\Estoque\EstEntrada;
use App\Controllers\Estoque\EstProduto;
use App\Controllers\Estoque\EstContagem;
use App\Controllers\Estoque\EstDeposito;
use App\Controllers\Estoque\EstRelSaida;
use App\Controllers\Config\CfgDicionario;
use App\Controllers\Estoque\EstRelCompra;
use App\Controllers\Estoque\EstRelPedido;
use App\Controllers\Estoque\EstUndMedida;
use App\Controllers\Estoque\EstFornecedor;
use App\Controllers\Estoque\EstRelEntrada;
use App\Controllers\Estoque\EstGrupoCompra;
use App\Controllers\Estoque\EstRecebimento;
use App\Controllers\Estoque\EstRelContagem;
use App\Controllers\Estoque\EstGrupoProduto;
use App\Controllers\Estoque\EstRelHistorico;
use App\Controllers\Estoque\EstRelMovimento;
use App\Controllers\Estoque\EstCompraCotacao;
use Estoque\EstCompraNaochegou;

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
$routes->setTranslateURIDashes(false);
$routes->get('(.+)\.map', function ($filename) {
    // Redireciona todas as chamadas a .map para um arquivo vazio
    return redirect()->to('/assets/empty.map');
});
$routes->set404Override(function () {
    log_message('critical', 'Rota 404 chamada: {uri}', ['uri' => current_url()]);
    return view('vw_semacesso', [
        'title' => current_url(),
        'permissao' => false,
        'erromsg' => "<h2>Atenção</h2>O Caminho <b>" . current_url() . "</b><br>
        <span style='color:red; font-size:16px'>Não foi Encontrado!</span><br>
        Informe o Problema ao Administrador do Sistema!",
    ]);
});
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
$routes->get('/home_config', 'Config\Home_config::index');

$routes->get('/CfgModulo', 'Config\CfgModulo::index');
$routes->get('/CfgModulo/(:any)', 'Config\CfgModulo::$1');
$routes->post('/CfgModulo/(:any)', 'Config\CfgModulo::$1');

$routes->get('/CfgTela', 'Config\CfgTela::index');
$routes->get('/CfgTela/(:any)', 'Config\CfgTela::$1');
$routes->post('/CfgTela/(:any)', 'Config\CfgTela::$1');

$routes->get('/CfgMenu', 'Config\CfgMenu::index');
$routes->get('/CfgMenu/(:any)', 'Config\CfgMenu::$1');
$routes->post('/CfgMenu/(:any)', 'Config\CfgMenu::$1');

$routes->get('/CfgDicionario', 'Config\CfgDicionario::index');
$routes->get('/CfgDicionario/(:any)', 'Config\CfgDicionario::$1');
$routes->post('/CfgDicionario/(:any)', 'Config\CfgDicionario::$1');

$routes->get('/CfgFuncoes', 'Config\CfgFuncoes::index');
$routes->get('/CfgFuncoes/(:any)', 'Config\CfgFuncoes::$1');
$routes->post('/CfgFuncoes/(:any)', 'Config\CfgFuncoes::$1');

$routes->get('/CfgPerfil', 'Config\CfgPerfil::index');
$routes->get('/CfgPerfil/(:any)', 'Config\CfgPerfil::$1');
$routes->post('/CfgPerfil/(:any)', 'Config\CfgPerfil::$1');

$routes->get('/CfgUsuario', 'Config\CfgUsuario::index');
$routes->get('/CfgUsuario/(:any)', 'Config\CfgUsuario::$1');
$routes->post('/CfgUsuario/(:any)', 'Config\CfgUsuario::$1');

$routes->get('/CfgMensagem', 'Config\CfgMensagem::index');
$routes->get('/CfgMensagem/(:any)', 'Config\CfgMensagem::$1');
$routes->post('/CfgMensagem/(:any)', 'Config\CfgMensagem::$1');

$routes->get('/CfgStatus', 'Config\CfgStatus::index');
$routes->get('/CfgStatus/(:any)', 'Config\CfgStatus::$1');
$routes->post('/CfgStatus/(:any)', 'Config\CfgStatus::$1');

$routes->get('/CfgEmpresa', 'Config\CfgEmpresa::index');
$routes->get('/CfgEmpresa/(:any)', 'Config\CfgEmpresa::$1');
$routes->post('/CfgEmpresa/(:any)', 'Config\CfgEmpresa::$1');

$routes->get('/CfgApi', 'Config\CfgApi::index');
$routes->get('/CfgApi/(:any)', 'Config\CfgApi::$1');
$routes->post('/CfgApi/(:any)', 'Config\CfgApi::$1');

$routes->match(['get', 'post'], '/CfgModulo/(:any)/(:any)', 'Config\CfgModulo::$1::$2');

$routes->get('/Graph', 'Graph::index');
$routes->get('/Graph/(:any)', 'Graph::$1');
$routes->post('/Graph/(:any)', 'Graph::$1');

// $routes->get('/(:any)', '$1');
// $routes->post('/(:any)/(:any)', '$1::$2');

$routes->get('/EstUndMedida', 'Estoque\EstUndMedida::index');
$routes->get('/EstUndMedida/(:any)', 'Estoque\EstUndMedida::$1');
$routes->post('/EstUndMedida/(:any)', 'Estoque\EstUndMedida::$1');

$routes->get('/EstDeposito', 'Estoque\EstDeposito::index');
$routes->get('/EstDeposito/(:any)', 'Estoque\EstDeposito::$1');
$routes->post('/EstDeposito/(:any)', 'Estoque\EstDeposito::$1');

$routes->get('/EstGrupoProduto', 'Estoque\EstGrupoProduto::index');
$routes->get('/EstGrupoProduto/(:any)', 'Estoque\EstGrupoProduto::$1');
$routes->post('/EstGrupoProduto/(:any)', 'Estoque\EstGrupoProduto::$1');

$routes->get('/EstGrupoCompra', 'Estoque\EstGrupoCompra::index');
$routes->get('/EstGrupoCompra/(:any)', 'Estoque\EstGrupoCompra::$1');
$routes->post('/EstGrupoCompra/(:any)', 'Estoque\EstGrupoCompra::$1');

$routes->get('/EstProduto', 'Estoque\EstProduto::index');
$routes->get('/EstProduto/(:any)', 'Estoque\EstProduto::$1');
$routes->post('/EstProduto/(:any)', 'Estoque\EstProduto::$1');

$routes->get('/EstMarca', 'Estoque\EstMarca::index');
$routes->get('/EstMarca/(:any)', 'Estoque\EstMarca::$1');
$routes->post('/EstMarca/(:any)', 'Estoque\EstMarca::$1');

$routes->get('/EstContagem', 'Estoque\EstContagem::index');
$routes->get('/EstContagem/(:any)', 'Estoque\EstContagem::$1');
$routes->post('/EstContagem/(:any)', 'Estoque\EstContagem::$1');

$routes->get('/EstFornecedor', 'Estoque\EstFornecedor::index');
$routes->get('/EstFornecedor/(:any)', 'Estoque\EstFornecedor::$1');
$routes->post('/EstFornecedor/(:any)', 'Estoque\EstFornecedor::$1');

$routes->get('/EstCompra', 'Estoque\EstCompra::index');
$routes->get('/EstCompra/(:any)', 'Estoque\EstCompra::$1');
$routes->post('/EstCompra/(:any)', 'Estoque\EstCompra::$1');

$routes->get('/EstRecebimento', 'Estoque\EstRecebimento::index');
$routes->get('/EstRecebimento/(:any)', 'Estoque\EstRecebimento::$1');
$routes->post('/EstRecebimento/(:any)', 'Estoque\EstRecebimento::$1');

$routes->get('/EstCompraNaochegou', 'Estoque\EstCompraNaochegou::index');
$routes->get('/EstCompraNaochegou/(:any)', 'Estoque\EstCompraNaochegou::$1');
$routes->post('/EstCompraNaochegou/(:any)', 'Estoque\EstCompraNaochegou::$1');

$routes->get('/EstEntrada', 'Estoque\EstEntrada::index');
$routes->get('/EstEntrada/(:any)', 'Estoque\EstEntrada::$1');
$routes->post('/EstEntrada/(:any)', 'Estoque\EstEntrada::$1');

// Controller de importação SEFAZ
$routes->get('/EstSefazImportacao', 'Estoque\EstSefazImportacao::index');
$routes->get('/EstSefazImportacao/(:any)', 'Estoque\EstSefazImportacao::$1');
$routes->post('/EstSefazImportacao/(:any)', 'Estoque\EstSefazImportacao::$1');

$routes->get('/EstSaida', 'Estoque\EstSaida::index');
$routes->get('/EstSaida/(:any)', 'Estoque\EstSaida::$1');
$routes->post('/EstSaida/(:any)', 'Estoque\EstSaida::$1');

$routes->get('/EstCotacao', 'Estoque\EstCotacao::index');
$routes->get('/EstCotacao/(:any)', 'Estoque\EstCotacao::$1');
$routes->post('/EstCotacao/(:any)', 'Estoque\EstCotacao::$1');

$routes->get('/EstCompraCotacao', 'Estoque\EstCompraCotacao::index');
$routes->get('/EstCompraCotacao/(:any)', 'Estoque\EstCompraCotacao::$1');
$routes->post('/EstCompraCotacao/(:any)', 'Estoque\EstCompraCotacao::$1');

$routes->get('/EstCotForn', 'Estoque\EstCotForn::index$1');
$routes->get('/EstCotForn/(:any)/(:any)', 'Estoque\EstCotForn::$1/$2');
$routes->post('/EstCotForn/(:any)/(:any)', 'Estoque\EstCotForn::$1/$2');

$routes->get('/EstCotForn/(:any)', 'Estoque\EstCotForn::$1');
$routes->post('/EstCotForn/(:any)', 'Estoque\EstCotForn::$1');

$routes->get('/EstCotForn/listaforn', 'Estoque\EstCotForn::listaforn');
$routes->post('/EstCotForn/listaforn', 'Estoque\EstCotForn::listaforn');

$routes->get('/EstPedido', 'Estoque\EstPedido::index');
$routes->get('/EstPedido/(:any)', 'Estoque\EstPedido::$1');
$routes->post('/EstPedido/(:any)', 'Estoque\EstPedido::$1');

$routes->get('/EstMinmax', 'Estoque\EstMinmax::index');
$routes->get('/EstMinmax/(:any)', 'Estoque\EstMinmax::$1');
$routes->post('/EstMinmax/(:any)', 'Estoque\EstMinmax::$1');

$routes->get('/EstConsumo', 'Estoque\EstConsumo::index');
$routes->get('/EstConsumo/(:any)', 'Estoque\EstConsumo::$1');
$routes->post('/EstConsumo/(:any)', 'Estoque\EstConsumo::$1');

$routes->get('/EstRelSaida', 'Estoque\EstRelSaida::index');
$routes->get('/EstRelSaida/(:any)', 'Estoque\EstRelSaida::$1');
$routes->post('/EstRelSaida/(:any)', 'Estoque\EstRelSaida::$1');

$routes->get('/EstRelEntrada', 'Estoque\EstRelEntrada::index');
$routes->get('/EstRelEntrada/(:any)', 'Estoque\EstRelEntrada::$1');
$routes->post('/EstRelEntrada/(:any)', 'Estoque\EstRelEntrada::$1');

$routes->get('/EstRelContagem', 'Estoque\EstRelContagem::index');
$routes->get('/EstRelContagem/(:any)', 'Estoque\EstRelContagem::$1');
$routes->post('/EstRelContagem/(:any)', 'Estoque\EstRelContagem::$1');

$routes->get('/EstRelHistorico', 'Estoque\EstRelHistorico::index');
$routes->get('/EstRelHistorico/(:any)', 'Estoque\EstRelHistorico::$1');
$routes->post('/EstRelHistorico/(:any)', 'Estoque\EstRelHistorico::$1');

$routes->get('/EstRelPedido', 'Estoque\EstRelPedido::index');
$routes->get('/EstRelPedido/(:any)', 'Estoque\EstRelPedido::$1');
$routes->post('/EstRelPedido/(:any)', 'Estoque\EstRelPedido::$1');

$routes->get('/EstRelCompra', 'Estoque\EstRelCompra::index');
$routes->get('/EstRelCompra/(:any)', 'Estoque\EstRelCompra::$1');
$routes->post('/EstRelCompra/(:any)', 'Estoque\EstRelCompra::$1');

$routes->get('/EstRelMovimento', 'Estoque\EstRelMovimento::index');
$routes->get('/EstRelMovimento/(:any)', 'Estoque\EstRelMovimento::$1');
$routes->post('/EstRelMovimento/(:any)', 'Estoque\EstRelMovimento::$1');

$routes->get('/RhCargo', 'Rh\RhCargo::index');
$routes->get('/RhCargo/(:any)', 'Rh\RhCargo::$1');
$routes->post('/RhCargo/(:any)', 'Rh\RhCargo::$1');

$routes->get('/RhSetor', 'Rh\RhSetor::index');
$routes->get('/RhSetor/(:any)', 'Rh\RhSetor::$1');
$routes->post('/RhSetor/(:any)', 'Rh\RhSetor::$1');

$routes->get('/RhJornada', 'Rh\RhJornada::index');
$routes->get('/RhJornada/(:any)', 'Rh\RhJornada::$1');
$routes->post('/RhJornada/(:any)', 'Rh\RhJornada::$1');

$routes->get('/RhQuadro', 'Rh\RhQuadro::index');
$routes->get('/RhQuadro/(:any)', 'Rh\RhQuadro::$1');
$routes->post('/RhQuadro/(:any)', 'Rh\RhQuadro::$1');

$routes->get('/RhColaborador', 'Rh\RhColaborador::index');
$routes->get('/RhColaborador/(:any)', 'Rh\RhColaborador::$1');
$routes->post('/RhColaborador/(:any)', 'Rh\RhColaborador::$1');

$routes->get('/RhHolerite', 'Rh\RhHolerite::index');
$routes->get('/RhHolerite/(:any)', 'Rh\RhHolerite::$1');
$routes->post('/RhHolerite/(:any)', 'Rh\RhHolerite::$1');

$routes->get('/RhPonto', 'Rh\RhPonto::index');
$routes->get('/RhPonto/(:any)', 'Rh\RhPonto::$1');
$routes->post('/RhPonto/(:any)', 'Rh\RhPonto::$1');

$routes->get('/RhPagamento', 'Rh\RhPagamento::index');
$routes->get('/RhPagamento/(:any)', 'Rh\RhPagamento::$1');
$routes->post('/RhPagamento/(:any)', 'Rh\RhPagamento::$1');

$routes->get('/RhGorjeta', 'Rh\RhGorjeta::index');
$routes->get('/RhGorjeta/(:any)', 'Rh\RhGorjeta::$1');
$routes->post('/RhGorjeta/(:any)', 'Rh\RhGorjeta::$1');

$routes->get('/RhSolver', 'Rh\RhSolver::index');
$routes->get('/RhSolver/(:any)', 'Rh\RhSolver::$1');
$routes->post('/RhSolver/(:any)', 'Rh\RhSolver::$1');

$routes->get('/RhVale', 'Rh\RhVale::index');
$routes->get('/RhVale/(:any)', 'Rh\RhVale::$1');
$routes->post('/RhVale/(:any)', 'Rh\RhVale::$1');


/*
 * --------------------------------------------------------------------
 * APIS
 * --------------------------------------------------------------------
*/
$routes->get('/ApiConfig', 'Api\ApiConfig::index');
$routes->get('/ApiConfig/(:any)', 'Api\ApiConfig::$1');
$routes->post('/ApiConfig/(:any)', 'Api\ApiConfig::$1');

$routes->get('/ApiEstoque', 'Api\ApiEstoque::index');
$routes->get('/ApiEstoque/(:any)', 'Api\ApiEstoque::$1');
$routes->post('/ApiEstoque/(:any)', 'Api\ApiEstoque::$1');

// $routes->resource('ApiEstoque', ['filter' => 'jwt']);

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
