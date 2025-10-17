<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use App\Models\LogMonModel;
use App\Common;
/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        Services::toolbar()->respond();
    }
});

// ✅ LOG DE ACESSO POR CONTROLLER/MÉTODO
// Events::on('post_controller_constructor', function () {
//     $session = session();
//     $usuId   = $session->get('usu_id');
//     $usuNome = $session->get('usu_nome');

//     if (empty($usuId) || empty($usuNome)) {
//         return; // só loga se o usuário estiver logado
//     }

//     $router     = service('router');
//     $request    = service('request');

//     $controllerFull = $router->controllerName();
//     $controller     = basename(str_replace('\\', '/', $controllerFull));
//     $method         = $router->methodName();

//     $ip            = $request->getIPAddress();
//     $queryString   = $_SERVER['QUERY_STRING'] ?? '';
//     $uriCompleta   = $request->getUri()->__toString();
//     $userAgent     = $request->getUserAgent()->getAgentString();
//     $httpMethod    = strtoupper($request->getMethod());

//     // Mapeamento de método para operação
//     $map = [
//         'index'     => 'abriu',
//         'lista'     => 'listou',
//         'add'       => 'abriu_cadastro',
//         'edit'      => 'abriu_edicao',
//         'show'      => 'abriu_consulta',
//         'delete'    => 'excluiu',
//         'listaadd'  => 'listou_itens',
//         'store'     => 'gravou',
//     ];
//     $operacao = $map[$method] ?? 'acessou';

//     $uri     = $request->uri;

//     $idRegistro = null;

//     if ($uri->getTotalSegments() >= 3) {
//         $idRaw = $uri->getSegment(3);
//         $idRegistro = is_numeric($idRaw) ? (int) $idRaw : null;
//     }
//     // Monta o array de log
//     $log = [
//         'log_tabela'        => '__acesso__',
//         'log_operacao'      => $operacao,
//         'log_id_registro'   => $idRegistro,
//         'log_id_usuario'    => $usuNome,
//         'log_usuario_nome'  => $usuNome,
//         'log_usuario_id'    => $usuId,
//         'log_data'          => date('Y-m-d H:i:s'),
//         'log_controller'    => $controller,
//         'log_metodo'        => $method,
//         'log_dados'         => [
//             'ip'           => $ip,
//             'uri'          => $uriCompleta,
//             'query_string' => $queryString,
//             'user_agent'   => $userAgent,
//             'metodo_http'  => $httpMethod,
//         ]
//     ];

//     // (new MongoDBService())->getCollection('logs')->insertOne($log);
//     $mongo = new LogMonModel();
//     $mongo->insertLogAcesso($log);
// });

// Events::on('DBQuery', function ($query) {
//     $session = session();
//     $usuId   = $session->get('usu_id');
//     $usuNome = $session->get('usu_nome');

//     if (empty($usuId) || empty($usuNome)) {
//         return;
//     }

//     $sql       = $query->getQuery();
//     $operation = strtoupper(strtok(trim($sql), ' '));

//     // Ignora SELECT, só loga INSERT/UPDATE/DELETE
//     if (!in_array($operation, ['INSERT', 'UPDATE', 'DELETE'])) {
//         return;
//     }

//     // Detectar a tabela (via regex simples)
//     preg_match('/(?:INTO|UPDATE|FROM)\s+`?(\w+)`?/i', $sql, $matches);
//     $tabela = $matches[1] ?? 'desconhecida';

//     $request      = service('request');
//     $router       = service('router');
//     $controller   = basename(str_replace('\\', '/', $router->controllerName()));
//     $method       = $router->methodName();

//     $ip           = $request->getIPAddress();
//     $queryString  = $_SERVER['QUERY_STRING'] ?? '';
//     $uriCompleta  = $request->getUri()->__toString();
//     $userAgent    = $request->getUserAgent()->getAgentString();
//     $httpMethod   = strtoupper($request->getMethod());

//     // Bind values
//     $binds = $query->getBinds();

//     $uri     = $request->uri;

//     $idRegistro = null;

//     if ($uri->getTotalSegments() >= 3) {
//         $idRaw = $uri->getSegment(3);
//         $idRegistro = is_numeric($idRaw) ? (int) $idRaw : null;
//     }

//     $log = [
//         'log_tabela'        => $tabela,
//         'log_operacao'      => strtolower($operation),
//         'log_id_registro'   => $idRegistro,
//         'log_id_usuario'    => $usuNome,
//         'log_usuario_nome'  => $usuNome,
//         'log_usuario_id'    => $usuId,
//         'log_data'          => date('Y-m-d H:i:s'),
//         'log_controller'    => $controller,
//         'log_metodo'        => $method,
//         'log_dados'         => [
//             'ip'           => $ip,
//             'uri'          => $uriCompleta,
//             'query_string' => $queryString,
//             'user_agent'   => $userAgent,
//             'metodo_http'  => $httpMethod,
//             'query'        => $sql,
//             'binds'        => $binds,
//         ]
//     ];

//     // ✅ Dispara a gravação no MongoDB
//     // (new MongoDBService())->getCollection('logs')->insertOne($log);
//     $mongo = new LogMonModel();
//     $mongo->insertLogAcesso($log);
// });
// Listener global para alterações nas tabelas desejadas
Events::on('DBQuery', function ($query) {
    $sql = strtolower($query->getQuery());

    // Verifica se é um INSERT, UPDATE ou DELETE nas tabelas desejadas
    if (
        ((str_contains($sql, 'insert') ||
         str_contains($sql, 'update') ||
         str_contains($sql, 'delete')) &&

         (str_contains($sql, 'est_pedido') ||
         str_contains($sql, 'est_compra_produto')))
    ) {
        session()->setFlashdata('msgsocket', 'Resumo');
        envia_msg_ws();
    }
});