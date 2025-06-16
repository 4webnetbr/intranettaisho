<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Controllers\Config\CfgEmpresa;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstSaida extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $saida;
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
        $this->saida = new EstoquSaidaModel();
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

        $this->data['nome']         = 'saida';
        // $this->data['colunas'] = montaColunasLista($this->data, 'sai_id','d');
        $this->data['colunas'] = montaColunasLista($this->data, 'sai_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista_prod');
        $this->data['campos']         = $campos;
        // $this->data['script'] = "<script>carrega_lista_detail('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
        $this->data['script'] = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
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
        // $emp->funcChan              = "carrega_lista_detail(this,'EstSaida/lista_prod','saida')";
        $emp->funcChan              = "carrega_lista(this,'EstSaida/lista_prod','saida')";
        $this->dash_empresa         = $emp->crSelect();
    }

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
        // $campos = montaColunasCampos($this->data, 'com_id','d');
        $campos = montaColunasCampos($this->data, 'com_id');
        // debug($campos, true);
        $dados_saida = $this->saida->getSaidaLista(false, $param);
        $sai_ids_assoc = array_column($dados_saida, 'sai_id');
        $log = buscaLogTabela('est_saida', $sai_ids_assoc);
        // $this->data['edicao'] = false;
        foreach ($dados_saida as &$sai) {
            // Verificar se o log já está disponível para esse ana_id
            $sai['sai_usuario'] = $log[$sai['sai_id']]['usua_alterou'] ?? '';
            $qtia = formataQuantia(isset($sai['sap_quantia']) ? $sai['sap_quantia'] : 0);
            $sai['sap_quantia'] = $qtia['qtia'];
            $conv = formataQuantia(isset($sai['sap_conversao']) ? $sai['sap_conversao'] : 0);
            $sai['sap_conversao'] = $conv['qtia'];
            $qcon = formataQuantia(isset($sai['sap_qtia_conv']) ? $sai['sap_qtia_conv'] : 0);
            $sai['sap_qtia_conv'] = $qcon['qtia'];
            $sai['sai_datahora'] = dataDbToBr($sai['sai_datahora']);
        }
        // for ($dc = 0; $dc < count($dados_saida); $dc++) {
        //     // $dados_saida[$dc]['d'] = '';
        //     $cta = $dados_saida[$dc];
        //     $log = buscaLog('est_saida', $cta['sai_id']);
        //     $dados_saida[$dc]['sai_usuario'] = $log['usua_alterou'];
        //     $qtia = formataQuantia(isset($dados_saida[$dc]['sap_quantia']) ? $dados_saida[$dc]['sap_quantia'] : 0);
        //     $dados_saida[$dc]['sap_quantia'] = $qtia['qtia'];
        //     $conv = formataQuantia(isset($dados_saida[$dc]['sap_conversao']) ? $dados_saida[$dc]['sap_conversao'] : 0);
        //     $dados_saida[$dc]['sap_conversao'] = $conv['qtia'];
        //     $qcon = formataQuantia(isset($dados_saida[$dc]['sap_qtia_conv']) ? $dados_saida[$dc]['sap_qtia_conv'] : 0);
        //     $dados_saida[$dc]['sap_qtia_conv'] = $qcon['qtia'];
        //     $dados_saida[$dc]['sai_datahora'] = dataDbToBr($dados_saida[$dc]['sai_datahora']);
        // }

        $saida = montaListaColunas($this->data, 'sai_id', $dados_saida, $campos[1], false);
        $saidas['data'] = $saida;
        cache()->save('cont', $saidas, 60000);
        // }
        // debug($saidas, true);
        echo json_encode($saidas);
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
        $campos[0][0] = $this->sai_id;
        $campos[0][1] = $this->sai_data;
        $campos[0][2] = $this->emp_id;
        $campos[0][3] = $this->dep_id;

        $this->def_campos_pro();

        $secao[1] = 'Produtos';
        $displ[1] = 'tabela';
        $campos[1][0][0] = $this->sap_id;
        $campos[1][0][1] = $this->mar_codigo;
        $campos[1][0][2] = $this->sap_saldo;
        $campos[1][0][3] = $this->pro_id;
        $campos[1][0][4] = $this->pro_nome;
        $campos[1][0][5] = $this->und_id;
        $campos[1][0][6] = $this->mar_nome;
        $campos[1][0][7] = $this->unm_id;
        $campos[1][0][8] = $this->sap_quantia;
        $campos[1][0][9] = $this->sap_destino;
        $campos[1][0][10] = $this->sap_conversao;
        $campos[1][0][11] = $this->bt_add;
        $campos[1][0][12] = $this->bt_del;

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
        $campo[count($campo)] = $this->sap_id;
        $campo[count($campo)] = $this->mar_codigo;
        $campo[count($campo)] = $this->sap_saldo;
        $campo[count($campo)] = $this->pro_id;
        $campo[count($campo)] = $this->pro_nome;
        $campo[count($campo)] = $this->und_id;
        $campo[count($campo)] = $this->mar_nome;
        $campo[count($campo)] = $this->unm_id;
        $campo[count($campo)] = $this->sap_quantia;
        $campo[count($campo)] = $this->sap_destino;
        $campo[count($campo)] = $this->sap_conversao;
        $campo[count($campo)] = $this->bt_add;
        $campo[count($campo)] = $this->bt_del;

        echo json_encode($campo);
        exit;
    }

    /**
     * show
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
        $dados_ent = $this->saida->getSaidaLista([$id])[0];
        $this->def_campos($dados_ent, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->sai_id;
        $campos[0][1] = $this->sai_data;
        $campos[0][2] = $this->emp_id;
        $campos[0][3] = $this->dep_id;

        $secao[1] = 'Produtos';
        $displ[1] = 'tabela';
        $dados_enp = $this->saida->getProdutoSaida($id);
        // debug($dados_enp);
        if (count($dados_enp) > 0) {
            for ($c = 0; $c < count($dados_enp); $c++) {
                $this->def_campos_pro($dados_enp[$c], $c, $show);
                $campos[1][$c] = [];
                $campos[1][$c][count($campos[1][$c])] = $this->sap_id;
                $campos[1][$c][count($campos[1][$c])] = $this->mar_codigo;
                $campos[1][$c][count($campos[1][$c])] = $this->sap_saldo;
                $campos[1][$c][count($campos[1][$c])] = $this->pro_id;
                $campos[1][$c][count($campos[1][$c])] = $this->pro_nome;
                $campos[1][$c][count($campos[1][$c])] = $this->und_id;
                $campos[1][$c][count($campos[1][$c])] = $this->mar_nome;
                $campos[1][$c][count($campos[1][$c])] = $this->unm_id;
                $campos[1][$c][count($campos[1][$c])] = $this->sap_quantia;
                $campos[1][$c][count($campos[1][$c])] = $this->sap_destino;
                $campos[1][$c][count($campos[1][$c])] = $this->sap_conversao;
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
            $campos[1][0][count($campos[1][0])] = $this->sap_id;
            $campos[1][0][count($campos[1][0])] = $this->mar_codigo;
            $campos[1][0][count($campos[1][0])] = $this->sap_saldo;
            $campos[1][0][count($campos[1][0])] = $this->pro_id;
            $campos[1][0][count($campos[1][0])] = $this->pro_nome;
            $campos[1][0][count($campos[1][0])] = $this->und_id;
            $campos[1][0][count($campos[1][0])] = $this->mar_nome;
            $campos[1][0][count($campos[1][0])] = $this->unm_id;
            $campos[1][0][count($campos[1][0])] = $this->sap_quantia;
            $campos[1][0][count($campos[1][0])] = $this->sap_destino;
            $campos[1][0][count($campos[1][0])] = $this->sap_conversao;
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
            $this->saida->delete($id);
            $cta_exc = $this->common->deleteReg('dbEstoque', 'est_saida_produto', "sai_id = " . $id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Saída Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Saída, Verifique!<br>';
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
        $fornec = new EstoquFornecedorModel();
        $lst_fornec = $fornec->getFornecedor();
        $opc_fornec = array_column($lst_fornec, 'for_fantasia', 'for_id');

        $id             = new MyCampo('est_saida', 'sai_id');
        $id->valor      = isset($dados['sai_id']) ? $dados['sai_id'] : '';
        $this->sai_id   = $id->crOculto();

        $data               = new MyCampo('est_saida', 'sai_data');
        $data->obrigatorio  = true;
        $data->valor        = isset($dados['sai_data']) ? $dados['sai_data'] : date('Y-m-d');
        $data->largura      = 20;
        $data->leitura      = true;
        $this->sai_data     = $data->crInput();

        $empresas           = explode(',', session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('est_saida', 'emp_id');
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

        $dep                        = new MyCampo('est_saida', 'dep_id');
        $dep->valor = $dep->selecionado = isset($dados['dep_id']) ? $dados['dep_id'] : '';
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 50;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'emp_id';
        $dep->leitura      = $show;
        $this->dep_id               = $dep->crDepende();
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
        $id = new MyCampo('est_saida_produto', 'sap_id');
        $id->nome = "sap_id[$pos]";
        $id->id = "sap_id[$pos]";
        $id->valor = isset($dados['sap_id']) ? $dados['sap_id'] : '';
        $id->repete            = true;
        $this->sap_id = $id->crOculto();


        $mcd                    = new MyCampo('est_saida_produto', 'mar_codigo');
        $mcd->id = $mcd->nome   = "mar_codigo[$pos]";
        $mcd->ordem             = $pos;
        $mcd->valor             = isset($dados['mar_codigo']) ? $dados['mar_codigo'] : '';
        $mcd->funcBlur          = 'buscaProdutoMarca(this), buscaSaldoProduto(this)';
        $mcd->leitura           = $show;
        $mcd->dispForm          = 'col-4';
        $mcd->naocolar          = true;
        $this->mar_codigo       = $mcd->crInput();

        $pro                    = new MyCampo('est_saida_produto', 'pro_id');
        $pro->nome              = "pro_id[$pos]";
        $pro->ordem             = $pos;
        $pro->id                = "pro_id[$pos]";
        $pro->valor             = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        $pro->repete            = true;
        $pro->leitura           = $show;
        $pro->place             = "";
        $this->pro_id           = $pro->crOculto();

        $prn                        = new MyCampo('est_produto', 'pro_nome');
        $prn->id = $prn->nome       = "pro_nome[$pos]";
        $prn->valor                 = isset($dados['pro_nome']) ? $dados['pro_nome'] : '';
        $prn->ordem                 = $pos;
        $prn->repete                = true;
        $prn->leitura               = true;
        $prn->largura               = 40;
        $prn->dispForm              = 'col-4';
        $prn->place                 = "";
        $this->pro_nome               = $prn->crInput();

        $unidades                   = new EstoquUndMedidaModel();
        $lst_unds                   = $unidades->getUndMedida();
        $opc_und                    = array_column($lst_unds, 'und_sigla', 'und_id');

        $und                        = new MyCampo('est_saida_produto', 'und_id');
        $und->id = $und->nome       = "und_id[$pos]";
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

        $unm                        = new MyCampo('est_saida_produto', 'und_id');
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

        $conv = 0;
        $decimal = 0;
        if (isset($dados['sap_conversao'])) {
            // debug(floatval($dados['ctp_quantia']));
            // debug(intval($dados['ctp_quantia']));
            if (fmod($dados['sap_conversao'], 1) == 0) {
                $conv = intval($dados['sap_conversao']);
                $decimal = 0;
            } else {
                $conv = "<div class='text-end'>" . number_format($dados['sap_conversao'], 3, ',', '') . "</div>";
                $decimal = 1;
            }
        }
        $conv = formataQuantia(isset($dados['sap_conversao']) ? $dados['sap_conversao'] : 0);
        $con                        = new MyCampo('est_saida_produto', 'sap_conversao');
        $con->id = $con->nome       = "conversao[$pos]";
        $con->label                 = 'Fator de Conversão';
        $con->ordem                 = $pos;
        $con->valor                 = $conv['qtiv'];
        $con->decimal               = $conv['dec'];
        $con->repete                = true;
        $con->leitura                = true;
        $con->dispForm              = 'col-2';
        $con->place             = "";
        $this->sap_conversao        = $con->crInput();


        $sal                        = new MyCampo('est_saida_produto', 'sap_quantia');
        $sal->id = $sal->nome       = "sap_saldo[$pos]";
        $sal->tipo                 = 'sonumero';
        $sal->label                 = 'Saldo';
        $sal->ordem                 = $pos;
        $sal->valor                 = isset($dados['sap_saldo']) ? $dados['sap_saldo'] : "";
        $sal->largura               = 15;
        $sal->dispForm              = 'col-8';
        $sal->leitura               = true;
        $sal->place             = "";
        $this->sap_saldo            = $sal->crInput();

        $qti                        = new MyCampo('est_saida_produto', 'sap_quantia');
        $qti->id = $qti->nome       = "sap_quantia[$pos]";
        $qti->ordem                 = $pos;
        $qti->repete                = true;
        $qti->obrigatorio           = true;
        $qti->valor                 = isset($dados['sap_quantia']) ? $dados['sap_quantia'] : "0";
        $qti->largura               = 25;
        $qti->dispForm              = 'col-2';
        $qti->leitura               = $show;
        $qti->place             = "";
        $this->sap_quantia          = $qti->crInput();

        $des                        = new MyCampo('est_saida_produto', 'sap_destino');
        $des->id = $des->nome       = "sap_destino[$pos]";
        $des->ordem                 = $pos;
        $des->repete                = true;
        $des->obrigatorio           = true;
        $des->valor                 = isset($dados['sap_destino']) ? $dados['sap_destino'] : '';
        $des->largura               = 30;
        $des->dispForm              = 'col-5';
        $des->leitura      = $show;
        $this->sap_destino            = $des->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Produto";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('" . base_url("EstSaida/add_campo") . "','produtos',this)";
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
        // debug($dados, true);
        if ($ultimo) {
            if (date('Y-m-d', strtotime($dados['sai_data'])) < date('Y-m-d', strtotime($ultimo[0]['data_ultima_contagem']))) {
                $ret['erro'] = true;
                $ret['msg'] = 'A data de saída não pode ser anterior a Última Contagem ' . dataDbToBr($ultimo[0]['data_ultima_contagem']) . ', Verifique!';
            }
        }
        if (!$ret['erro']) {
            if (isset($dados['pro_id']) && count($dados['pro_id']) > 0) {
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
                'sai_id'    => $dados['sai_id'],
                'sai_data'  => $dados['sai_data'],
                'emp_id'    => $dados['emp_id'],
                'dep_id'    => $dados['dep_id'],
            ];
            if ($this->saida->save($dados_cta)) {
                $sai_id = $this->saida->getInsertID();
                if ($dados['sai_id'] != '') {
                    $sai_id = $dados['sai_id'];
                }
                if (isset($dados['pro_id'])) {
                    $data_atu = date('Y-m-d H:i:s');
                    foreach ($dados['pro_id'] as $key => $value) {
                        $dados_pro = [
                            'sai_id'    => $sai_id,
                            'mar_codigo'    => $dados['mar_codigo'][$key],
                            'pro_id'    => $dados['pro_id'][$key],
                            'und_id'    => $dados['und_id'][$key],
                            'sap_conversao'   => str_replace(',', '.', $dados['conversao'][$key]),
                            'sap_quantia'   => $dados['sap_quantia'][$key],
                            'sap_destino'   => $dados['sap_destino'][$key],
                            'sap_atualizado' => $data_atu
                        ];
                        // debug('Sap Id '.$dados['sap_id'][$key]);
                        // debug($dados_pro);
                        // if($dados['sap_id'][$key] != ''){
                        //     $salva = $this->common->updateReg('dbEstoque','est_saida_produto','sap_id = '.$dados['sap_id'][$key],$dados_pro);
                        // } else {
                        $salva = $this->common->insertReg('dbEstoque', 'est_saida_produto', $dados_pro);
                        // }
                        if (!$salva) {
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                            break;
                        }
                    }
                    $cta_exc = $this->common->deleteReg('dbEstoque', 'est_saida_produto', "sai_id = " . $sai_id . " AND sap_atualizado != '" . $data_atu . "'");
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Saidas gravada com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            } else {
                $erros = $this->saida->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar a Saida de Produto, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }
        echo json_encode($ret);
    }
}
