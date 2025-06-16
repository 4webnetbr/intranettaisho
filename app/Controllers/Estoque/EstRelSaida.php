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


class EstRelSaida extends BaseController
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

        $colunas = ['Código', 'Código', 'Produto', 'Ref Data', 'Saída', 'Conv.', 'Und', 'Destino', 'Data Reg', 'Usuário', ''];

        $this->data['cols']         = $colunas;
        $this->data['nome']         = 'relsaidas';
        $this->data['campos']         = $campos;
        return view('vw_relsaida', $this->data);
    }

    public function def_campos()
    {

        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo';
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->tamanho     = 30;
        $periodo->funcChan    = 'carrega_saidas()';
        $periodo->dispForm    = '3col';
        $this->periodo        = $periodo->crDaterange();

        $empresas = explode(',', session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_saidas()';
        $emp->dispForm              = '3col';
        $emp->largura               = 40;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crSelect();

        $depos = [];
        $deposito       = new EstoquDepositoModel();
        $dados_dep      = $deposito->getDeposito(false, $empresas);
        $depos = array_column($dados_dep, 'dep_nome', 'dep_id');

        $dep                        = new MyCampo();
        $dep->nome                  = 'deposito';
        $dep->id                    = 'deposito';
        $dep->label = $dep->place   = 'Depósito(s)';
        $dep->valor = $dep->selecionado = '';
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 40;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'empresa';
        $dep->dispForm              = '3col';
        $dep->funcChan              = 'carrega_saidas()';
        $this->dash_deposito        = $dep->crDepende();
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
            $saidas       = $this->saida->getRelSaida($deposito, $empresa, $inicio, $fim);
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
