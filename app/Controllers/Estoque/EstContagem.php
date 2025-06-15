<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquConsumoModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstContagem extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $contagem;
    public $consumo;
    public $deposito;
    public $produto;
    public $marca;
    public $unidades;
    public $common;
    public $cta_id;
    public $cta_data;
    public $emp_id;
    public $dep_id;
    public $ctp_id;
    public $pro_id;
    public $und_id;
    public $ctp_quantia;
    public $bt_add;
    public $bt_del;

    public $ctp_saldo;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->contagem = new EstoquContagemModel();
        $this->deposito = new EstoquDepositoModel();
        $this->produto  = new EstoquProdutoModel();
        $this->marca  = new EstoquMarcaModel();
        $this->unidades  = new EstoquUndMedidaModel();
        $this->consumo  = new EstoquConsumoModel();
        $this->common  = new CommonModel();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'cta_id', 'd');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista_prod');
        echo view('vw_lista_details', $this->data);

        // $this->data['colunas'] = montaColunasLista($this->data, 'cta_id');
        // $this->data['url_lista'] = base_url($this->data['controler'] . '/lista_prod');
        // echo view('vw_lista_details', $this->data);
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // if (!$contag = cache('contag')) {
        $campos = montaColunasCampos($this->data, 'cta_id');
        $dados_contag = $this->contagem->getContagemLista();
        $cta_ids_assoc = array_column($dados_contag, 'cta_id');
        $log = buscaLogTabela('est_contagem', $cta_ids_assoc);
        // $this->data['edicao'] = false;
        foreach ($dados_contag as &$cta) {
            // Verificar se o log já está disponível para esse ana_id
            $cta['cta_usuario'] = $log[$cta['cta_id']]['usua_alterou'] ?? '';

            // for ($i=0; $i < count($dados_contag); $i++) {
            // $cta = $dados_contag[$i];
            // $log = buscaLog('est_contagem', $cta['cta_id']);
            // $dados_contag[$i]['cta_usuario'] = $log['usua_alterou'];
        }
        // $this->data['exclusao'] = false;
        $contag = [
            'data' => montaListaColunas($this->data, 'cta_id', $dados_contag, $campos[1]),
        ];
        cache()->save('contag', $contag, 60000);
        // }

        echo json_encode($contag);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista_prod()
    {
        // if (!$compr = cache('compr')) {
        $campos = montaColunasCampos($this->data, 'com_id', 'd');
        // debug($campos, true);
        $dados_contag = $this->contagem->getContagemLista();
        $cta_ids_assoc = array_column($dados_contag, 'cta_id');
        $log = buscaLogTabela('est_contagem', $cta_ids_assoc);
        // $this->data['edicao'] = false;
        foreach ($dados_contag as &$cta) {
            // Verificar se o log já está disponível para esse ana_id
            $cta['cta_usuario'] = $log[$cta['cta_id']]['usua_alterou'] ?? '';
            $cta['d'] = '';
        }

        $contag = montaListaColunas($this->data, 'cta_id', $dados_contag, $campos[1], true);
        for ($cp = 0; $cp < count($contag); $cp++) {
            $cont = $contag[$cp];
            $contag[$cp]['col_details'] = [
                'tit' => ['Produto', 'Qtia', 'Und', 'Data'],
                'tam' => ['col-5', 'col-2', 'col-1', 'col-2'],
                'cam' => ['pro_nome', 'ctp_quantia', 'und_sigla', 'cta_datahora'],
            ];
            $dados_prods = $this->contagem->getContagemProd($cont[0]);
            for ($p = 0; $p < count($dados_prods); $p++) {
                $qtia = formataQuantia(isset($dados_prods[$p]['ctp_quantia']) ? $dados_prods[$p]['ctp_quantia'] : 0);
                $dados_prods[$p]['ctp_quantia'] = $qtia['qtia'];
                $dados_prods[$p]['cta_datahora'] = dataDbToBr($dados_prods[$p]['cta_datahora']);
                $contag[$cp]['details'][$p] = $dados_prods[$p];
            }
        }
        $contagem['data'] = $contag;
        cache()->save('cont', $contagem, 60000);
        // }
        // debug($compr, true);
        echo json_encode($contagem);
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

        $secao[0] = 'Informe as Contagens de Produtos';
        $campos[0] = $this->cta_id;
        $campos[1] = $this->emp_id;
        $campos[2] = $this->dep_id;

        // $this->def_campos_cont();
        $this->data['nome']         = 'contagem';
        $this->data['colunas']      = ['Id', 'Grupo', 'Produto', 'Saldo', 'Und', 'Marca', 'Apresentação', 'Quantia'];
        $this->data['url_lista']    = base_url($this->data['controler'] . '/listaadd');
        $this->data['secao']        = $secao;
        $this->data['campos']       = $campos;
        $this->data['destino']      = "";
        // $this->data['script']       = "<script>carrega_lista_edit('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }
    public function listaadd()
    {
        $empresa = $_REQUEST['empresa'];
        $deposito = $_REQUEST['deposito'];
        $camps = 'pro.pro_id, pro.pro_nome, pro.gru_nome,pro.und_nome, pro.und_id, sld.saldo';
        $produtos   =  $this->contagem->getProdutosMarcas($empresa, $deposito);
        // debug($produtos);

        $campos[0] = 'pro_id';
        $campos[count($campos)] = 'gru_nome';
        $campos[count($campos)] = 'pro_nome';
        $campos[count($campos)] = 'saldo';
        $campos[count($campos)] = 'und_nome';
        $campos[count($campos)] = 'mar_nome';
        $campos[count($campos)] = 'mar_apresenta';
        $campos[count($campos)] = 'ctp_quantia';
        // $campos[count($campos)] = 'acao';

        $dados_contag = [];
        for ($dc = 0; $dc < count($produtos); $dc++) {
            $prod = $produtos[$dc];
            $saldo   = $prod['saldo'];

            $dados_contag[$dc]['ctp_id']        = "";
            $dados_contag[$dc]['pro_id']        = $prod['pro_id'];
            $dados_contag[$dc]['gru_nome']      = $prod['gru_nome'];
            $dados_contag[$dc]['pro_nome']      = $prod['pro_nome'];
            $dados_contag[$dc]['saldo']         = formataQuantia($saldo, 3)['qtia'];
            $dados_contag[$dc]['und_id']        = $prod['und_id'];
            $dados_contag[$dc]['und_nome']      = $prod['und_nome'];
            $dados_contag[$dc]['mar_nome'] = !is_null($prod['mar_nome'])
                ? $prod['mar_nome'] . '<br> Cõd. ' . $prod['mar_codigo']
                : null;
            // $dados_contag[$dc]['mar_nome']      = $prod['mar_nome'] . '<br> Cõd. ' . $prod['mar_codigo'];
            $dados_contag[$dc]['mar_conversao']      = $prod['mar_conversao'];
            $dados_contag[$dc]['mar_apresenta']      = $prod['mar_apresenta'];
            $dados_contag[$dc]['ctp_quantia']   = $saldo;
            $dados_contag[$dc]['ctp_saldo']     = $saldo;
            $dados_contag[$dc]['acao']     = '';
            $this->def_campos_cont($dados_contag[$dc], $dc);
            $dados_contag[$dc]['ctp_quantia'] = $this->ctp_id . ' ' . $this->pro_id . ' ' . $this->und_id . ' ' . $this->ctp_quantia . ' ' . $this->ctp_fck . ' ' . $this->ctp_saldo;
        }
        $pedid = [
            'data' => montaListaEditColunas($campos, 'pro_id', $dados_contag, $campos[1]),
        ];
        // debug($pedid, true);
        // cache()->save('pedid', $pedid, 60000);
        // }

        echo json_encode($pedid);
    }


    public function add_campo($ind)
    {
        $this->def_campos_cont(false, $ind);

        $campo = [];
        $campo[count($campo)] = $this->ctp_id;
        $campo[count($campo)] = $this->pro_id;
        $campo[count($campo)] = $this->und_id;
        $campo[count($campo)] = $this->ctp_quantia;
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
        $dados_cta = $this->contagem->getContagemLista($id)[0];
        $this->def_campos($dados_cta, true);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->cta_id;
        // $campos[0][1] = $this->cta_data;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->dep_id;

        $secao[1] = 'Contagem';
        $displ[1] = 'tabela';
        $dados_ctp = $this->contagem->getContagem($id);
        // debug($dados_ctp);
        if (count($dados_ctp) > 0) {
            for ($c = 0; $c < count($dados_ctp); $c++) {
                $this->def_campos_cont_edit($dados_ctp[$c], $c, $show);
                $campos[1][$c] = [];
                $campos[1][$c][count($campos[1][$c])] = $this->ctp_id;
                $campos[1][$c][count($campos[1][$c])] = $this->pro_id;
                $campos[1][$c][count($campos[1][$c])] = $this->und_id;
                $campos[1][$c][count($campos[1][$c])] = $this->ctp_quantia;
                if ($show) {
                    $campos[1][$c][count($campos[1][$c])] = '';
                    $campos[1][$c][count($campos[1][$c])] = '';
                } else {
                    $campos[1][$c][count($campos[1][$c])] = $this->bt_add;
                    $campos[1][$c][count($campos[1][$c])] = $this->bt_del;
                }
            }
        } else {
            $this->def_campos_cont(false, 0, $show);
            $campos[1][0] = [];
            $campos[1][0][count($campos[1][0])] = $this->ctp_id;
            $campos[1][0][count($campos[1][0])] = $this->pro_id;
            $campos[1][0][count($campos[1][0])] = $this->und_id;
            $campos[1][0][count($campos[1][0])] = $this->ctp_quantia;
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
        $this->data['script'] = "<script>acerta_botoes_rep('contagem');</script>";

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
            $this->contagem->delete($id);
            $cta_exc = $this->common->deleteReg('dbEstoque', 'est_contagem_produto', "cta_id = " . $id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Contagem Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Contagem, Verifique!<br>';
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
        $id = new MyCampo('est_contagem', 'cta_id');
        $id->valor = isset($dados['cta_id']) ? $dados['cta_id'] : '';
        $this->cta_id = $id->crOculto();

        $dat                        = new MyCampo('est_contagem', 'cta_data');
        $dat->valor           = isset($dados['cta_data']) ? $dados['cta_data'] : date('Y-m-d');
        $dat->largura               = 20;
        $dat->leitura               = $show;
        $this->cta_data             = $dat->crInput();

        $empresas           = explode(',', session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('est_contagem', 'emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id']) ? $dados['emp_id'] : $empresas[0];
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $emp->dispForm              = "col-5";
        $this->emp_id               = $emp->crSelect();

        $depos = [];
        if ($dados) {
            $dados_dep = $this->deposito->getDeposito($dados['dep_id'], $dados['emp_id']);
            $depos = array_column($dados_dep, 'dep_nome', 'dep_id');
        }

        $dep                        = new MyCampo('est_contagem', 'dep_id');
        $dep->valor = $dep->selecionado = isset($dados['dep_id']) ? $dados['dep_id'] : '-1';
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 50;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'emp_id';
        $dep->dispForm              = "col-5";
        $dep->leitura               = $show;
        // $dep->funcChan              = "carregaContagem(this, '" . base_url($this->data['controler'] . '/listaadd') . "','contagem');";
        $dep->funcChan              = "carrega_lista_2param(this, '" . base_url($this->data['controler'] . '/listaadd') . "','contagem');";
        $this->dep_id               = $dep->crDepende();
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados
     * @return void
     */
    public function def_campos_cont($dados = false, $pos = 0, $marca = false)
    {
        $ctpid                     = new MyCampo('est_contagem_produto', 'ctp_id');
        $ctpid->nome               = "ctp_id[$pos]";
        $ctpid->ordem              = $pos;
        $ctpid->valor              = isset($dados['ctp_id']) ? $dados['ctp_id'] : '';
        $this->ctp_id              = $ctpid->crOculto();

        $proid                     = new MyCampo('est_contagem_produto', 'pro_id');
        $proid->nome               = "pro_id[$pos]";
        $proid->ordem              = $pos;
        $proid->valor              = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        $this->pro_id              = $proid->crOculto();

        $undid                     = new MyCampo('est_contagem_produto', 'und_id');
        $undid->nome               = "und_id[$pos]";
        $undid->ordem              = $pos;
        $undid->valor              = isset($dados['und_id']) ? $dados['und_id'] : '';
        $this->und_id              = $undid->crOculto();

        $qtia = formataQuantia(isset($dados['ctp_quantia']) ? $dados['ctp_quantia'] : 0);

        $qti                       = new MyCampo('est_contagem_produto', 'ctp_quantia');
        $qti->id = $qti->nome      = "ctp_quantia[$pos]";
        $qti->tipo                 = "quantia";
        $qti->label                = "";
        $qti->largura              = 15;
        $qti->ordem                = $pos;
        $qti->valor                = 0;
        $qti->decimal              = 0;
        $qti->classep             = "pr" . $dados['pro_id'];
        $qti->funcBlur              =  'gravaContagem(this)';
        $this->ctp_quantia          = $qti->crInput();

        $fck                       = new MyCampo('est_contagem_produto', 'ctp_quantia');
        $fck->id = $fck->nome      = "ctp_fck[$pos]";
        $fck->ordem                = $pos;
        $fck->valor                = $dados['mar_conversao'] ?? 1;
        $this->ctp_fck             = $fck->crOculto();

        $sald = formataQuantia(isset($dados['ctp_saldo']) ? $dados['ctp_saldo'] : 0);

        $sal                       = new MyCampo('est_contagem_produto', 'ctp_quantia');
        $sal->id = $sal->nome      = "ctp_saldo[$pos]";
        $sal->ordem                = $pos;
        $sal->valor                = $sald['qtiv'];
        $this->ctp_saldo            = $sal->crOculto();
    }
    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados
     * @return void
     */
    public function def_campos_cont_edit($dados = false, $pos = 0, $show = false)
    {
        $ctpid                     = new MyCampo('est_contagem_produto', 'ctp_id');
        $ctpid->nome               = "ctp_id[$pos]";
        $ctpid->ordem              = $pos;
        $ctpid->valor              = isset($dados['ctp_id']) ? $dados['ctp_id'] : '';
        $this->ctp_id              = $ctpid->crOculto();

        $prods = [];
        if ($dados) {
            $dados_pro = $this->produto->getProduto($dados['pro_id']);
            $prods = array_column($dados_pro, 'pro_nome', 'pro_id');
        }

        $pro                        = new MyCampo('est_contagem_produto', 'pro_id');
        $pro->id = $pro->nome       = "pro_id[$pos]";
        $pro->valor = $pro->selecionado = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        $pro->ordem              = $pos;
        $pro->opcoes                = $prods;
        $pro->largura               = 40;
        $pro->obrigatorio           = true;
        $pro->urlbusca              = base_url('buscas/busca_produto');
        $pro->dispForm              = 'col-6';
        $pro->leitura               = true;
        $this->pro_id               = $pro->crSelbusca();

        $dados_und = $this->unidades->getUndMedida();
        $unids = array_column($dados_und, 'und_nome', 'und_id');

        $und                        = new MyCampo('est_contagem_produto', 'und_id');
        $und->id = $und->nome      = "und_id[$pos]";
        $und->ordem              = $pos;
        $und->valor = $und->selecionado  = isset($dados['und_id']) ? $dados['und_id'] : '';
        $und->opcoes                = $unids;
        $und->largura               = 25;
        $und->dispForm              = 'col-3';
        $und->leitura               = true;
        $this->und_id               = $und->crSelect();

        $qtia = formataQuantia(isset($dados['ctp_quantia']) ? $dados['ctp_quantia'] : 0);

        $qti                       = new MyCampo('est_contagem_produto', 'ctp_quantia');
        $qti->id = $qti->nome      = "ctp_quantia[$pos]";
        $qti->tipo                 = "quantia";
        $qti->largura              = 15;
        $qti->ordem                = $pos;
        $qti->valor                = $qtia['qtiv'];
        $qti->decimal              = $qtia['dec'];
        $qti->funcBlur              =  'gravaContagem(this)';
        $qti->dispForm              = 'col-2';
        $qti->leitura               = $show;
        $this->ctp_quantia          = $qti->crInput();

        $sald = formataQuantia(isset($dados['ctp_saldo']) ? $dados['ctp_saldo'] : 0);

        $sal                       = new MyCampo('est_contagem_produto', 'ctp_quantia');
        $sal->id = $sal->nome      = "ctp_saldo[$pos]";
        $sal->ordem                = $pos;
        $sal->valor                = $sald['qtiv'];
        $this->ctp_saldo            = $sal->crOculto();

        // $atrib['data-index'] = $pos;
        // $add            = new MyCampo();
        // $add->attrdata  = $atrib;
        // $add->nome      = "bt_add[$pos]";
        // $add->id        = "bt_add[$pos]";
        // $add->i_cone    = "<i class='fas fa-plus'></i>";
        // $add->place     = "Adicionar Contagem";
        // $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        // $add->funcChan = "addCampo('" . base_url("EstContagem/add_campo") . "','contagem',this)";
        // $this->bt_add   = $add->crBotao();

        // $del            = new MyCampo();
        // $del->attrdata  = $atrib;
        // $del->nome      = "bt_del[$pos]";
        // $del->id        = "bt_del[$pos]";
        // $del->i_cone     = "<i class='fas fa-trash'></i>";
        // $del->place     = "Excluir Contagem";
        // $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        // $del->funcChan = "exclui_campo('contagem',this)";
        // $this->bt_del   = $del->crBotao();
    }

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function storecont()
    {
        $ret = [];
        $ret['erro'] = false;
        $erros = [];

        $dados = $this->request->getPost();
        // debug($dados, true);
        $dados_cta = [
            'cta_id'    => $dados['cta_id'],
            'cta_data'  => date('Y-m-d'),
            'emp_id'    => $dados['emp_id'],
            'dep_id'    => $dados['dep_id'],
        ];
        if ($this->contagem->save($dados_cta)) {
            if ($dados['cta_id'] != '') {
                $cta_id = $dados['cta_id'];
            } else {
                $cta_id = $this->contagem->getInsertID();
            }

            $data_atu = date('Y-m-d H:i:s');
            $qtia = str_replace(",", ".", $dados['ctp_quantia']);
            $dados_pro = [
                'ctp_id'    => $dados['ctp_id'],
                'cta_id'    => $cta_id,
                'pro_id'    => $dados['pro_id'],
                'und_id'    => $dados['und_id'],
                'ctp_quantia'   => $qtia,
                'ctp_atualizado' => $data_atu
            ];
            // debug($dados_pro, true);
            if ($dados['ctp_id'] != '') {
                $salva = $this->common->updateReg('dbEstoque', 'est_contagem_produto', 'ctp_id = ' . $dados['ctp_id'], $dados_pro);
                $idctp = $dados['ctp_id'];
            } else {
                $salva = $this->common->insertReg('dbEstoque', 'est_contagem_produto', $dados_pro);
                $idctp = $salva;
            }
            if (!$salva) {
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
            } else {
                $ret['erro'] = false;
                $ret['msg'] = 'Contagem gravada com Sucesso!!!';
                $ret['idcta'] = $cta_id;
                $ret['idctp'] = $idctp;
            }
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
        }
        echo json_encode($ret);
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
        $dados_cta = [
            'cta_id'    => $dados['cta_id'],
            'cta_data'  => date('Y-m-d'),
            'emp_id'    => $dados['emp_id'],
            'dep_id'    => $dados['dep_id'],
        ];
        if ($this->contagem->save($dados_cta)) {
            $cta_id = $this->contagem->getInsertID();
            $data_atu = date('Y-m-d H:i:s');
            if ($dados['cta_id'] != '') {
                $cta_id = $dados['cta_id'];
            } else {
                $entrada        = new EstoquEntradaModel();
                $ultimo_entrada = $entrada->getUltimoId()[0];
                $saida          = new EstoquSaidaModel();
                $ultimo_saida = $saida->getUltimoId()[0];
                $dados_cont = [
                    'dep_id'                => $dados['dep_id'],
                    'data_ultima_contagem'  => $data_atu,
                    'ultimo_id_entrada'     => $ultimo_entrada,
                    'ultimo_id_saida'       => $ultimo_saida,
                ];
                $salva = $this->common->insertReg('dbEstoque', 'est_controle_contagem', $dados_cont);
            }
            if (isset($dados['pro_id'])) {
                $data_atu = date('Y-m-d H:i:s');
                foreach ($dados['pro_id'] as $key => $value) {
                    $qtia = str_replace(",", ".", $dados['ctp_quantia'][$key]);
                    $dados_pro = [
                        'ctp_id'    => $dados['ctp_id'][$key],
                        'cta_id'    => $cta_id,
                        'pro_id'    => $dados['pro_id'][$key],
                        'und_id'    => $dados['und_id'][$key],
                        'ctp_quantia'   => $qtia,
                        'ctp_atualizado' => $data_atu
                    ];
                    // debug($dados_pro, true);
                    if ($dados['ctp_id'][$key] != '') {
                        $salva = $this->common->updateReg('dbEstoque', 'est_contagem_produto', 'ctp_id = ' . $dados['ctp_id'][$key], $dados_pro);
                    } else {
                        $salva = $this->common->insertReg('dbEstoque', 'est_contagem_produto', $dados_pro);
                    }
                    if (!$salva) {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                        break;
                    }
                }
                $cta_exc = $this->common->deleteReg('dbEstoque', 'est_contagem_produto', "cta_id = " . $cta_id . " AND ctp_atualizado != '" . $data_atu . "'");
            }
            $ret['erro'] = false;
            $ret['msg'] = 'Contagem gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $erros = $this->contagem->errors();
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Contagem de Produto, Verifique!';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        }
        echo json_encode($ret);
    }
}
