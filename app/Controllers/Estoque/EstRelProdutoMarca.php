<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;


class EstRelProdutoMarca extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $produto;
    public $contagem;
    public $entrada;
    public $saida;


    public function __construct()
    {
        $this->data  = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->empresa    = new  ConfigEmpresaModel();
        $this->produto    = new  EstoquProdutoModel();
        $this->saida       = new EstoquSaidaModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    public function index()
    {
        $this->def_campos();
        $campos[0] = $this->periodo;
        $campos[1] = $this->dash_empresa;
        $campos[2] = $this->dash_deposito;

        $colunas = ['Código', 'Código', 'Grupo', 'Grupo de Compra', 'Produto', 'Und Consumo', 'Und Compra', 'FCC', 'Cód Barras', 'Marca', 'Apres.', 'Und', 'FCK'];

        $this->data['cols']         = $colunas;
        $this->data['nome']         = 'relprodutomarcas';
        $this->data['campos']         = $campos;
        return view('vw_relprodutomarca', $this->data);
    }

    public function busca_dados()
    {
        // debug($dados_emp);
        $filtro          = $this->request->getVar();
        // debug($filtro, false);
        $inicio         = $filtro['inicio'];
        $fim            = $filtro['fim'];
        $inicio         = dataBrToDb($inicio);
        $fim            = dataBrToDb($fim);
        $empresa        = [$filtro['empresa']];
        $deposito       = $filtro['deposito'];

        $ret = [];
        $prods = [];
        if ($deposito != null) {
            $saidas       = $this->saida->getRelProdutoMarca($deposito, $empresa, $inicio, $fim);
            // debug($saidas);
            // debug(count($saidas));
            for ($p = 0; $p < count($saidas); $p++) {
                $prod = $saidas[$p];
                $prods[$p][0] = $prod['mar_codigo'];
                $prods[$p][1] = $prod['mar_codigo'];
                $prods[$p][2] = $prod['pro_nome'];
                $prods[$p][3] = dataDbToBr($prod['sai_data']);
                $qtia = formataQuantia(isset($prod['sap_quantia']) ? $prod['sap_quantia'] : 0);
                $conv = formataQuantia(isset($prod['sap_qtia_conv']) ? $prod['sap_qtia_conv'] : 0);
                $prods[$p][4] = $qtia['qtia'];
                $prods[$p][5] = $conv['qtia'];
                $prods[$p][6] = $prod['und_sigla'];
                $prods[$p][7] = $prod['sap_destino'];
                $prods[$p][8] = dataDbToBr($prod['sai_datahora']);
                $log = buscaLog('est_saida', $prod['sai_id']);
                $prods[$p][9] = $log['usua_alterou'];
                $prods[$p][10] = '';
            }
        }
        // debug(count($prods));
        // $ret['saidas'] = [];
        $ret['data'] = $prods;
        echo json_encode($ret);
    }
}
