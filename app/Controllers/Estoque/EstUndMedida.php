<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Estoqu\EstoquConversaoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstUndMedida extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $undmedida;
    public $conversao;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->undmedida     = new EstoquUndMedidaModel();
        $this->conversao     = new EstoquConversaoModel();
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
        $this->data['colunas'] = montaColunasLista($this->data,'und_id');
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
            $dados_unidade = $this->undmedida->getUndMedida();
            $unidades = [             
                'data' => montaListaColunas($this->data,'und_id',$dados_unidade,'und_nome'),
            ];
            cache()->save('unidades', $unidades, 60000);
        // }
        echo json_encode($unidades);
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
        $campos[0][0] = $this->und_id;
        $campos[0][1] = $this->und_sigla;
        $campos[0][2] = $this->und_nome;
        $campos[0][3] = $this->und_desc;

        $this->def_campos_cvs();

        $secao[1] = 'Conversão';
        $displ[1] = 'tabela';
        $campos[1][0][0] = $this->cvs_id;
        $campos[1][0][1] = $this->cvs_dest;
        $campos[1][0][2] = $this->cvs_operador;
        $campos[1][0][3] = $this->cvs_fator;
        $campos[1][0][4] = $this->bt_add;
        $campos[1][0][5] = $this->bt_del;

        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ; 
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('conversao');</script>";

        echo view('vw_edicao', $this->data);
    }

    public function add_campo($ind){
        $this->def_campos_cvs(false, $ind);

        $campo = [];
        $campo[count($campo)] = $this->cvs_id;
        $campo[count($campo)] = $this->cvs_dest;
        $campo[count($campo)] = $this->cvs_operador;
        $campo[count($campo)] = $this->cvs_fator;
        $campo[count($campo)] = $this->bt_add;
        $campo[count($campo)] = $this->bt_del;

        echo json_encode($campo);
        exit;
    }
    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id)
    {
        $dados_undmedida = $this->undmedida->getUndMedida($id)[0];
        $this->def_campos($dados_undmedida);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->und_id;
        $campos[0][1] = $this->und_sigla;
        $campos[0][2] = $this->und_nome;
        $campos[0][3] = $this->und_desc;

        $secao[1] = 'Conversão';
        $displ[1] = 'tabela';
        $dados_conversao = $this->conversao->getConversao($id);
        if(count($dados_conversao) > 0){
            for($c=0;$c<count($dados_conversao);$c++){
                $this->def_campos_cvs($dados_conversao[$c], $c);

                $campos[1][$c][0] = $this->cvs_id;
                $campos[1][$c][1] = $this->cvs_dest;
                $campos[1][$c][2] = $this->cvs_operador;
                $campos[1][$c][3] = $this->cvs_fator;
                $campos[1][$c][4] = $this->bt_add;
                $campos[1][$c][5] = $this->bt_del;
            }
        } else {
            $this->def_campos_cvs(false, 0);
            $campos[1][0][0] = $this->cvs_id;
            $campos[1][0][1] = $this->cvs_dest;
            $campos[1][0][2] = $this->cvs_operador;
            $campos[1][0][3] = $this->cvs_fator;
            $campos[1][0][4] = $this->bt_add;
            $campos[1][0][5] = $this->bt_del;
        }
        
        $this->data['desc_edicao'] = $dados_undmedida['und_sigla'].' - '.$dados_undmedida['und_nome'];
        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ; 
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('conversao');</script>";

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_unidades', $id);

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
        $this->undmedida->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler']));
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos($dados = false, $pos = 0)
    {
        $id = new MyCampo('est_unidades','und_id');
        $id->valor = isset($dados['und_id']) ? $dados['und_id'] : '';
        $this->und_id = $id->crOculto();

        $sigla = new MyCampo('est_unidades','und_sigla');
        $sigla->obrigatorio = true;
        $sigla->minLength   = 2;
        $sigla->valor = isset($dados['und_sigla'])? $dados['und_sigla']: '';
        $this->und_sigla = $sigla->crInput();

        $nome = new MyCampo('est_unidades','und_nome');
        $nome->obrigatorio = true;
        $nome->valor = isset($dados['und_nome'])? $dados['und_nome']: '';
        $this->und_nome = $nome->crInput();

        $desc = new MyCampo('est_unidades','und_descricao');
        $desc->obrigatorio = false;
        $desc->valor    = isset($dados['und_descricao'])
            ? trim(strip_tags(html_entity_decode($dados['und_descricao'])))
            : '';
        $this->und_desc = $desc->crTexto();

    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_cvs($dados = false, $pos = 0)
    {
        $id = new MyCampo('est_conversao','cvs_id');
        $id->nome = "cvs_id[$pos]";
        $id->id = "cvs_id[$pos]";
        $id->valor = isset($dados['cvs_id']) ? $dados['cvs_id'] : '';
        $id->repete            = true;
        $this->cvs_id = $id->crOculto();

        $undmedida = $this->undmedida->getUndMedida();
        $undmed = [];
        for($u=0;$u<count($undmedida);$u++){
            $undmed[$undmedida[$u]['und_id']] = $undmedida[$u]['und_sigla'].' - '.$undmedida[$u]['und_nome'];
        }
        $dest = new MyCampo('est_conversao','cvs_destino');
        $dest->nome     = "cvs_destino[$pos]";
        $dest->id       = "cvs_destino[$pos]";
        $dest->obrigatorio = false;
        $dest->valor    = isset($dados['cvs_id_destino'])? $dados['cvs_id_destino']: '';
        $dest->selecionado    = isset($dados['cvs_id_destino'])? $dados['cvs_id_destino']: '';
        $dest->opcoes   = $undmed;
        $dest->tipo_form = 'inline';
        $dest->repete   = true;
        $dest->largura  = 30;
        $this->cvs_dest = $dest->crSelect();

        $opera['/'] = ' ½ ';
        $opera['*'] = ' X ';
        $oper = new MyCampo('est_conversao','cvs_operacao');
        $oper->nome   = "cvs_operador[$pos]";
        $oper->id     = "cvs_operador[$pos]";
        $oper->label  = "Operador";
        $oper->classe      = 'btn-outline-danger';
        $oper->obrigatorio = false;
        $oper->valor  = isset($dados['cvs_operador'])? $dados['cvs_operador']: '';
        $oper->selecionado  = isset($dados['cvs_operador'])? $dados['cvs_operador']: '';
        $oper->opcoes = $opera;
        $oper->tipo_form = 'inline';
        $oper->repete            = true;
        $this->cvs_operador = $oper->crRadio();

        if(isset($dados['cvs_fator'])){
            // debug($dados['cvs_fator']);
            $fatorq = formataQuantia($dados['cvs_fator']);
            $fator = $fatorq['qtiv'];
            // debug($fator);
            $decim = $fatorq['dec'];
        } else {
            $fator = "";
            $decim = 0;
        }
        

        $fat = new MyCampo('est_conversao','cvs_fator');
        $fat->tipo     = 'quantia';
        $fat->decimal  = $decim;
        $fat->nome     = "cvs_fator[$pos]";
        $fat->id       = "cvs_fator[$pos]";
        $fat->obrigatorio = false;
        $fat->size     = 5;
        $fat->tamanho  = 15;
        $fat->largura  = 25;
        $fat->tipo_form = 'inline';
        $fat->repete   = true;
        $fat->valor    =  $fator;
        $this->cvs_fator = $fat->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone     = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Conversão";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('".base_url("EstUndMedida/add_campo")."','conversao',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Conversão";
        $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        $del->funcChan = "exclui_campo('conversao',this)";
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
        $dados_und = [
            'und_id'           => $dados['und_id'],
            'und_sigla'        => $dados['und_sigla'],
            'und_nome'         => $dados['und_nome'],
            'und_descricao'    => $dados['und_descricao'],
        ];
        if ($this->undmedida->save($dados_und)) {
            $und_id = $this->undmedida->getInsertID();
            if($dados['und_id'] != ''){
                $und_id = $dados['und_id'];
            }
            if(isset($dados['cvs_id'])){
                $data_atu = date('Y-m-d H:i');
                // debug($dados['cvs_id']);
                for($cvs=0;$cvs<count($dados['cvs_id']);$cvs++){
                    if(isset($dados['cvs_id'][$cvs]) && $dados['cvs_destino'][$cvs] != ''){
                        $dados_cvs = [
                            'cvs_id'        => $dados['cvs_id'][$cvs],
                            'cvs_id_origem' => $und_id,
                            'cvs_id_destino'=> $dados['cvs_destino'][$cvs],
                            'cvs_operador'  => $dados['cvs_operador'][$cvs],
                            'cvs_fator'     => $dados['cvs_fator'][$cvs],
                            'cvs_atualizado' => $data_atu,
                        ];
                        // debug($dados_cvs);
                        $cvs_id = $this->conversao->save($dados_cvs);
                        if(!$cvs_id){
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar as Conversões, Verifique!';
                            break;
                        }
                    }
                }
                $cvs_exc = $this->conversao->exclui($und_id,$data_atu);
            }
            $ret['erro'] = false;
            $ret['msg'] = 'Unidade de Medida gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
            $ret['urledit'] = site_url($this->data['controler'] . '/edit/' . $und_id);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Unidade de Medida, Verifique!';
        }
        echo json_encode($ret);
    }
}
