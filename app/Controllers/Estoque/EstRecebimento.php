<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstRecebimento extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $compra;
    public $marca;
    public $entrada;
    public $common;
    public $empresa;
    public $pedido;
    public $produto;
    public $dash_empresa;
    public $for_id;
    public $cop_valor;
    public $cop_total;
    public $com_previsao;
    public $com_id;
    public $cop_id;
    public $und_id;
    public $ped_id;
    public $pro_id;
    public $cop_quantia;
    public $ped_qtia;
    public $und_sigla;


    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->compra = new EstoquCompraModel();
        $this->entrada = new EstoquEntradaModel();
        $this->common    = new CommonModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->pedido       = new EstoquPedidoModel();
        $this->produto       = new EstoquProdutoModel();
        $this->marca       = new EstoquMarcaModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']         = 'recebe';
        $this->data['colunas']      = montaColunasLista($this->data, 'com_id');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista');
        $this->data['campos']         = $campos;
        $this->data['script']       = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1, $idempresa = false)
    {
        $empresas = explode(',', session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');
        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';
        $emp->valor = $emp->selecionado  = $idempresa ? $idempresa : $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = 'linha';
        $emp->largura               = 50;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        if ($tipo == 1) {
            $emp->funcChan              = "carrega_lista(this,'EstRecebimento/lista','compra')";
        } else {
            $emp->funcChan              = "carrega_lista_edit(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
        }

        $this->dash_empresa         = $emp->crSelect();
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = [0];
        } else {
            $param = [$param];
        }
        // $campos = montaColunasCampos($this->data, 'com_id');
        // debug($campos, true);
        $dados_compr = $this->compra->getCompraProdPendente(false, $param);
        $com_ids_assoc = array_column($dados_compr, 'com_id');
        $log = buscaLogTabela('est_compra', $com_ids_assoc);
        // $this->data['edicao'] = false;
        $this->data['exclusao'] = false;
        foreach ($dados_compr as &$com) {
            // Verificar se o log já está disponível para esse ana_id
            $com['com_usuario'] = $log[$com['com_id']]['usua_alterou'] ?? '';
            $com['cop_previsao'] = ($com['cop_previsao'] != null)?$com['cop_previsao']:$com['com_previsao']; 
            $com['com_previsao'] = ($com['com_previsao'] != null)?$com['com_previsao']:$com['cop_previsao']; 
            $qtia = formataQuantia(isset($com['cop_quantia']) ? $com['cop_quantia'] : 0);
            $com['cop_quantia'] = $qtia['qtia'];
        }
        $compr = [
            'data' => montaListaColunas($this->data, 'cop_id', $dados_compr, 'pro_nome'),
        ];
        // cache()->save('compr', $compr, 60000);
        // }

        echo json_encode($compr);
    }


    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id, $show = false)
    {
        $dados_ent = $this->compra->getCompraCop($id)[0];
        $this->def_campos($dados_ent, true);
        $this->def_campos_pro($dados_ent, 0, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->ent_id;
        $campos[0][count($campos[0])] = $this->mar_codigo;
        $campos[0][count($campos[0])] = $this->ent_data;
        $campos[0][count($campos[0])] = $this->emp_id;
        $campos[0][count($campos[0])] = $this->dep_id;
        $campos[0][count($campos[0])] = $this->for_id;
        $campos[0][count($campos[0])] = $this->com_id;
        $campos[0][count($campos[0])] = $this->enp_id;
        $campos[0][count($campos[0])] = $this->pro_id;
        $campos[0][count($campos[0])] = $this->pro_nome;
        $campos[0][count($campos[0])] = $this->und_id;
        $campos[0][count($campos[0])] = $this->enp_quantia;
        $campos[0][count($campos[0])] = $this->enp_valor;
        $campos[0][count($campos[0])] = $this->enp_total;
        // $campos[0][count($campos[0])] = $this->enp_saldo;
        $campos[0][count($campos[0])] = $this->mar_nome;
        $campos[0][count($campos[0])] = $this->unm_id;
        $campos[0][count($campos[0])] = $this->enp_conversao;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['desc_metodo'] = 'Recebimento de Produto';
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>jQuery('#mar_codigo').focus();jQuery('#enp_quantia').focus();</script>";

        echo view('vw_edicao', $this->data);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_pro($dados = false, $pos = 0, $show = false)
    {
        // debug($dados);
        $id = new MyCampo('est_entrada_produto', 'enp_id');
        $id->nome = "enp_id";
        $id->id = "enp_id";
        $id->valor = isset($dados['enp_id']) ? $dados['enp_id'] : '';
        // $id->repete            = true;
        $this->enp_id = $id->crOculto();

        if($dados['gru_controlaestoque'] == 'N'){ // busca a marca, traz preenchida e pede a quantidade
            $marcax = $this->marca->getMarcaProd($dados['pro_id']);
            if($marcax){
                $marcax = $marcax[0];
            }
        }
 
        $mcd                    = new MyCampo('est_entrada_produto', 'mar_codigo');
        $mcd->funcBlur          = "buscaProdutoMarca(this, 1)";
        // $mcd->dispForm          = "col-4";
        $mcd->naocolar          = false;
        $mcd->leitura           = $show;
        $mcd->valor             = isset($dados['mar_codigo']) ? $dados['mar_codigo'] : '';
        if($dados['gru_controlaestoque'] == 'N'){ // busca a marca, traz preenchida e pede a quantidade
            $mcd->leitura           = true;
            $mcd->valor             = $marcax['mar_codigo'];
            $dados['enp_conversao'] = $marcax['mar_conversao'];
        }
        $this->mar_codigo       = $mcd->crInput();

        $pro                    = new MyCampo('est_entrada_produto', 'pro_id');
        // $pro->nome              = "pro_id";
        // $pro->ordem             = $pos;
        // $pro->id                = "pro_id";
        $pro->valor             = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        // $pro->repete            = true;
        $pro->leitura           = $show;
        $this->pro_id           = $pro->crOculto();

        $prn                        = new MyCampo('est_produto', 'pro_nome');
        // $prn->id = $prn->nome       = "pro_nome[$pos]";
        $prn->valor                 = isset($dados['pro_nome']) ? $dados['pro_nome'] : '';
        // $prn->ordem                 = $pos;
        // $prn->repete                = true;
        $prn->leitura               = true;
        $prn->largura               = 40;
        // $prn->dispForm              = 'col-4';
        $prn->place             = "";
        $this->pro_nome             = $prn->crInput();

        $unidades                   = new EstoquUndMedidaModel();
        $lst_unds                   = $unidades->getUndMedida();
        $opc_und                    = array_column($lst_unds, 'und_completa', 'und_id');

        $und                        = new MyCampo('est_entrada_produto', 'und_id');
        // $und->id = $und->nome       = "und_id[$pos]";
        $und->label                 = 'Und Prod';
        // $und->ordem                 = $pos;
        // $und->repete                = true;
        $und->leitura               = true;
        $und->valor = $und->selecionado  = isset($dados['und_id']) ? $dados['und_id'] : '';
        $und->largura               = 30;
        // $und->dispForm              = 'col-2';
        $und->classep               = ' text-nowrap';
        $und->opcoes                = $opc_und;
        $und->place             = "";
        $this->und_id               = $und->crSelect();

        $mar                        = new MyCampo('est_marca', 'mar_nome');
        // $mar->id = $mar->nome       = "mar_nome[$pos]";
        $mar->valor                 = isset($dados['mar_nome']) ? $dados['mar_nome'] . ' - ' . $dados['mar_apresenta'] : '';
        // $mar->ordem                 = $pos;
        $mar->leitura               = true;
        $mar->largura               = 30;
        $mar->dispForm              = 'col-3 d-none';
        $mar->place                 = "";
        $this->mar_nome             = $mar->crInput();

        $unm                        = new MyCampo('est_entrada_produto', 'und_id');
        // $unm->id = $unm->nome       = "unm_id[$pos]";
        $unm->label                 = 'Und Marca';
        // $unm->ordem                 = $pos;
        // $unm->repete                = true;
        $unm->leitura               = true;
        $unm->valor = $unm->selecionado  = isset($dados['unm_id']) ? $dados['unm_id'] : '';
        $unm->largura               = 15;
        $unm->dispForm              = 'col-2 d-none';
        $unm->classep               = ' text-nowrap';
        $unm->opcoes                = $opc_und;
        $unm->place             = "";
        $this->unm_id               = $unm->crSelect();


        $conv = formataQuantia(isset($dados['enp_conversao']) ? $dados['enp_conversao'] : 0);
        $con                        = new MyCampo('est_entrada_produto', 'enp_conversao');
        // $con->id = $con->nome       = "conversao[$pos]";
        $con->label                 = 'Fator de Conversão';
        // $con->ordem                 = $pos;
        $con->valor                 = $conv['qtiv'];
        $con->decimal               = $conv['dec'];
        // $con->repete                = true;
        $con->leitura               = true;
        $con->dispForm              = 'col-4 d-none';
        $this->enp_conversao        = $con->crInput();


        $qti                        = new MyCampo('est_entrada_produto', 'enp_quantia');
        // $qti->id = $qti->nome       = "enp_quantia[$pos]";
        // $qti->ordem                 = $pos;
        // $qti->repete                = true;
        // $qti->obrigatorio           = true;
        $quantia = formataQuantia($dados['cop_quantia'])['qtiv'];
        $qti->valor                 = $quantia;
        $qti->tipo                  = 'text';
        $qti->largura               = 30;
        $qti->maximo                = 999999;
        $qti->leitura               = true;
        if($dados['gru_controlaestoque'] == 'N'){ // busca a marca, traz preenchida e pede a quantidade
            $qti->leitura               = false;
            $qti->funcBlur              = "calculaTotal(this,'enp_quantia', 'enp_valor', 'enp_total');";
            $qti->valor                 = '';
        }
        // $qti->dispForm              = 'col-12';
        $qti->place                 = "";
        $this->enp_quantia          = $qti->crInput();

        $sal                        = new MyCampo('est_entrada_produto', 'enp_quantia');
        $sal->id = $sal->nome       = "enp_saldo[$pos]";
        $sal->tipo                 = 'sonumero';
        $sal->label                 = 'Saldo';
        // $sal->ordem                 = $pos;
        $sal->valor                 = isset($dados['enp_saldo']) ? $dados['enp_saldo'] : "";
        $sal->largura               = 30;
        $sal->dispForm              = 'col-12 d-none';
        $sal->leitura               = true;
        $sal->place             = "";
        $this->enp_saldo            = $sal->crInput();


        $val                        = new MyCampo('est_entrada_produto', 'enp_valor');
        // $val->id = $val->nome       = "enp_valor[$pos]";
        // $val->ordem                 = $pos;
        // $val->repete                = true;
        // $val->obrigatorio           = true;
        // debug($dados['enp_valor']);
        $val->valor                 = isset($dados['cop_valor']) ? $dados['cop_valor'] : '';
        $val->largura               = 30;
        // $val->funcBlur              = "calculaTotal(this,'enp_quantia', 'enp_valor', 'enp_total')";
        // $val->dispForm              = 'col-12';
        $val->leitura               = true;
        $val->place                 = "";
        $this->enp_valor            = $val->crInput();

        $tot                        = new MyCampo('est_entrada_produto', 'enp_total');
        // $tot->id = $tot->nome       = "enp_total[$pos]";
        // $tot->ordem                 = $pos;
        // $tot->repete                = true;
        $tot->leitura               = true;
        $tot->valor                 = isset($dados['cop_total']) ? $dados['cop_total'] : '';
        $tot->largura               = 30;
        // $tot->dispForm              = 'col-12';
        $tot->place                 = "";
        $this->enp_total            = $tot->crInput();
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos($dados = false, $show = false)
    {
        $opc_fornec = [];
        if (isset($dados['for_id'])) {
            $fornec = new EstoquFornecedorModel();
            $lst_fornec = $fornec->getFornecedor($dados['for_id']);
            $opc_fornec = array_column($lst_fornec, 'for_razao', 'for_id');
        }

        $id             = new MyCampo('est_entrada', 'ent_id');
        $id->valor      = isset($dados['ent_id']) ? $dados['ent_id'] : '';
        $this->ent_id   = $id->crOculto();

        $max = date("Y-m-d");
        $data               = new MyCampo('est_entrada', 'ent_data');
        $data->obrigatorio  = true;
        $data->valor        = isset($dados['ent_data']) ? $dados['ent_data'] : date('Y-m-d');
        $data->largura      = 20;
        $data->maxdata      = $max;
        $data->mindata      = $max;
        $data->leitura      = true;
        $this->ent_data     = $data->crInput();

        $forn               = new MyCampo('est_entrada', 'for_id');
        $forn->obrigatorio  = true;
        $forn->valor        = isset($dados['for_id']) ? $dados['for_id'] : '';
        $forn->cadModal     = base_url('EstFornecedor/add/modal=true');
        $forn->opcoes       = $opc_fornec;
        $forn->largura      = 50;
        $forn->urlbusca     = base_url('buscas/buscaFornecedor');
        $forn->leitura      = $show;
        $this->for_id       = $forn->crSelbusca();

        $empresas           = explode(',', session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('est_entrada', 'emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id']) ? $dados['emp_id'] : '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $this->emp_id               = $emp->crSelect();

        $depos = [];
        $depositos           = explode(',', session()->get('usu_deposito'));
        $deposito       = new EstoquDepositoModel();
        if($deposito != ''){
            $dados_dep      = $deposito->getDeposito($depositos, $dados['emp_id']);
        } else {
            $dados_dep      = $deposito->getDeposito(false, $dados['emp_id']);
        }
        $depos = array_column($dados_dep, 'dep_nome', 'dep_id');
        // }
        $dep                        = new MyCampo('est_entrada', 'dep_id');
        $dep->valor = $dep->selecionado = isset($dados['dep_id']) ? $dados['dep_id'] : array_key_first($depos);
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 50;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'emp_id';
        $dep->leitura               = false;
        $this->dep_id               = $dep->crDepende();

        $comp               = new MyCampo('est_entrada', 'com_id');
        $comp->valor        = isset($dados['com_id']) ? $dados['com_id'] : '';
        $this->com_id       = $comp->crOculto();
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function store()
    {
        $ret = [];
        $ret['erro'] = false;
        $erros = [];
        $dados = $this->request->getPost();
        // debug($dados, true);
        $contagem = new EstoquContagemModel();
        $ultimo   = $contagem->getUltimaContagem($dados['dep_id']);
        if ($ultimo) {
            if (date('Y-m-d', strtotime($dados['ent_data'])) < date('Y-m-d', strtotime($ultimo[0]['data_ultima_contagem']))) {
                $ret['erro'] = true;
                $ret['msg'] = 'A data de entrada não pode ser anterior a Última Contagem ' . dataDbToBr($ultimo[0]['data_ultima_contagem']) . ', Verifique!';
            }
        }
        if (!$ret['erro']) {
            $total_ent = floatval(moedaToFloat($dados['enp_total']));
        }
        // if (!$ret['erro']) {
        //     $total_ent = 0;
        //     if (isset($dados['pro_id']) && count($dados['pro_id']) > 0) {
        //         foreach ($dados['pro_id'] as $key => $value) {
        //             $total_ent += floatval(moedaToFloat($dados['enp_total'][$key]));
        //         }
        //     } else {
        //         $ret['erro'] = true;
        //         $ret['msg'] = 'É necessário informar pelo menos 1 Produto, Verifique!';
        //     }
        // }
        if (!$ret['erro']) {
            // foreach ($dados['pro_id'] as $key => $value) {
                // $fck = str_replace(',', '.', $dados['conversao'][$key]);
                $fck = str_replace(',', '.', $dados['enp_conversao']);
                if ((float)$fck <= 0) {
                    $ret['erro'] = true;
                    // $ret['msg'] = 'O Fator de Conversão do Produto ' . $dados['pro_nome'][$key] . ' Não pode zer ZERO(0), Verifique!';
                    $ret['msg'] = 'O Fator de Conversão do Produto ' . $dados['pro_nome'] . ' Não pode zer ZERO(0), Verifique!';
                    // break;
                }
            // }
        }
        if (!$ret['erro']) {
            $dados_cta = [
                'ent_id'    => $dados['ent_id'],
                'ent_data'  => $dados['ent_data'],
                'emp_id'    => $dados['emp_id'],
                'for_id'    => $dados['for_id'],
                'dep_id'    => $dados['dep_id'],
                'com_id'    => $dados['com_id'],
                'ent_valor'    => $total_ent,
            ];
            if ($this->entrada->save($dados_cta)) {
                $ent_id = $this->entrada->getInsertID();
                if ($dados['ent_id'] != '') {
                    $ent_id = $dados['ent_id'];
                }
                if (isset($dados['pro_id'])) {
                    $data_atu = date('Y-m-d H:i:s');
                    $cta_exc = $this->common->deleteReg('dbEstoque', 'est_entrada_produto', "ent_id = " . $ent_id);
                    // foreach ($dados['pro_id'] as $key => $value) {
                    //     // $qtiaconv = $dados['enp_quantia'][$key] * floatval($dados['enp_conversao'][$key]);
                    //     $valor = moedaToFloat($dados['enp_valor'][$key]);
                    //     $total = moedaToFloat($dados['enp_total'][$key]);
                    //     // debug($valor);
                    //     // debug($total,true);
                    //     $dados_pro = [
                    //         'ent_id'    => $ent_id,
                    //         'mar_codigo'    => $dados['mar_codigo'][$key],
                    //         'pro_id'    => $dados['pro_id'][$key],
                    //         'und_id'    => $dados['und_id'][$key],
                    //         'enp_quantia'   => $dados['enp_quantia'][$key],
                    //         'enp_valor'   => $valor,
                    //         'enp_conversao'   => str_replace(',', '.', $dados['conversao'][$key]),
                    //         'enp_total'   => $total,
                    //         'enp_atualizado' => $data_atu
                    //     ];
                    //     try {
                    //         $salva = $this->common->insertReg('dbEstoque', 'est_entrada_produto', $dados_pro);
                    //     } catch (\Throwable $th) {
                    //         $ret['erro'] = true;
                    //         $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                    //         break;
                    //     }
                    // }
                    // foreach ($dados['pro_id'] as $key => $value) {
                        // $qtiaconv = $dados['enp_quantia'][$key] * floatval($dados['enp_conversao'][$key]);
                        $valor = moedaToFloat($dados['enp_valor']);
                        $total = moedaToFloat($dados['enp_total']);
                        // debug($valor);
                        // debug($total,true);
                        $dados_pro = [
                            'ent_id'    => $ent_id,
                            'mar_codigo'    => $dados['mar_codigo'],
                            'pro_id'    => $dados['pro_id'],
                            'und_id'    => $dados['und_id'],
                            'enp_quantia'   => str_replace(',', '.',$dados['enp_quantia']),
                            'enp_valor'   => $valor,
                            'enp_conversao'   => str_replace(',', '.', $dados['enp_conversao']),
                            'enp_total'   => $total,
                            'enp_atualizado' => $data_atu
                        ];
                        try {
                            $salva = $this->common->insertReg('dbEstoque', 'est_entrada_produto', $dados_pro);
                        } catch (\Throwable $th) {
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                        }
                    // }
                }
                // atualiza a Compra como Recebida
                $completo = $this->compra->getCompraVsEntrada($dados['com_id'])[0];
                if($completo['entrada_completa'] == 1){
                    $dados_com = [
                        'com_id'    => $dados['com_id'],
                        'com_status'    => 'R',
                    ];
                    $this->compra->save($dados_com);
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Recebimento gravado com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            } else {
                $erros = $this->entrada->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar a Entrada de Produto, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }
        echo json_encode($ret);
    }
}
