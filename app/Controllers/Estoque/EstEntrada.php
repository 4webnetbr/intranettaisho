<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\Config\CfgEmpresa;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquConversaoModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstEntrada extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $entrada;
    public $common;
    public $empresa;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->entrada = new EstoquEntradaModel();
        $this->common    = new CommonModel();
        $this->empresa       = new ConfigEmpresaModel();

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

        $this->data['nome']         = 'entrada';
        $this->data['colunas'] = montaColunasLista($this->data, 'ent_id', 'd');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista_prod');
        $this->data['campos']         = $campos;
        $this->data['script'] = "<script>carrega_lista_detail('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista()
    {

        $empresas = explode(',', session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = 'linha';
        $emp->largura               = 50;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        $emp->funcChan              = "carrega_lista_detail(this,'EstEntrada/lista_prod','entrada')";
        $this->dash_empresa         = $emp->crSelect();
    }

    // public function index()
    // {
    //     $this->data['colunas'] = montaColunasLista($this->data, 'cta_id','d');
    //     $this->data['url_lista'] = base_url($this->data['controler'] . '/lista_prod');
    //     echo view('vw_lista_details', $this->data);

    //     // $this->data['colunas'] = montaColunasLista($this->data, 'ent_id');
    //     // $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
    //     // echo view('vw_lista', $this->data);
    // }
    // /**
    // * Listagem
    // * lista
    // *
    // * @return void
    // */
    // public function lista()
    // {
    //     // if (!$entrad = cache('entrad')) {
    //         $campos = montaColunasCampos($this->data, 'ent_id');
    //         $dados_entrad = $this->entrada->getEntradaLista();
    //         $entrad = [
    //             'data' => montaListaColunas($this->data, 'ent_id', $dados_entrad, $campos[1]),
    //         ];
    //         cache()->save('entrad', $entrad, 60000);
    //     // }

    //     echo json_encode($entrad);
    // }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista_prod()
    {
        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = [0];
        } else {
            $param = [$param];
        }

        // if (!$compr = cache('compr')) {
        $campos = montaColunasCampos($this->data, 'ent_id', 'd');
        // debug($campos, true);
        $dados_entrad = $this->entrada->getEntradaLista(false, $param);
        for ($dc = 0; $dc < count($dados_entrad); $dc++) {
            $dados_entrad[$dc]['d'] = '';
            $ent = $dados_entrad[$dc];
            $log = buscaLog('est_entrada', $ent['ent_id']);
            $dados_entrad[$dc]['ent_usuario'] = $log['usua_alterou'];
        }
        $entradas = montaListaColunas($this->data, 'ent_id', $dados_entrad, $campos[1], true);
        for ($cp = 0; $cp < count($entradas); $cp++) {
            $entr = $entradas[$cp];
            $entradas[$cp]['col_details'] = [
                'tit' => ['Produto', 'Qtia', 'Fator Conv', 'Qtia Conv', 'Und', 'Unit', 'Total'],
                'tam' => ['col-4', 'col-1', 'col-1', 'col-1', 'col-1', 'col-1', 'col-2'],
                'cam' => ['pro_nome', 'enp_quantia', 'enp_conversao', 'enp_qtia_conv', 'und_sigla', 'enp_valor', 'enp_total'],
            ];
            $dados_prods = $this->entrada->getEntradaProd($entr[0]);
            for ($p = 0; $p < count($dados_prods); $p++) {
                $qtia = formataQuantia(isset($dados_prods[$p]['enp_quantia']) ? $dados_prods[$p]['enp_quantia'] : 0);
                $dados_prods[$p]['enp_quantia'] = $qtia['qtia'];
                $qtia = formataQuantia(isset($dados_prods[$p]['enp_conversao']) ? $dados_prods[$p]['enp_conversao'] : 0);
                $dados_prods[$p]['enp_conversao'] = $qtia['qtia'];
                $qtia = formataQuantia(isset($dados_prods[$p]['enp_qtia_conv']) ? $dados_prods[$p]['enp_qtia_conv'] : 0);
                $dados_prods[$p]['enp_qtia_conv'] = $qtia['qtia'];

                $dados_prods[$p]['enp_valor'] = floatToMoeda($dados_prods[$p]['enp_valor']);
                $dados_prods[$p]['enp_total'] = floatToMoeda($dados_prods[$p]['enp_total']);
                $entradas[$cp]['details'][$p] = $dados_prods[$p];
            }
        }
        $entra['data'] = $entradas;
        cache()->save('entra', $entra, 60000);
        // }
        // debug($compr, true);
        echo json_encode($entra);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->ent_id;
        $campos[0][1] = $this->ent_data;
        $campos[0][2] = $this->emp_id;
        $campos[0][3] = $this->dep_id;
        $campos[0][4] = $this->for_id;
        $campos[0][5] = $this->com_id;

        $this->def_campos_pro();

        $secao[1] = 'Produtos';
        $displ[1] = 'tabela';
        $campos[1][0] = [];
        $campos[1][0][count($campos[1][0])] = $this->enp_id;
        $campos[1][0][count($campos[1][0])] = $this->mar_codigo;
        $campos[1][0][count($campos[1][0])] = $this->enp_saldo;
        $campos[1][0][count($campos[1][0])] = $this->pro_id;
        $campos[1][0][count($campos[1][0])] = $this->pro_nome;
        $campos[1][0][count($campos[1][0])] = $this->und_id;
        $campos[1][0][count($campos[1][0])] = $this->mar_nome;
        $campos[1][0][count($campos[1][0])] = $this->unm_id;
        $campos[1][0][count($campos[1][0])] = $this->enp_quantia;
        $campos[1][0][count($campos[1][0])] = $this->enp_valor;
        $campos[1][0][count($campos[1][0])] = $this->enp_total;
        $campos[1][0][count($campos[1][0])] = $this->enp_conversao;
        $campos[1][0][count($campos[1][0])] = $this->bt_add;
        $campos[1][0][count($campos[1][0])] = $this->bt_del;

        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('produtos');</script>";

        echo view('vw_edicao', $this->data);
    }

    public function add_campo($ind)
    {
        $this->def_campos_pro(false, $ind);

        $campo = [];
        $campo[count($campo)] = $this->enp_id;
        $campo[count($campo)] = $this->mar_codigo;
        $campo[count($campo)] = $this->enp_saldo;
        $campo[count($campo)] = $this->pro_id;
        $campo[count($campo)] = $this->pro_nome;
        $campo[count($campo)] = $this->und_id;
        $campo[count($campo)] = $this->mar_nome;
        $campo[count($campo)] = $this->unm_id;
        $campo[count($campo)] = $this->enp_quantia;
        $campo[count($campo)] = $this->enp_valor;
        $campo[count($campo)] = $this->enp_total;
        $campo[count($campo)] = $this->enp_conversao;
        $campo[count($campo)] = $this->bt_add;
        $campo[count($campo)] = $this->bt_del;

        echo json_encode($campo);
        exit;
    }

    /**
     * Show
     * show
     *
     * @param mixed $id 
     * @return void
     */
    public function show($id)
    {
        $this->edit($id, true);
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
        $dados_ent = $this->entrada->getEntradaLista($id)[0];
        $this->def_campos($dados_ent, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->ent_id;
        $campos[0][1] = $this->ent_data;
        $campos[0][2] = $this->emp_id;
        $campos[0][3] = $this->dep_id;
        $campos[0][4] = $this->for_id;
        $campos[0][5] = $this->com_id;

        $secao[1] = 'Produtos';
        $displ[1] = 'tabela';
        $dados_enp = $this->entrada->getProdutoEntrada($id);
        // debug($dados_enp);
        if (count($dados_enp) > 0) {
            for ($c = 0; $c < count($dados_enp); $c++) {
                $this->def_campos_pro($dados_enp[$c], $c, $show);
                $campos[1][$c] = [];
                $campos[1][$c][count($campos[1][$c])] = $this->enp_id;
                $campos[1][$c][count($campos[1][$c])] = $this->mar_codigo;
                $campos[1][$c][count($campos[1][$c])] = $this->enp_saldo;
                $campos[1][$c][count($campos[1][$c])] = $this->pro_id;
                $campos[1][$c][count($campos[1][$c])] = $this->pro_nome;
                $campos[1][$c][count($campos[1][$c])] = $this->und_id;
                $campos[1][$c][count($campos[1][$c])] = $this->mar_nome;
                $campos[1][$c][count($campos[1][$c])] = $this->unm_id;
                $campos[1][$c][count($campos[1][$c])] = $this->enp_quantia;
                $campos[1][$c][count($campos[1][$c])] = $this->enp_valor;
                $campos[1][$c][count($campos[1][$c])] = $this->enp_total;
                $campos[1][$c][count($campos[1][$c])] = $this->enp_conversao;
                if ($show) {
                    $campos[1][$c][count($campos[1][$c])] = '';
                    $campos[1][$c][count($campos[1][$c])] = '';
                } else {
                    $campos[1][$c][count($campos[1][$c])] = $this->bt_add;
                    $campos[1][$c][count($campos[1][$c])] = $this->bt_del;
                }
            }
        } else {
            $this->def_campos_pro(false, 0, $show);
            $campos[1][0] = [];
            $campos[1][0][count($campos[1][0])] = $this->enp_id;
            $campos[1][0][count($campos[1][0])] = $this->mar_codigo;
            $campos[1][0][count($campos[1][0])] = $this->enp_saldo;
            $campos[1][0][count($campos[1][0])] = $this->pro_id;
            $campos[1][0][count($campos[1][0])] = $this->pro_nome;
            $campos[1][0][count($campos[1][0])] = $this->und_id;
            $campos[1][0][count($campos[1][0])] = $this->mar_nome;
            $campos[1][0][count($campos[1][0])] = $this->unm_id;
            $campos[1][0][count($campos[1][0])] = $this->enp_quantia;
            $campos[1][0][count($campos[1][0])] = $this->enp_valor;
            $campos[1][0][count($campos[1][0])] = $this->enp_total;
            $campos[1][0][count($campos[1][0])] = $this->enp_conversao;
            if ($show) {
                $campos[1][0][count($campos[1][0])] = '';
                $campos[1][0][count($campos[1][0])] = '';
            } else {
                $campos[1][0][count($campos[1][0])] = $this->bt_add;
                $campos[1][0][count($campos[1][0])] = $this->bt_del;
            }
        }

        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('produtos');</script>";

        echo view('vw_edicao', $this->data);
    }
    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        $ret = [];
        try {
            $this->entrada->delete($id);
            $cta_exc = $this->common->deleteReg('dbEstoque', 'est_entrada_produto', "ent_id = " . $id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Entrada Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Entrada, Verifique!<br>';
        }
        echo json_encode($ret);
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
        $emp->leitura      = $show;
        $this->emp_id               = $emp->crSelect();

        $depos = [];
        if ($dados) {
            $deposito       = new EstoquDepositoModel();
            $dados_dep      = $deposito->getDeposito($dados['dep_id'], $dados['emp_id']);
            $depos = array_column($dados_dep, 'dep_nome', 'dep_id');
        }

        $dep                        = new MyCampo('est_entrada', 'dep_id');
        $dep->valor = $dep->selecionado = isset($dados['dep_id']) ? $dados['dep_id'] : '';
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 50;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'emp_id';
        $dep->leitura      = $show;
        $this->dep_id               = $dep->crDepende();

        $comp               = new MyCampo('est_entrada', 'com_id');
        $comp->valor        = isset($dados['com_id']) ? $dados['com_id'] : '';
        $this->com_id       = $comp->crOculto();
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
        $id = new MyCampo('est_entrada_produto', 'enp_id');
        $id->nome = "enp_id[$pos]";
        $id->id = "enp_id[$pos]";
        $id->valor = isset($dados['enp_id']) ? $dados['enp_id'] : '';
        $id->repete            = true;
        $this->enp_id = $id->crOculto();

        $mcd                    = new MyCampo('est_entrada_produto', 'mar_codigo');
        $mcd->id = $mcd->nome   = "mar_codigo[$pos]";
        $mcd->ordem             = $pos;
        $mcd->valor             = isset($dados['mar_codigo']) ? $dados['mar_codigo'] : '';
        $mcd->funcBlur          = "buscaProdutoMarca(this); buscaSaldoProduto(this,'enp_saldo')";
        $mcd->dispForm          = "col-4";
        $mcd->leitura           = $show;
        $mcd->naocolar          = true;
        $this->mar_codigo       = $mcd->crInput();

        $pro                    = new MyCampo('est_entrada_produto', 'pro_id');
        $pro->nome              = "pro_id[$pos]";
        $pro->ordem             = $pos;
        $pro->id                = "pro_id[$pos]";
        $pro->valor             = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        $pro->repete            = true;
        $pro->leitura      = $show;
        $this->pro_id           = $pro->crOculto();

        $prn                        = new MyCampo('est_produto', 'pro_nome');
        $prn->id = $prn->nome       = "pro_nome[$pos]";
        $prn->valor                 = isset($dados['pro_nome']) ? $dados['pro_nome'] : '';
        $prn->ordem                 = $pos;
        $prn->repete                = true;
        $prn->leitura               = true;
        $prn->largura               = 40;
        $prn->dispForm              = 'col-4';
        $prn->place             = "";
        $this->pro_nome             = $prn->crInput();

        $unidades                   = new EstoquUndMedidaModel();
        $lst_unds                   = $unidades->getUndMedida();
        $opc_und                    = array_column($lst_unds, 'und_completa', 'und_id');

        $und                        = new MyCampo('est_entrada_produto', 'und_id');
        $und->id = $und->nome       = "und_id[$pos]";
        $und->label                 = 'Und Prod';
        $und->ordem                 = $pos;
        $und->repete                = true;
        $und->leitura               = true;
        $und->valor = $und->selecionado  = isset($dados['und_id']) ? $dados['und_id'] : '';
        $und->largura               = 15;
        $und->dispForm              = 'col-2';
        $und->classep               = ' text-nowrap';
        $und->opcoes                = $opc_und;
        $und->place             = "";
        $this->und_id               = $und->crSelect();

        $mar                        = new MyCampo('est_marca', 'mar_nome');
        $mar->id = $mar->nome       = "mar_nome[$pos]";
        $mar->valor                 = isset($dados['mar_nome']) ? $dados['mar_nome'] . ' - ' . $dados['mar_apresenta'] : '';
        $mar->ordem                 = $pos;
        $mar->repete                = true;
        $mar->leitura               = true;
        $mar->largura               = 30;
        $mar->dispForm              = 'col-3';
        $mar->place             = "";
        $this->mar_nome             = $mar->crInput();

        $unm                        = new MyCampo('est_entrada_produto', 'und_id');
        $unm->id = $unm->nome       = "unm_id[$pos]";
        $unm->label                 = 'Und Marca';
        $unm->ordem                 = $pos;
        $unm->repete                = true;
        $unm->leitura               = true;
        $unm->valor = $unm->selecionado  = isset($dados['unm_id']) ? $dados['unm_id'] : '';
        $unm->largura               = 15;
        $unm->dispForm              = 'col-2';
        $unm->classep               = ' text-nowrap';
        $unm->opcoes                = $opc_und;
        $unm->place             = "";
        $this->unm_id               = $unm->crSelect();


        $conv = formataQuantia(isset($dados['enp_conversao']) ? $dados['enp_conversao'] : 0);
        $con                        = new MyCampo('est_entrada_produto', 'enp_conversao');
        $con->id = $con->nome       = "conversao[$pos]";
        $con->label                 = 'Fator de Conversão';
        $con->ordem                 = $pos;
        $con->valor                 = $conv['qtiv'];
        $con->decimal               = $conv['dec'];
        $con->repete                = true;
        $con->leitura               = true;
        $con->dispForm              = 'col-4';
        $this->enp_conversao        = $con->crInput();


        $qti                        = new MyCampo('est_entrada_produto', 'enp_quantia');
        $qti->id = $qti->nome       = "enp_quantia[$pos]";
        $qti->ordem                 = $pos;
        $qti->repete                = true;
        $qti->obrigatorio           = true;
        $qti->valor                 = isset($dados['enp_quantia']) ? $dados['enp_quantia'] : '';
        $qti->largura               = 25;
        $qti->maximo                = 999999;
        $qti->funcBlur              = "calculaTotal(this,'enp_quantia', 'enp_valor', 'enp_total')";
        $qti->dispForm              = 'col-2';
        $qti->leitura      = $show;
        $qti->place             = "";
        $this->enp_quantia          = $qti->crInput();

        $sal                        = new MyCampo('est_entrada_produto', 'enp_quantia');
        $sal->id = $sal->nome       = "enp_saldo[$pos]";
        $sal->tipo                 = 'sonumero';
        $sal->label                 = 'Saldo';
        $sal->ordem                 = $pos;
        $sal->valor                 = isset($dados['enp_saldo']) ? $dados['enp_saldo'] : "";
        $sal->largura               = 15;
        $sal->dispForm              = 'col-8';
        $sal->leitura               = true;
        $sal->place             = "";
        $this->enp_saldo            = $sal->crInput();


        $val                        = new MyCampo('est_entrada_produto', 'enp_valor');
        $val->id = $val->nome       = "enp_valor[$pos]";
        $val->ordem                 = $pos;
        $val->repete                = true;
        $val->obrigatorio           = true;
        // debug($dados['enp_valor']);
        $val->valor                 = isset($dados['enp_valor']) ? $dados['enp_valor'] : '';
        // $val->largura               = 15;
        $val->funcBlur              = "calculaTotal(this,'enp_quantia', 'enp_valor', 'enp_total')";
        $val->dispForm              = 'col-3';
        $val->leitura      = $show;
        $val->place             = "";
        $this->enp_valor            = $val->crInput();

        $tot                        = new MyCampo('est_entrada_produto', 'enp_total');
        $tot->id = $tot->nome       = "enp_total[$pos]";
        $tot->ordem                 = $pos;
        $tot->repete                = true;
        $tot->leitura               = true;
        $tot->valor                 = isset($dados['enp_total']) ? $dados['enp_total'] : '';
        // $tot->largura               = 15;
        $tot->dispForm              = 'col-3';
        $tot->leitura      = $show;
        $tot->place             = "";
        $this->enp_total            = $tot->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Produto";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('" . base_url("EstEntrada/add_campo") . "','produtos',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Produto";
        $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        $del->funcChan = "exclui_campo('produtos',this)";
        $this->bt_del   = $del->crBotao();
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
        $contagem = new EstoquContagemModel();
        $ultimo   = $contagem->getUltimaContagem($dados['dep_id']);
        if ($ultimo) {
            if (date('Y-m-d', strtotime($dados['ent_data'])) < date('Y-m-d', strtotime($ultimo[0]['data_ultima_contagem']))) {
                $ret['erro'] = true;
                $ret['msg'] = 'A data de entrada não pode ser anterior a Última Contagem ' . dataDbToBr($ultimo[0]['data_ultima_contagem']) . ', Verifique!';
            }
        }
        if (!$ret['erro']) {
            $total_ent = 0;
            if (isset($dados['pro_id']) && count($dados['pro_id']) > 0) {
                foreach ($dados['pro_id'] as $key => $value) {
                    $total_ent += floatval(moedaToFloat($dados['enp_total'][$key]));
                }
            } else {
                $ret['erro'] = true;
                $ret['msg'] = 'É necessário informar pelo menos 1 Produto, Verifique!';
            }
        }
        if (!$ret['erro']) {
            foreach ($dados['pro_id'] as $key => $value) {
                $fck = str_replace(',', '.', $dados['conversao'][$key]);
                if ((float)$fck <= 0) {
                    $ret['erro'] = true;
                    $ret['msg'] = 'O Fator de Conversão do Produto ' . $dados['pro_nome'][$key] . ' Não pode zer ZERO(0), Verifique!';
                    break;
                }
            }
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
                    foreach ($dados['pro_id'] as $key => $value) {
                        // $qtiaconv = $dados['enp_quantia'][$key] * floatval($dados['enp_conversao'][$key]);
                        $valor = moedaToFloat($dados['enp_valor'][$key]);
                        $total = moedaToFloat($dados['enp_total'][$key]);
                        // debug($valor);
                        // debug($total,true);
                        $dados_pro = [
                            'ent_id'    => $ent_id,
                            'mar_codigo'    => $dados['mar_codigo'][$key],
                            'pro_id'    => $dados['pro_id'][$key],
                            'und_id'    => $dados['und_id'][$key],
                            'enp_quantia'   => $dados['enp_quantia'][$key],
                            'enp_valor'   => $valor,
                            'enp_conversao'   => str_replace(',', '.', $dados['conversao'][$key]),
                            'enp_total'   => $total,
                            'enp_atualizado' => $data_atu
                        ];
                        // if($dados['enp_id'][$key] != ''){
                        //     $salva = $this->common->updateReg('dbEstoque','est_entrada_produto','enp_id = '.$dados['enp_id'][$key],$dados_pro);
                        // } else {
                        $salva = $this->common->insertReg('dbEstoque', 'est_entrada_produto', $dados_pro);
                        // }
                        if (!$salva) {
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                            break;
                        }
                    }
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Entradas gravada com Sucesso!!!';
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
