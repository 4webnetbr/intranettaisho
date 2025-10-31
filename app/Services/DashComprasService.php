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
        $indica[count($indica)] = 'Produtos<br>Comprados';
        $indica[count($indica)] = 'Solicitações<br>Pendentes';
        $indica[count($indica)] = 'Produtos<br>Recebidos ';
        $indica[count($indica)] = 'Recebimentos<br>Pendentes';
        $indica[count($indica)] = 'Entregas<br>Atrasadas';
        $indica[count($indica)] = 'Produtos NÃO<br>chegaram ';
        $indica[count($indica)] = 'Compras<br>Devolvidas';
        $indica[count($indica)] = 'Taxa de<br>Eficiência ';
        $indica[count($indica)] = 'Taxa de<br>Sucesso';
        $indica[count($indica)] = 'Taxa de<br>NÃO comprados';
        $indica[count($indica)] = 'Taxa de<br>NÃO recebidos';

        $valores[0] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;
        $valores[count($valores)] = 0;

        $cores = ['bg-primary', 'bg-success', 'bg-secondary', 'bg-danger', 'bg-warning', 'bg-info', 'bg-light', 'bg-body','bg-white'];


        // debug($empresa, true);
        $strEmpIds = implode(',', $empresa);
        $resumo = $this->compraModel->getResumoDashCompras($strEmpIds,$inicio,$fim);

        // SOMA OS RESULTADOS
        for ($r=0; $r < count($resumo) ; $r++) { 
            $resu = $resumo[$r];
            $recebidos = $resu['compras_total'] - $resu['compras_pendentes'];
            $valores[0]  += $resu['solic_total'];
            $valores[1]  += $resu['compras_total'];
            $valores[2]  += $resu['solic_pendentes'];
            $valores[3]  += $recebidos;
            $valores[4]  += $resu['compras_pendentes'];
            $valores[5]  += $resu['compras_atrasadas'];
            $valores[6]  += $resu['compras_naochegou'];
            $valores[7]  += $resu['compras_devolvidas'];
        }
        //$taxa_de_eficiencia = floatval(($valores[1] / $valores[0]) *100) ;
        $taxa_sucesso = floatval(($valores[3] / $valores[0]) *100) ;
        $taxa_nao_comprados = floatval(($valores[2] / $valores[0]) *100) ;
        $taxa_nao_recebidos = floatval(100 - $taxa_sucesso) ;
        taxa de não chegou, taxa de devolvidos
        $valores[8]  = number_format($taxa_de_eficiencia,2).'%';
        $valores[9]  = number_format($taxa_sucesso,2).'%';
        $valores[10]  = number_format($taxa_nao_comprados,2).'%';
        $valores[11]  = number_format($taxa_nao_recebidos,2).'%';
        $ret = view('partials/vw_cards_dashcompras', ['indica' => $indica, 'valores' =>$valores,'cores' => $cores]);
        // debug($ret, true);

        return $ret;
    }
}
