<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquGrupoCompraModel;
use App\Models\Estoqu\EstoquGrupoProdutoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstProduto extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $Produto;
    public $grupoproduto;
    public $grupocompra;
    public $unidades;
    public $pro_id;
    public $gru_id;
    public $grc_id;
    public $pro_nome;
    public $und_id;
    public $und_compra;
    public $pro_fcc;
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
        $this->grupocompra  = new EstoquGrupoCompraModel();
        $this->unidades     = new EstoquUndMedidaModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'pro_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
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
        $empresas = explode(',', session()->get('usu_empresa'));

        $dados_produto = $this->Produto->getProduto();
        for ($p = 0; $p < count($dados_produto); $p++) {
            $dados_produto[$p]['pro_fcc'] = formataQuantia($dados_produto[$p]['pro_fcc'])['qtiv'];
        }
        $this->data['exclusao'] = false;
        $produtos = [
            'data' => montaListaColunas($this->data, 'pro_id', $dados_produto, 'pro_nome'),
        ];
        // }
        echo json_encode($produtos);
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
        $campos[0][0] = $this->pro_id;
        $campos[0][1] = $this->gru_id;
        $campos[0][2] = $this->grc_id;
        $campos[0][3] = $this->pro_nome;
        $campos[0][4] = $this->und_id;
        // $campos[0][5] = $this->pro_minimo;
        $campos[0][5] = $this->und_compra;
        $campos[0][6] = $this->pro_fcc;
        $campos[0][7] = $this->pro_link;
        $campos[0][8] = $this->pro_status;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        if (!$modal) {
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
        $dados_Produto = $this->Produto->getProduto($id)[0];
        $this->def_campos($dados_Produto, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->pro_id;
        $campos[0][1] = $this->gru_id;
        $campos[0][2] = $this->grc_id;
        $campos[0][3] = $this->pro_nome;
        $campos[0][4] = $this->und_id;
        // $campos[0][5] = $this->pro_minimo;
        $campos[0][5] = $this->und_compra;
        $campos[0][6] = $this->pro_fcc;
        $campos[0][7] = $this->pro_link;
        $campos[0][8] = $this->pro_status;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_produto', $id);

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
            $this->Produto->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Produto Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Produto, Verifique!';
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
        $id = new MyCampo('est_produto', 'pro_id');
        $id->valor = isset($dados['pro_id']) ? $dados['pro_id'] : '';
        $this->pro_id = $id->crOculto();

        $nome = new MyCampo('est_produto', 'pro_nome');
        $nome->obrigatorio = true;
        $nome->valor = isset($dados['pro_nome']) ? $dados['pro_nome'] : '';
        $nome->leitura       = $show;
        $this->pro_nome = $nome->crInput();

        $dados_gru = $this->grupoproduto->getGrupoProduto();
        $grups = array_column($dados_gru, 'gru_nome', 'gru_id');

        $gru                        = new MyCampo('est_produto', 'gru_id');
        $gru->selecionado           = isset($dados['gru_id']) ? $dados['gru_id'] : '';
        $gru->opcoes                = $grups;
        $gru->largura               = 50;
        $gru->leitura       = $show;
        $this->gru_id         = $gru->crSelect();

        $dados_grc = $this->grupocompra->getGrupocompra();
        $grucs = array_column($dados_grc, 'grc_nome', 'grc_id');

        $grc                        = new MyCampo('est_produto', 'grc_id');
        $grc->selecionado           = isset($dados['grc_id']) ? $dados['grc_id'] : '';
        $grc->opcoes                = $grucs;
        $grc->largura               = 50;
        $grc->leitura       = $show;
        $this->grc_id         = $grc->crSelect();

        $dados_und = $this->unidades->getUndMedida();
        $unids = array_column($dados_und, 'und_completa', 'und_id');

        $und                        = new MyCampo('est_produto', 'und_id');
        $und->selecionado           = isset($dados['und_id']) ? $dados['und_id'] : '';
        $und->opcoes                = $unids;
        $und->funcChan              = "alteraTextoInfo('und_id','und_id_compra', 'pro_fcc', 'Informe quantos <b>TEXTO1</b> tem em <b>1 TEXTO2</b>');";
        $und->largura               = 50;
        $und->leitura       = $show;
        $this->und_id         = $und->crSelect();

        $und                        = new MyCampo('est_produto', 'und_id_compra');
        $und->selecionado           = isset($dados['und_id_compra']) ? $dados['und_id_compra'] : '';
        $und->opcoes                = $unids;
        $und->largura               = 50;
        $und->funcChan              = "alteraTextoInfo('und_id','und_id_compra', 'pro_fcc', 'Informe quantos <b>TEXTO1</b> tem em <b>1 TEXTO2</b>');";
        $und->leitura               = $show;
        $this->und_compra           = $und->crSelect();

        $unpro = $uncom = '';
        if (isset($dados['und_id'])) {
            $unpro = $unids[$dados['und_id']];
        }
        if (isset($dados['und_id_compra'])) {
            $uncom = (isset($unids[$dados['und_id_compra']])) ? $unids[$dados['und_id_compra']] : '';
        }
        $txt_info = '';
        if ($unpro != '' && $uncom != '') {
            $txt_info = 'Informe quantos <b>' . $unpro . '</b> tem em <b>1 ' . $uncom . '</b>';
        }
        if (isset($dados['pro_fcc']) && $dados['pro_fcc'] != '') {
            $fcc = formataQuantia($dados['pro_fcc']);
        } else {
            if (isset($dados['und_id_compra'])) {
                if ($dados['und_id_compra'] == $dados['und_id']) {
                    $fcc = formataQuantia(1);
                } else {
                    $conv = $this->unidades->getConversaoDePara($dados['und_id_compra'], $dados['und_id']);
                    if (count($conv) > 0) {
                        $fcc = formataQuantia($conv[0]['cvs_fator']);
                    } else {
                        $fcc = formataQuantia(0);
                    }
                }
            } else {
                $fcc = formataQuantia(0);
            }
        }
        $conv                       = new MyCampo('est_produto', 'pro_fcc');
        $conv->obrigatorio          = true;
        $conv->tipo                 = 'quantia';
        $conv->valor                = $fcc['qtiv'];
        $conv->decimal              = $fcc['dec'];
        $conv->infotop              = $txt_info;
        $conv->size                 = 3;
        $conv->maxLength            = 7;
        $conv->leitura              = $show;
        $this->pro_fcc              = $conv->crInput();

        $link                      = new MyCampo('est_produto', 'pro_link');
        $link->tipo                = 'site';
        $link->valor               = isset($dados['pro_link']) ? $dados['pro_link'] : '';
        $link->leitura             = $show;
        $this->pro_link            = $link->crInput();

        $simnao['A'] = 'Ativo';
        $simnao['I'] = 'Inativo';
        $atin                      = new MyCampo('est_produto','pro_status');
        $atin->valor               = $atin->selecionado = isset($dados['pro_status'])? $dados['pro_status']: 'A';
        $atin->opcoes              = $simnao;
        $atin->classep             = 'mark';
        $this->pro_status          = $atin->cr2opcoes();

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
        $dados_dep = [
            'pro_id'            => $dados['pro_id'],
            'gru_id'            => $dados['gru_id'],
            'grc_id'            => $dados['grc_id'],
            'pro_nome'          => $dados['pro_nome'],
            'pro_fcc'           => $dados['pro_fcc'],
            'pro_link'          => $dados['pro_link'],
            'pro_status'        => $dados['pro_status'],
            'und_id'            => $dados['und_id'],
            'und_id_compra'     => $dados['und_id_compra'],
        ];
        if ($this->Produto->save($dados_dep)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Produto gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar o Produto, Verifique!';
        }
        echo json_encode($ret);
    }
}
