<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\BarcodeGen;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquGrupoProdutoModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstMarca extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $Produto;
    public $grupoproduto;
    public $unidades;
    public $marca;
    public $common;
    public $mar_id;
    public $mar_codigo;
    public $pro_id;
    public $und_id;
    public $mar_nome;
    public $unm_id;
    public $mar_apresenta;
    public $mar_conversao;
    public $mar_aprovada;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->Produto     = new EstoquProdutoModel();
        $this->grupoproduto = new EstoquGrupoProdutoModel();
        $this->unidades     = new EstoquUndMedidaModel();
        $this->marca        = new EstoquMarcaModel();
        $this->common       = new CommonModel();
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
        $this->data['colunas'] = montaColunasLista($this->data,'mar_id');
        $this->data['url_lista'] = base_url($this->data['controler'].'/lista');
        echo view('vw_lista', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // $empresas = explode(',',session()->get('usu_empresa'));

            $dados_marcas = $this->marca->getMarca();
            // debug($dados_marcas);
            for ($dm=0; $dm < count($dados_marcas); $dm++) { 
                // $dados_compr[$dc]['d'] = '';
                $qtia = formataQuantia($dados_marcas[$dm]['mar_conversao']);
                $dados_marcas[$dm]['mar_conversao'] = $qtia['qtia'];
            }
            // $this->data['exclusao'];
            $marcas = [
                'data' => montaListaColunas($this->data,'mar_id',$dados_marcas,'pro_nome'),
            ];
        // }
        echo json_encode($marcas);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add($modal = false)
    {
        $this->def_campos();
            $this->def_campos_codigo(false, 0);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = "<div class='col-6 float-start'>";
        $campos[0][1] = $this->mar_id;
        $campos[0][2] = $this->mar_codigo;
        $campos[0][3] = $this->pro_id;
        $campos[0][4] = $this->und_id; 
        $campos[0][5] = $this->mar_nome;
        $campos[0][6] = $this->unm_id;
        $campos[0][7] = $this->mar_apresenta;
        $campos[0][8] = $this->mar_conversao;
        $campos[0][9] = $this->mar_aprovada;
        $campos[0][10] = "</div>";
        $campos[0][11] = "<div id='dados_produto' class='col-6 float-start'>";
        $campos[0][12] = "</div>";

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            echo view('vw_edicao_modal', $this->data);
        }
    }

    /**
     * Consulta
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
        $dados_marca = $this->marca->getMarca($id);
        // debug($dados_marca);
        $this->def_campos($dados_marca[0], 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = "<div class='col-6 float-start'>";
        $campos[0][1] = $this->mar_id;
        $campos[0][2] = $this->pro_id;
        $campos[0][3] = $this->und_id;
        $campos[0][4] = $this->mar_nome;
        $campos[0][5] = $this->unm_id;
        $campos[0][6] = $this->mar_apresenta;
        $campos[0][7] = $this->mar_conversao;
        $campos[0][8] = $this->mar_aprovada;

        $secao[1] = 'Código de Barras';
        $displ[1] = 'tabela';
        if(count($dados_marca) > 0){
            for($c=0;$c<count($dados_marca);$c++){
                $this->def_campos_codigo($dados_marca[$c], $c);
                $campos[1][$c][0] = $this->mar_codigo;
                $campos[1][$c][1] = $this->bt_add;
                $campos[1][$c][2] = $this->bt_del;
            }
        } else {
            $this->def_campos_codigo(false, 0);
            $campos[1][0][0] = $this->mar_codigo;
            $campos[1][0][1] = $this->bt_add;
            $campos[1][0][2] = $this->bt_del;
        }


        $campos[0][9] = "</div>";
        $campos[0][10] = "<div id='dados_produto' class='col-6 float-start'>";
        $campos[0][11] = "</div>";

        $this->data['desc_edicao'] = $dados_marca[0]['mar_nome'];
        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ; 
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('codigo_de_barras');</script>";

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_marca', $id);

        echo view('vw_edicao', $this->data);
    }

    public function add_campo($ind){
        $this->def_campos_codigo(false, $ind);

        $campo = [];
        $campo[count($campo)] = $this->mar_codigo;
        $campo[count($campo)] = $this->bt_add;
        $campo[count($campo)] = $this->bt_del;

        echo json_encode($campo);
        exit;
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
            $this->marca->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Marca Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Marca, Verifique!';
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
    public function def_campos($dados = false, $pos = 0, $show = false)
    {
        $id = new MyCampo('est_marca','mar_id');
        $id->valor = isset($dados['mar_id']) ? $dados['mar_id'] : '';
        $this->mar_id = $id->crOculto();

        $dados_pro = $this->Produto->getProduto();
        $prods = array_column($dados_pro, 'pro_nome', 'pro_id');

        // debug($dados['pro_id']);
        $pro                        = new MyCampo('est_marca','pro_id');
        $pro->valor = $pro->selecionado = isset($dados['pro_id'])? $dados['pro_id']: '';
        $pro->opcoes                = $prods;
        $pro->largura               = 50;
        $pro->urlbusca              = base_url('buscas/busca_produto');
        $pro->cadModal              = base_url('EstProduto/add/modal=true');
        $pro->funcChan              = 'buscaDadosProduto(this)';
        $pro->leitura               = $show;
        $this->pro_id               = $pro->crSelbusca();

        $undx = isset($dados['und_marca'])? $dados['und_marca']: '';
        $dados_und = $this->unidades->getUndMedida();
        $unids = array_column($dados_und, 'und_nome', 'und_id');

        $und                        = new MyCampo('est_produto','und_id');
        $und->valor = $und->selecionado           = isset($dados['und_prod'])? $dados['und_prod']: '';
        $und->opcoes                = $unids;
        $und->largura               = 30;
        $und->leitura               = true;
        $this->und_id               = $und->crSelect();
        
        $unm                        = new MyCampo('est_marca','und_id');
        $unm->nome  = $unm->id      = 'unm_id';
        $unm->valor = $unm->selecionado           = $undx;
        $unm->opcoes                = $unids;
        $unm->largura               = 30;
        $unm->leitura               = $show;
        $unm->obrigatorio           = true;
        $this->unm_id               = $unm->crSelect();

        $nome                       = new MyCampo('est_marca','mar_nome');
        $nome->obrigatorio          = true;
        $nome->valor                = isset($dados['mar_nome'])? $dados['mar_nome']: '';
        $nome->largura               = 50;
        $nome->leitura               = $show;
        $this->mar_nome             = $nome->crInput();

        $codi                       = new MyCampo('est_marca','mar_codigo');
        $codi->obrigatorio          = true;
        $codi->valor                = isset($dados['mar_codigo'])? $dados['mar_codigo']: '';
        $codi->leitura               = $show;
        $codi->funcBlur             = 'buscarProdutoPorCodBarras()';
        $this->mar_codigo           = $codi->crInput();

        $medi                       = new MyCampo('est_marca','mar_apresenta');
        $medi->obrigatorio          = true;
        $medi->valor                = isset($dados['mar_apresenta'])? $dados['mar_apresenta']: '';
        $medi->leitura               = $show;
        $this->mar_apresenta           = $medi->crInput();

        // debug($dados['mar_conversao']);
        $qconv = formataQuantia(isset($dados['mar_conversao'])? $dados['mar_conversao']: 0);
        // debug($qconv);
        
        $conv                       = new MyCampo('est_marca','mar_conversao');
        $conv->obrigatorio          = true;
        $conv->valor                = 'quantia';
        $conv->valor                = $qconv['qtiv'];
        $conv->decimal                = $qconv['dec'];
        $conv->size                 = 3;
        $conv->maxLength            = 7;
        $conv->leitura               = $show;
        $this->mar_conversao        = $conv->crInput();

        $simnao['A'] = 'Aprovada';
        $simnao['R'] = 'Reprovada';
        $aprov        = new MyCampo('est_marca','mar_aprovada');
        $aprov->valor = $aprov->selecionado = isset($dados['mar_aprovada'])? $dados['mar_aprovada']: 'A';
        $aprov->opcoes = $simnao;
        $aprov->classep = 'mark';
        $this->mar_aprovada = $aprov->cr2opcoes();
    }


    public function def_campos_codigo($dados = false, $pos = 0, $show = false){
        $codi                       = new MyCampo('est_marca','mar_codigo');
        $codi->nome                 = "mar_codigo[$pos]";
        $codi->id                   = "mar_codigo[$pos]";
        $codi->obrigatorio          = true;
        $codi->valor                = isset($dados['mar_codigo'])? $dados['mar_codigo']: '';
        $codi->leitura               = $show;
        $codi->funcBlur             = 'buscarProdutoPorCodBarras()';
        $this->mar_codigo           = $codi->crInput();
    
        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone     = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Código";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('".base_url("EstMarca/add_campo")."','codigo_de_barras',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Código";
        $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        $del->funcChan = "exclui_campo('codigo_de_barras',this)";
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
        $dados = $this->request->getPost();
        // debug($dados, true);
        $dados_mar = [
            'mar_id'           => $dados['mar_id'],
            'pro_id'         => $dados['pro_id'],
            'mar_nome'         => $dados['mar_nome'],
            'und_id'         => $dados['unm_id'],
            'mar_aprovada'         => $dados['mar_aprovada'],
            'mar_apresenta'         => $dados['mar_apresenta'],
            'mar_conversao'         => str_replace(',','.',$dados['mar_conversao']),
        ];
        // debug($dados_mar,true);
        $salvar = $this->marca->save($dados_mar);
        if ($salvar) {
            $mar_id = $this->marca->getInsertID();
            if($dados['mar_id'] != ''){
                $mar_id = $dados['mar_id'];
            }
            if(isset($dados['mar_codigo'])){
                // debug($dados['cvs_id']);
                $cvs_exc = $this->marca->excluiCodigo($mar_id);
                for($cvs=0;$cvs<count($dados['mar_codigo']);$cvs++){
                    if(isset($dados['mar_codigo'][$cvs])){
                        $dados_cvs = [
                            'mar_id' => $mar_id,
                            'mar_codigo'=> $dados['mar_codigo'][$cvs],
                        ];
                        // debug($dados_cvs);
                        $cvs_id = $this->common->insertReg('dbEstoque','est_marca_codigo_link', $dados_cvs);
                        if(!$cvs_id){
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar os Códigos, Verifique!';
                            break;
                        }
                    }
                    $entradas = new EstoquEntradaModel();
                    $lst_entr = $entradas->getProdutoEntradaMarca($dados['mar_codigo'][$cvs]);
                    for ($e=0; $e < count($lst_entr); $e++) { 
                        $sql_ent = [
                            'enp_conversao' => str_replace(',','.',$dados['mar_conversao']),
                        ];
                        $salva = $this->common->updateReg('dbEstoque','est_entrada_produto',"enp_id = ".$lst_entr[$e]['enp_id'],$sql_ent, $lst_entr[$e]['enp_id']);
                    }
                    $saidas = new EstoquSaidaModel();
                    $lst_said = $saidas->getProdutoSaidaMarca($dados['mar_codigo'][$cvs]);
                    for ($s=0; $s < count($lst_said); $s++) { 
                        $sql_sai = [
                            'sap_conversao' => str_replace(',','.',$dados['mar_conversao']),
                        ];
                        $salva = $this->common->updateReg('dbEstoque','est_saida_produto',"sap_id = ".$lst_said[$s]['sap_id'],$sql_sai, $lst_said[$s]['sap_id']);
                    }
                }
            }
            // debug($fim, true);
            $ret['erro'] = false;
            $ret['msg'] = 'Marca gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->marca->errors();
            $ret['msg'] = 'Não foi possível gravar a Marca de Produto, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
