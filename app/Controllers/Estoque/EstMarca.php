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
            for ($dm=0; $dm < count($dados_marcas) ; $dm++) { 
                // $dados_compr[$dc]['d'] = '';
                $qtia = formataQuantia($dados_marcas[$dm]['mar_conversao']);
                $dados_marcas[$dm]['mar_conversao'] = $qtia['qtia'];

                
                // $codbar = substr('0000000000000'.trim($dados_marcas[$dm]['mar_codigo']), -12);
                // $barcodeGen = new BarcodeGen();
                // $imgcodbar = $barcodeGen->generateHTML($codbar);
                // $dados_marcas[$dm]['codbar'] = $imgcodbar;

            }
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

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->mar_id;
        $campos[0][1] = $this->mar_codigo;
        $campos[0][2] = $this->pro_id;
        $campos[0][3] = $this->und_id;
        $campos[0][4] = $this->mar_nome;
        $campos[0][5] = $this->unm_id;
        $campos[0][6] = $this->mar_apresenta;
        $campos[0][7] = $this->mar_conversao;

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
        $dados_marca = $this->marca->getMarca($id)[0];
        // debug($dados_marca);
        $this->def_campos($dados_marca, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->mar_id;
        $campos[0][1] = $this->mar_codigo;
        $campos[0][2] = $this->pro_id;
        $campos[0][3] = $this->und_id;
        $campos[0][4] = $this->mar_nome;
        $campos[0][5] = $this->unm_id;
        $campos[0][6] = $this->mar_apresenta;
        $campos[0][7] = $this->mar_conversao;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_marca', $id);

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
        $dados_mar = [
            'mar_id'           => $dados['mar_id'],
            'pro_id'         => $dados['pro_id'],
            'mar_codigo'         => $dados['mar_codigo'],
            'mar_nome'         => $dados['mar_nome'],
            'und_id'         => $dados['unm_id'],
            'mar_apresenta'         => $dados['mar_apresenta'],
            'mar_conversao'         => str_replace(',','.',$dados['mar_conversao']),
        ];
        // debug($dados_mar,true);
        $salvar = $this->marca->save($dados_mar);
        if ($salvar) {
            $entradas = new EstoquEntradaModel();
            $lst_entr = $entradas->getProdutoEntradaMarca($dados['mar_codigo']);
            for ($e=0; $e < count($lst_entr); $e++) { 
                $sql_ent = [
                    'enp_conversao' => str_replace(',','.',$dados['mar_conversao']),
                ];
                $salva = $this->common->updateReg('dbEstoque','est_entrada_produto',"enp_id = ".$lst_entr[$e]['enp_id'],$sql_ent);
            }
            $saidas = new EstoquSaidaModel();
            $lst_said = $saidas->getProdutoSaidaMarca($dados['mar_codigo']);
            for ($s=0; $s < count($lst_said); $s++) { 
                $sql_sai = [
                    'sap_conversao' => str_replace(',','.',$dados['mar_conversao']),
                ];
                $salva = $this->common->updateReg('dbEstoque','est_saida_produto',"sap_id = ".$lst_said[$s]['sap_id'],$sql_sai);
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
