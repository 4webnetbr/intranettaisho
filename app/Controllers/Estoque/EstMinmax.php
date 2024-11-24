<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquMinmaxModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstMinmax extends BaseController {
    public $data = [];
    public $permissao = '';
    public $minmax;
    public $common;
    public $empresa;
    public $produto;
    public $unidades;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->minmax = new EstoquMinmaxModel();
        $this->common    = new CommonModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->produto       = new EstoquProdutoModel();
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
        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']     	= 'minmax'; 
        $this->data['colunas']      = montaColunasLista($this->data,'mmi_id');
        $this->data['url_lista']    = base_url($this->data['controler'].'/lista');
        $this->data['campos']     	= $campos;  
        $this->data['script']       = "<script>carrega_lista('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1){
        
        $empresas  = explode(',',session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres    = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';        
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        $emp->dispForm              = 'linha';
        $emp->largura               = 50;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        if($tipo == 1){
            $emp->funcChan              = "carrega_lista(this, 'EstMinmax/lista','minmax');";
        } else {
            $emp->funcChan              = "atualizaProdutosMinmax()";
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
        if($param == 'undefined'){
            $param = false;
        }

        // if (!$pedid = cache('pedid')) {
            $empresas           = explode(',',$param);
            $campos = montaColunasCampos($this->data, 'mmi_id');
            // debug($campos);
            $dados_minmax = $this->minmax->getMinmax($empresas);
            for ($dc=0; $dc < count($dados_minmax) ; $dc++) { 
                $mmi = $dados_minmax[$dc];
                $qmin = formataQuantia($mmi['mmi_minimo']);
                $qmax = formataQuantia($mmi['mmi_maximo']);

                $dados_minmax[$dc]['mmi_minimo'] = $qmin['qtia'];
                $dados_minmax[$dc]['mmi_maximo'] = $qmax['qtia'];
                // $dados_mmiid[$dc]['mmi_usuario'] = $log['usua_alterou'];
            }
            $minmax = [
                'data' => montaListaColunas($this->data, 'mmi_id', $dados_minmax, $campos[1]),
            ];
            // debug($pedid);
            // cache()->save('pedid', $pedid, 60000);
        // }

        echo json_encode($minmax);
    }

    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $this->def_campos_lista(2);
        $secao[0] = 'Quantias Mínimo e Máximo';
        $campos[0][0] = $this->dash_empresa;

        $campos[0][1] = "<div id='produtosMmi' class='accordion col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";

        $campospro = $this->montalistaProdutos(false);

        $campost[0] = array_merge($campos[0], $campospro[0]);

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campost;
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);        
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
        // debug($id);
        $dados_mmi = $this->minmax->getMinmax(false, false, $id)[0];
        // debug($dados_mmi, true);
        $this->def_campos_lista(2);
        $secao[0] = 'Quantias Mínimo e Máximo';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][1] = "<div id='produtosMmi' class='accordion col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";
        
        $campospro = $this->montalistaProdutos($dados_mmi);

        $campost[0] = array_merge($campos[0], $campospro[0]);

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campost;
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);
        
    }

    public function montalistaProdutos($dados_mmi = false, $empresa = false){
        if(!$empresa){
            $empresas = explode(',',session()->get('usu_empresa'));
        } else {
            $empresas[0] = $empresa;
        }
        $minmax = false;
        $produt = false;
        if($dados_mmi){
            $empres = $dados_mmi['emp_id'];
            $minmax = $dados_mmi['mmi_id'];
            $produt = $dados_mmi['pro_id'];
            $produtos =  $this->minmax->getMinmax($empres, $produt,$minmax);
        } else {
            $produtos =  $this->produto->getProduto(false, $empresas[0]);
        }
        
        $cabecalho = "<div class='col-12 bg-primary'>";
        $cabecalho .= "<div class='col-6 text-center float-start bg-primary text-white'><h5>Produto</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Und</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Mínimo</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Máximo</h5></div>";
        $cabecalho .= "</div>";
        $campospr[0]  = [];
        $grupo = '';
        for($p=0;$p<count($produtos);$p++){
            $this->def_campos_prod($produtos[$p], $p, $empresas[0]);
            if($produtos[$p]['gru_nome'] != $grupo){
                $grupo = $produtos[$p]['gru_nome'];
                if($p > 0){
                    $campospr[0][count($campospr[0])] = "</div>";
                    $campospr[0][count($campospr[0])] = "</div>";
                }
                $campospr[0][count($campospr[0])] = "<div class='accordion-item col-lg-6 col-12 float-start p-2 border border-primary'>";
                $campospr[0][count($campospr[0])] = "<div class='accordion-button bg-primary p-2 collapsed' data-bs-toggle='collapse' data-bs-target='#collapsePed$p' aria-expanded='true' aria-controls='collapsePed$p'>";
                $campospr[0][count($campospr[0])] = "<h4 class='text-white'>$grupo</h4>";
                $campospr[0][count($campospr[0])] = "</div>";
                $campospr[0][count($campospr[0])] = $cabecalho;
                $campospr[0][count($campospr[0])] = "<div id='collapsePed$p' class='accordion-collapse collapse col-12 overflow-y-auto' style='max-height:50vh'>";
            }
            $campospr[0][count($campospr[0])] = "<div class='col-6 text-start float-start'>";
            $campospr[0][count($campospr[0])] = $this->pro_nome;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-center float-start'>";
            $campospr[0][count($campospr[0])] = $this->und_id;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->mmi_id;
            $campospr[0][count($campospr[0])] = $this->pro_id;
            $campospr[0][count($campospr[0])] = $this->mmi_minimo;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->mmi_maximo;
            $campospr[0][count($campospr[0])] = "</div>";
        }
        $campospr[0][count($campospr[0])] = "</div>";
        $campospr[0][count($campospr[0])] = "</div>";

        return $campospr;
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
            $this->minmax->delete($id);
            $mmi_exc = $this->common->deleteReg('dbEstoque','est_minmax',"mmi_id = ".$id);
            $ret['erro'] = false;
            $ret['msg']  = 'Minmax Excluído com Sucesso';
            // session()->setFlashdata('msg', 'Minmax Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Minmax, Verifique!<br>';
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
    public function def_campos_prod($dados = false, $ord = 0, $empresa)
    {
        if(!$dados){
            return;
        }
        if(!isset($dados['mmi_id'])){
            $busca = $this->minmax->getMinmax($empresa,$dados['pro_id']);
            if($busca){
                $dados['mmi_id']     = $busca[0]['mmi_id'];
                $dados['mmi_minimo'] = $busca[0]['mmi_minimo'];
                $dados['mmi_maximo'] = $busca[0]['mmi_maximo'];
            }
        }
        $minmax        = new MyCampo('est_minmax','mmi_id');
        $minmax->ordem = $ord;
        $minmax->valor = isset($dados['mmi_id'])?$dados['mmi_id']:'';
        $this->mmi_id = $minmax->crOculto();

        $proid = new MyCampo('est_minmax','pro_id');
        $proid->ordem = $ord;
        $proid->valor = $dados['pro_id'];
        $this->pro_id = $proid->crOculto();

        $gru                        = new MyCampo('est_grupoproduto','gru_nome');
        $gru->valor = $gru->selecionado = $dados['gru_nome'];
        $gru->label                 = '';
        $gru->ordem                 = $ord;
        $gru->largura               = 40;
        $gru->leitura               = true;
        $gru->dispForm              = '';
        $this->gru_nome               = $gru->crInput();

        $pro                        = new MyCampo('est_produto','pro_nome');
        $pro->valor = $pro->selecionado = $dados['pro_nome'];
        $pro->label                 = '';
        $pro->ordem                 = $ord;
        $pro->largura               = 30;
        $pro->leitura               = true;
        $pro->dispForm              = '';
        $pro->classep                 = ' text-nowrap overflow-hidden';
        $this->pro_nome               = $pro->crShow();

        $und = (isset($dados['und_id_compra'])?$dados['und_id_compra']:$dados['und_id']);
        $dados_und = $this->unidades->getUndMedida($und)[0];
        // debug($dados_und);
        $und                        = new MyCampo('est_unidades','und_sigla');
        $und->valor = $und->selecionado  = $dados_und['und_sigla'];
        $und->label                 = '';
        $und->ordem                 = $ord;
        $und->largura               = 13;
        $und->dispForm              = '';
        $und->classep                 = ' text-nowrap overflow-hidden';
        $this->und_id               = $und->crShow();

        $qmin = formataQuantia(isset($dados['mmi_minimo'])? $dados['mmi_minimo']: 0);

        $min                        = new MyCampo('est_minmax','mmi_minimo');
        $min->valor                 = $qmin['qtiv'];
        $min->decimal               = $qmin['dec'];
        $min->label                 = '';
        $min->ordem                 = $ord;
        $min->largura               = 12;
        $min->dispForm              = '';
        $min->leitura               = false;
        $min->funcBlur              =  'gravaMinmax(this)';
        $min->size                  = 10;
        $min->maxLength             = 10;
        $this->mmi_minimo             = $min->crInput();

        $qmax = formataQuantia(isset($dados['mmi_maximo'])? $dados['mmi_maximo']: 0);
        $max                        = new MyCampo('est_minmax','mmi_maximo');
        $max->valor                 = $qmax['qtiv'];
        $max->decimal               = $qmax['dec'];
        $max->label                 = '';
        $max->ordem                 = $ord;
        $max->largura               = 12;
        $max->dispForm              = '';
        $max->leitura               = false;
        $max->funcBlur              =  'gravaMinmax(this)';
        $max->size                  = 10;
        $max->maxLength             = 10;
        $this->mmi_maximo             = $max->crInput();
    }

    public function atualizaProdutosMinmax(){
        $dados = $this->request->getPost();
        $empresa = $dados['emp_id'];
        $campos = $this->montalistaProdutos(false, $empresa);
        $ret['campos'] = $campos;
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
        $dados = $this->request->getPost();
        $erros = [];
        $data = new  \DateTime();
        if(!$ret['erro']){
            if($dados['mmi_id'] == ''){
                $jatem = $this->minmax->getMinmax($dados['emp_id'],$dados['pro_id']);
                if($jatem){
                    $dados['mmi_id'] = $jatem[0]['mmi_id'];
                }
            }
            $dados_mmi = [
                'mmi_id'    => $dados['mmi_id'],
                'emp_id'    => $dados['emp_id'],
                'pro_id'    => $dados['pro_id'],
                'mmi_minimo'  => $dados['mmi_minimo'],
                'mmi_maximo'  => $dados['mmi_maximo'],
            ];
            if ($this->minmax->save($dados_mmi)) {
                if($dados['mmi_id'] == ''){
                    $mmi_id = $this->minmax->getInsertID();
                } else {
                    $mmi_id = $dados_mmi['mmi_id'];
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Mínimo e Máximo gravado com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['id'] = $mmi_id;
            } else {
                $erros = $this->minmax->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar o Mínimo e Máximo, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }
        echo json_encode($ret);
    }
}
