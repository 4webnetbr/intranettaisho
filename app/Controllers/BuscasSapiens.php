<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\SoapSapiens;

class BuscasSapiens extends BaseController
{
    public $data = [];
    public $menu;
    public $modulo; 
    public $tela;
    public $usuario; 
    public $admDados;

    public function __construct()
    {
    }

    public function buscaEmpresas()
    {
        $soapdep = new SoapSapiens('ceqweb_integra');
        // debug($soapdep, false);
        $ret_emps = $soapdep->empresasSapiens('ConsultaEmpresaFilial');

        return $ret_emps->retorno;
    }

    public function buscaDepositos()
    {
        $soapdep = new SoapSapiens('ceqweb_integra');
        // debug($soapdep, false);
        $ret_deps = $soapdep->depositosSapiens('ConsultarDepositos');

        return $ret_deps->retorno;
    }

    public function buscaEstoqueDeposito($deposito)
    {
        $soapdep = new SoapSapiens('ceqweb_integra');
        // debug($soapdep, false);
        $ret_deps = $soapdep->estoquePorDeposito($deposito);

        return $ret_deps->retorno;
    }

    public function buscaTransacoes()
    {
        $soapdep = new SoapSapiens('ceqweb_integra');
        // debug($soapdep, false);
        $ret_tnss = $soapdep->transacoesEstoque('ConsultarTransacoesEstoque');

        return $ret_tnss->retorno;
    }
}
