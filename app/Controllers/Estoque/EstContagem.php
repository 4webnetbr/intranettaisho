<?php
namespace App\Controllers\Estoque;
use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstContagem extends BaseController {
    public $data = [];
    public $permissao = '';
    public $contagem;
    public $deposito;
    public $produto;
    public $unidades;
    public $common;

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
        $this->unidades  = new EstoquUndMedidaModel();
        $this->common  = new CommonModel();
        
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
        $this->data['colunas'] = montaColunasLista($this->data, 'cta_id','d');
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
            for ($i=0; $i < count($dados_contag); $i++) { 
                $cta = $dados_contag[$i];
                $log = buscaLog('est_contagem', $cta['cta_id']);
                $dados_contag[$i]['cta_usuario'] = $log['usua_alterou'];
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
            $campos = montaColunasCampos($this->data, 'com_id','d');
            // debug($campos, true);
            $dados_contag = $this->contagem->getContagemLista();
            for ($dc=0; $dc < count($dados_contag) ; $dc++) { 
                $dados_contag[$dc]['d'] = '';
                $cta = $dados_contag[$dc];
                $log = buscaLog('est_contagem', $cta['cta_id']);
                $dados_contag[$dc]['cta_usuario'] = $log['usua_alterou'];
            }

            $contag = montaListaColunas($this->data, 'cta_id', $dados_contag, $campos[1], true);
            for ($cp=0; $cp < count($contag) ; $cp++) { 
                $cont =$contag[$cp];
                $contag[$cp]['col_details'] =[
                    'tit' => ['Produto','Qtia','Und','Data'],
                    'tam' => ['col-5','col-2','col-1','col-2'],
                    'cam' => ['pro_nome','ctp_quantia','und_sigla','cta_datahora'],
                ];
                $dados_prods = $this->contagem->getContagemProd($cont[0]);
                for ($p=0; $p < count($dados_prods) ; $p++) { 
                    $qtia = formataQuantia(isset($dados_prods[$p]['ctp_quantia'])?$dados_prods[$p]['ctp_quantia']:0);
                    $dados_prods[$p]['ctp_quantia'] = $qtia['qtia'];

                    // if(fmod($dados_prods[$p]['ctp_quantia'], 1) > 0){
                    //     $dados_prods[$p]['ctp_quantia'] = "<div class='text-end'>".number_format($dados_prods[$p]['ctp_quantia'],3,',','')."</div>";
                    // } else {
                    //     $dados_prods[$p]['ctp_quantia'] = intval($dados_prods[$p]['ctp_quantia']);
                    // }
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

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->cta_id;
        $campos[0][1] = $this->cta_data;
        $campos[0][2] = $this->emp_id;
        $campos[0][3] = $this->dep_id;

        $this->def_campos_cont();

        $secao[1] = 'Contagem';
        $displ[1] = 'tabela';
        $campos[1][0][0] = $this->ctp_id;
        $campos[1][0][1] = $this->pro_id;
        $campos[1][0][2] = $this->und_id;
        $campos[1][0][3] = $this->ctp_quantia;
        $campos[1][0][4] = $this->bt_add;
        $campos[1][0][5] = $this->bt_del;

        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ; 
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('contagem');</script>";

        echo view('vw_edicao', $this->data);
    }

    public function add_campo($ind){
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
        $this->def_campos($dados_cta, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->cta_id;
        $campos[0][1] = $this->cta_data;
        $campos[0][2] = $this->emp_id;
        $campos[0][3] = $this->dep_id;

        $secao[1] = 'Contagem';
        $displ[1] = 'tabela';
        $dados_ctp = $this->contagem->getContagem($id);
        // debug($dados_ctp);
        if (count($dados_ctp) > 0) {
            for ($c = 0; $c < count($dados_ctp); $c++) {
                $this->def_campos_cont($dados_ctp[$c], $c, $show);
                $campos[1][$c] = [];
                $campos[1][$c][count($campos[1][$c])] = $this->ctp_id;  
                $campos[1][$c][count($campos[1][$c])] = $this->pro_id;
                $campos[1][$c][count($campos[1][$c])] = $this->und_id;
                $campos[1][$c][count($campos[1][$c])] = $this->ctp_quantia;
                if($show){
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
            if($show){
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
            $cta_exc = $this->common->deleteReg('dbEstoque','est_contagem_produto',"cta_id = ".$id);
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
        $id = new MyCampo('est_contagem','cta_id');
        $id->valor = isset($dados['cta_id']) ? $dados['cta_id'] : '';
        $this->cta_id = $id->crOculto();

        $dat                        = new MyCampo('est_contagem','cta_data');
        $dat->valor           = isset($dados['cta_data'])? $dados['cta_data']: date('Y-m-d');
        $dat->largura               = 20;
        $dat->leitura               = $show;
        $this->cta_data             = $dat->crInput();
        
        $empresas           = explode(',',session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('est_contagem','emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id'])? $dados['emp_id']: '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $this->emp_id               = $emp->crSelect();

        $depos = [];
        if($dados){
            $dados_dep = $this->deposito->getDeposito($dados['dep_id'],$dados['emp_id']);
            $depos = array_column($dados_dep, 'dep_nome', 'dep_id');
        }

        $dep                        = new MyCampo('est_contagem','dep_id');
        $dep->valor = $dep->selecionado = isset($dados['dep_id'])? $dados['dep_id']: '';
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 50;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'emp_id';
        $dep->leitura               = $show;
        $this->dep_id               = $dep->crDepende();
    }        
        
    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_cont($dados = false, $pos = 0, $show = false)
    {
        $ctpid                     = new MyCampo('est_contagem_produto','ctp_id');
        $ctpid->nome               = "ctp_id[$pos]";
        $ctpid->ordem              = $pos;
        $ctpid->repete             = true;
        $ctpid->valor              = isset($dados['ctp_id']) ? $dados['ctp_id'] : '';
        $this->ctp_id              = $ctpid->crOculto();

        $prods = [];
        if($dados){
            $dados_pro = $this->produto->getProduto($dados['pro_id']);
            $prods = array_column($dados_pro, 'pro_nome', 'pro_id');
        }

        $pro                        = new MyCampo('est_contagem_produto','pro_id');
        $pro->id = $pro->nome       = "pro_id[$pos]";
        $pro->valor = $pro->selecionado = isset($dados['pro_id'])? $dados['pro_id']: '';
        $pro->ordem              = $pos;
        $pro->repete             = true;
        $pro->opcoes                = $prods;
        $pro->largura               = 40;
        $pro->obrigatorio           = true;
        $pro->urlbusca              = base_url('buscas/busca_produto');
        $pro->cadModal              = base_url('EstProduto/add/modal=true');
        $pro->dispForm              = 'col-6';
        $pro->leitura               = $show;
        $pro->funcChan              = 'buscaDadosProduto(this)';
        $this->pro_id               = $pro->crSelbusca();

        $dados_und = $this->unidades->getUndMedida();
        $unids = array_column($dados_und, 'und_nome', 'und_id');

        $und                        = new MyCampo('est_contagem_produto','und_id');
        $und->id = $und->nome      = "und_id[$pos]";
        $und->ordem              = $pos;
        $und->repete             = true;
        $und->valor = $und->selecionado  = isset($dados['und_id'])? $dados['und_id']: '';
        $und->opcoes                = $unids;
        $und->largura               = 25;
        $und->dispForm              = 'col-3';
        $und->leitura               = true;
        $this->und_id               = $und->crSelect();

        $qtia = formataQuantia(isset($dados['ctp_quantia'])?$dados['ctp_quantia']:0);

        $qti                       = new MyCampo('est_contagem_produto','ctp_quantia');
        $qti->id = $qti->nome      = "ctp_quantia[$pos]";
        $qti->tipo                 = "quantia";
        $qti->ordem                = $pos;
        $qti->repete               = true;
        $qti->obrigatorio          = true;
        $qti->valor                = $qtia['qtiv'];
        $qti->decimal              = $qtia['dec'];
        // $qti->largura               = 20;
        $qti->dispForm              = 'col-2';
        $qti->leitura               = $show;
        // debug($qti);
        $this->ctp_quantia          = $qti->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Contagem";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('".base_url("EstContagem/add_campo")."','contagem',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Contagem";
        $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        $del->funcChan = "exclui_campo('contagem',this)";
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
        $dados_cta = [
            'cta_id'    => $dados['cta_id'],
            'cta_data'  => $dados['cta_data'],
            'emp_id'    => $dados['emp_id'],
            'dep_id'    => $dados['dep_id'],
        ];
        if ($this->contagem->save($dados_cta)) {
            $cta_id = $this->contagem->getInsertID();
            $data_atu = date('Y-m-d H:i:s');
            if($dados['cta_id'] != ''){
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
                $salva = $this->common->insertReg('dbEstoque','est_controle_contagem',$dados_cont);
            }
            if(isset($dados['pro_id'])){
                $data_atu = date('Y-m-d H:i:s');
                foreach($dados['pro_id'] as $key => $value){
                    $qtia = str_replace(",",".",$dados['ctp_quantia'][$key]);
                    $dados_pro = [
                        'ctp_id'    => $dados['ctp_id'][$key],
                        'cta_id'    => $cta_id,
                        'pro_id'    => $dados['pro_id'][$key],
                        'und_id'    => $dados['und_id'][$key],
                        'ctp_quantia'   => $qtia,
                        'ctp_atualizado' => $data_atu
                    ];
                    // debug($dados_pro, true);
                    if($dados['ctp_id'][$key] != ''){
                        $salva = $this->common->updateReg('dbEstoque','est_contagem_produto','ctp_id = '.$dados['ctp_id'][$key],$dados_pro);
                    } else {
                        $salva = $this->common->insertReg('dbEstoque','est_contagem_produto',$dados_pro);
                    }
                    if (!$salva) {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                        break;
                    }
                }
                $cta_exc = $this->common->deleteReg('dbEstoque','est_contagem_produto',"cta_id = ".$cta_id." AND ctp_atualizado != '".$data_atu."'");
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
