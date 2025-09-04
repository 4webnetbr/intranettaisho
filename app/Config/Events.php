<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use App\Models\LogMonModel;

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

Events::on('post_controller_constructor', function () {
    $usuNome = session()->get('usu_nome');
    $usuid = session()->get('usu_id');
    if (empty($usuNome)) {
        return; // não grava log se não estiver logado
    }

    $router  = service('router');
    $request = service('request');

    $controllerFull = $router->controllerName();
    $controller     = basename(str_replace('\\', '/', $controllerFull));
    $method         = $router->methodName();
    $ip             = $request->getIPAddress();
    $userAgent      = $request->getUserAgent()->getAgentString();
    $queryString    = $_SERVER['QUERY_STRING'] ?? '';
    $uriCompleta    = $request->getUri()->__toString(); // inclui query params
    $metodoHTTP     = $request->getMethod();

    // Ignorar controllers se quiser
    $ignorar = [
        'App\Controllers\Auth',
        'App\Controllers\Home',
    ];
    if (in_array($controller, $ignorar)) {
        return;
    }

    $mongo = new LogMonModel();

    $dados = [
        'log_tabela'        => '__acesso__',
        'log_operacao'      => 'acesso',
        'log_id_registro'   => null,
        'log_usuario_id'    => $usuid,
        'log_id_usuario'    => $usuNome,
        'log_data'          => date('Y-m-d H:i:s'),
        'log_controller'    => $controller,
        'log_metodo'        => $method,
        'log_ip'            => $ip,
        'log_uri'           => $uriCompleta,
        'log_query_string'  => $queryString,
        'log_user_agent'    => $userAgent,
        'log_metodo_http'   => strtoupper($metodoHTTP),
    ];

    $mongo->insertLogAcesso($dados);
});
