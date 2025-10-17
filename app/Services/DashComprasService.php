<?php

namespace App\Services;

use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;

class DashComprasService
{
    protected ConfigEmpresaModel $empresaModel;
    protected EstoquCompraModel $compraModel;

    public function __construct()
    {
        // Instâncias dos models originais
        $this->empresaModel = new ConfigEmpresaModel();
        $this->compraModel = new EstoquCompraModel();
    }

    /**
     * Armazena variáveis de sessão (fase de "secao")
     */
    public function buscarSecao(array $vars): void
    {
        session()->set($vars);
    }

    /**
     * Monta e retorna a string da tela para cada indicador
     */
    public function buscarDadosCompras(
        string $inicio,
        string $fim,
        $empresa,
    ){
        // prepara retorno inicial
        $indica[0] = 'Solicitações<br>Pendentes';
        $indica[1] = 'Recebimentos<br>Pendentes';
        $indica[2] = 'Entregas<br>Atrasadas';
        $indica[3] = 'Produtos NÃO<br>chegaram';
        $indica[4] = 'Compras<br>Devolvidas';

        $valores[0] = 0;
        $valores[1] = 0;
        $valores[2] = 0;
        $valores[3] = 0;
        $valores[4] = 0;

        $cores = ['bg-primary', 'bg-info', 'bg-danger', 'bg-white', 'bg-warning'];


        // debug($empresa, true);
        $strEmpIds = implode(',', $empresa);
        $resumo = $this->compraModel->getResumoDashCompras($strEmpIds,$inicio,$fim);

        // SOMA OS RESULTADOS
        for ($r=0; $r < count($resumo) ; $r++) { 
            $resu = $resumo[$r];
            $valores[0]  += $resu['solic_pendentes'];
            $valores[1]  += $resu['compras_pendentes'];
            $valores[2]  += $resu['compras_atrasadas'];
            $valores[3]  += $resu['compras_naochegou'];
            $valores[4]  += $resu['compras_devolvidas'];
        }
        $ret = view('partials/vw_cards_dashcompras', ['indica' => $indica, 'valores' =>$valores,'cores' => $cores]);
        // debug($ret, true);

        return $ret;
    }
}
