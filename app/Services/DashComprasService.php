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
        $indica[0] = 'Produtos<br>Solicitados';
        $indica[1] = 'Produtos<br>Comprados';
        $indica[2] = '% Sucesso';
        $indica[3] = 'Solicitações<br>Pendentes';
        $indica[4] = '% NÃO comprados';
        $indica[5] = 'Produtos<br>Recebidos ';
        $indica[6] = 'Recebimentos<br>Pendentes';
        $indica[7] = '% NÃO recebidos';
        $indica[8] = 'Entregas<br>Atrasadas';
        $indica[9] = 'Produtos NÃO<br>chegaram ';
        $indica[10] = '% NÃO chegaram';
        $indica[11] = 'Compras<br>Devolvidas';
        $indica[12] = '% Devolução ';

        $valores[0] = 0;
        $valores[1] = 0;
        $valores[2] = 0;
        $valores[3] = 0;
        $valores[4] = 0;
        $valores[5] = 0;
        $valores[6] = 0;
        $valores[7] = 0;
        $valores[8] = 0;
        $valores[9] = 0;
        $valores[10] = 0;
        $valores[11] = 0;
        $valores[12] = 0;

        $cores[0] = 'bg-primary';
        $cores[1] = 'bg-success';
        $cores[2] = '';
        $cores[3] = 'bg-secondary';
        $cores[4] = '';
        $cores[5] = 'bg-info';
        $cores[6] = 'bg-warning';
        $cores[7] = '';
        $cores[8] = 'bg-danger';
        $cores[9] = 'bg-light';
        $cores[10] = '';
        $cores[11] = 'bg-secondary';
        $cores[12] = '';


        // debug($empresa, true);
        $strEmpIds = implode(',', $empresa);
        $resumo = $this->compraModel->getResumoDashCompras($strEmpIds,$inicio,$fim);

        // SOMA OS RESULTADOS
        for ($r=0; $r < count($resumo) ; $r++) { 
            $resu = $resumo[$r];
            $recebidos = $resu['compras_total'] - $resu['compras_pendentes'];
            $valores[0]  += $resu['solic_total'];
            $valores[1]  += $resu['compras_total'];
            $valores[3]  += $resu['solic_pendentes'];
            $valores[5]  += $recebidos;
            $valores[6]  += $resu['compras_pendentes'];
            $valores[8]  += $resu['compras_atrasadas'];
            $valores[9]  += $resu['compras_naochegou'];
            $valores[11]  += $resu['compras_devolvidas'];
        }
        //$taxa_de_eficiencia = floatval(($valores[1] / $valores[0]) *100) ;
        $taxa_sucesso = floatval(($valores[1] / $valores[0]) *100) ;
        $taxa_nao_comprados = floatval(($valores[3] / $valores[0]) *100) ;
        $taxa_nao_recebidos = floatval(($valores[6] / $valores[1]) *100) ;
        $taxa_nao_chegou = floatval(($valores[9] / $valores[1]) *100);
        $taxa_devolvidos = floatval(($valores[11] / $valores[1]) *100);
        $valores[2]  = number_format($taxa_sucesso,2).'%';
        $valores[4]  = number_format($taxa_nao_comprados,2).'%';
        $valores[7]  = number_format($taxa_nao_recebidos,2).'%';

        $valores[10]  = number_format($taxa_nao_chegou,2).'%';
        $valores[12]  = number_format($taxa_devolvidos,2).'%';
        $ret = view('partials/vw_cards_dashcompras', ['indica' => $indica, 'valores' =>$valores,'cores' => $cores]);
        // debug($ret, true);

        return $ret;
    }
}
