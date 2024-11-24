<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumCargoModel;
use App\Models\Rechum\RechumJornadaModel;
use App\Models\Rechum\RechumQuadroCargoModel;
use App\Models\Rechum\RechumQuadroModel;
use App\Models\Rechum\RechumSetorModel;

class RhQuadro extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $empresa;
    public $cargo;
    public $quadro;
    public $quadrocargo;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->cargo        = new RechumCargoModel(); 
        $this->quadro       = new RechumQuadroModel(); 
        $this->quadrocargo = new RechumQuadroCargoModel(); 
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
     * Tela de Aberjora
     * index
     */
    public function index()
    {
        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']     	= 'quadro'; 
        $this->data['colunas']      = montaColunasLista($this->data,'qua_id');
        $this->data['url_lista']    = base_url($this->data['controler'].'/lista');
        $this->data['campos']     	= $campos;  
        $this->data['script']       = "<script>carrega_lista('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @rejorn void
     */
    public function lista()
    {
        $param = $_REQUEST['param'];
        if($param == 'undefined'){
            $param = false;
        }

            $empresas           = explode(',',$param);
            $dados_quadros = $this->quadro->getQuadro(false, $empresas);
            $quadros = [             
                'data' => montaListaColunas($this->data,'qua_id',$dados_quadros,'qua_id'),
            ];
        // }
        echo json_encode($quadros);
    }
    /**
     * Inclusão
     * add
     *
     * @rejorn void
     */
    public function add()
    {
        $this->def_campos_lista(2);
        $secao[0] = 'Informe as Vagas';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][1] = "<div id='cargosQua' class='accordion col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";

        // $camposcag = $this->montalistaCargos(false);

        // $campost[0] = array_merge($campos[0], $camposcag[0]);
        
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['script'] = "<script>atualizaCargosQuadro()</script>";
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);
        
    }

    public function montalistaCargos($empresa = false){
        if(!$empresa){
            $empresas = explode(',',session()->get('usu_empresa'));
        } else {
            $empresas[0] = $empresa;
        }
        $pedido = false;
        $produt = false;
        // debug($dados_cag);
        // if($dados_cag){
        //     $quadro = $dados_cag[0]['qua_id'];
        //     // $cargo = $dados_cag['quf_id'];
        //     $cargos =  $this->quadrocargo->getQuadroCargo($quadro);
        // } else {
        // }
        $cargos =  $this->cargo->getCargoSetor();
        // debug($cargos);

        $cabecalho = "<div class='col-12 bg-primary'>";
        $cabecalho .= "<div class='col-6 text-center float-start bg-primary text-white'><h5>Cargo</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Vagas</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Salário Base</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>% Participação</h5></div>";
        $cabecalho .= "</div>";
        $camposca[0]  = [];
        $grupo = '';
        for($p=0;$p<count($cargos);$p++){
            // debug($cargos[$p]);
            $quadro = $this->quadro->getQuadro(false, $empresa, $cargos[$p]['set_id']);
            if(!isset($quadro[0]['qua_id'])){
                $quadro = false;
            }
            if($quadro){
                $cargos[$p]['qua_id'] = $quadro[0]['qua_id'];
                $cagquadro = $this->quadrocargo->getQuadroCargo($quadro[0]['qua_id'], $cargos[$p]['cag_id']);
                if(isset($cagquadro[0]['cag_id'])){
                    $cargos[$p]['quf_id'] = $cagquadro[0]['quf_id'];
                    $cargos[$p]['quf_participacao'] = $cagquadro[0]['quf_participacao'];
                    $cargos[$p]['quf_vagas'] = $cagquadro[0]['quf_vagas'];
                    $cargos[$p]['quf_salario'] = $cagquadro[0]['quf_salario'];
                }
            }
            // debug($cargos[$p]);
            $this->def_campos_cargo($cargos[$p], $p);
            if($cargos[$p]['set_nome'] != $grupo){
                $grupo = $cargos[$p]['set_nome'];
                if($p > 0){
                    $camposca[0][count($camposca[0])] = "</div>";
                    $camposca[0][count($camposca[0])] = "</div>";
                }
                $camposca[0][count($camposca[0])] = "<div class='accordion-item col-lg-12 col-12 float-start p-2 border border-primary'>";
                $camposca[0][count($camposca[0])] = "<div class='accordion-button bg-primary p-2 collapsed' data-bs-toggle='collapse' data-bs-target='#collapsePed$p' aria-expanded='true' aria-controls='collapsePed$p'>";
                $camposca[0][count($camposca[0])] = "<h4 class='text-white'>$grupo</h4>";
                $camposca[0][count($camposca[0])] = "</div>";
                $camposca[0][count($camposca[0])] = $cabecalho;
                $camposca[0][count($camposca[0])] = "<div id='collapsePed$p' class='accordion-collapse collapse col-12 overflow-y-auto' style='max-height:50vh'>";
            }
            $camposca[0][count($camposca[0])] = "<div class='col-6 text-start float-start'>";
            $camposca[0][count($camposca[0])] = $this->qua_id;
            $camposca[0][count($camposca[0])] = $this->set_id;
            $camposca[0][count($camposca[0])] = $this->cag_id;
            $camposca[0][count($camposca[0])] = $this->quf_id;
            $camposca[0][count($camposca[0])] = $this->cag_nome;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-2 text-center float-start'>";
            $camposca[0][count($camposca[0])] = $this->quf_vagas;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-2 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->quf_salario;
            $camposca[0][count($camposca[0])] = "</div>";
            $camposca[0][count($camposca[0])] = "<div class='col-2 text-end float-start'>";
            $camposca[0][count($camposca[0])] = $this->quf_participacao;
            $camposca[0][count($camposca[0])] = "</div>";
        }
        $camposca[0][count($camposca[0])] = "</div>";
        $camposca[0][count($camposca[0])] = "</div>";

        return $camposca;
    }

    /**
     * Consulta
     * show
     *
     * @param mixed $id 
     * @rejorn void
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
     * @rejorn void
     */
    public function edit($id, $show = false)
    {
        $quadro = $this->quadro->getQuadro($id);
        $empresa = $quadro[0]['emp_id'];
        $this->def_campos_lista(2, $empresa);
        $secao[0] = 'Informe as Vagas';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][1] = "<div id='cargosQua' class='accordion col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";

        // $camposcag = $this->montalistaCargos(false);

        // $campost[0] = array_merge($campos[0], $camposcag[0]);
        
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['script'] = "<script>atualizaCargosQuadro()</script>";
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @rejorn void
     */
    public function delete($id)
    {
        $ret = [];
        try {
            $this->quadro->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Quadro Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Quadro, Verifique!';
        }
        echo json_encode($ret);
    }

    public function def_campos_lista($tipo = 1, $empresa = false){
        
        $empresas                   = explode(',',session()->get('usu_empresa'));
        if($empresa){
            $empresas = array($empresa);            
        }
        $dados_emp                  = $this->empresa->getEmpresa($empresas);
        $empres                     = array_column($dados_emp, 'emp_apelido', 'emp_id');

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
            $emp->funcChan              = "carrega_lista(this, 'RhQuadro/lista','quadro');";
        } else {
            $emp->funcChan              = "atualizaCargosQuadro()";
        }
        $this->dash_empresa         = $emp->crSelect();

    }
    
    public function atualizaCargosQuadro(){
        $dados = $this->request->getPost();
        $empresa = $dados['emp_id'];
        // debug($quadro, true);
        $campos = $this->montalistaCargos($empresa);
        $ret['campos'] = $campos;
        echo json_encode($ret);
    }

    /**
     * Definição de Campos
     * def_campos_cargo
     *
     * @param bool $dados 
     * @rejorn void
     */
    public function def_campos_cargo($dados = false, $pos = 0, $show = false)
    {
        $quaid                      = new MyCampo('rh_quadro','qua_id');
        $quaid->valor               = isset($dados['qua_id']) ? $dados['qua_id'] : '';
        $quaid->ordem               = $pos;
        $this->qua_id               = $quaid->crOculto();

        $id                         = new MyCampo('rh_quadro_cargo','quf_id');
        $id->id                     = $id->nome = "quf_id[$pos]";
        $id->valor                  = isset($dados['quf_id']) ? $dados['quf_id'] : '';
        $id->ordem                  = $pos;
        $this->quf_id               = $id->crOculto();

        $setor                      = new MyCampo('rh_quadro','set_id');
        $setor->ordem               = $pos;
        $setor->valor               = $dados['set_id'];
        $this->set_id               = $setor->crOculto();

        $gru                        = new MyCampo('rh_setor','set_nome');
        $gru->valor = $gru->selecionado = $dados['set_nome'];
        $gru->label                 = '';
        $gru->ordem                 = $pos;
        $gru->largura               = 40;
        $gru->leitura               = true;
        $gru->dispForm              = '';
        $gru->label = $gru->place                 = '';
        $this->set_nome               = $gru->crInput();

        $funid = new MyCampo('rh_quadro_cargo','cag_id');
        $funid->ordem = $pos;
        $funid->valor = $dados['cag_id'];
        $this->cag_id = $funid->crOculto();

        // $funcoes                    = new RechumCargoModel();
        // $dados_fun                  = $funcoes->getCargo();
        // $opc_fun                    = array_column($dados_fun, 'cag_nome', 'cag_id');

        $cgn                        = new MyCampo('rh_cargo','cag_nome');
        $cgn->valor = $cgn->selecionado = $dados['cag_nome'];
        $cgn->label  = $cgn->place                 = '';
        $cgn->ordem                 = $pos;
        $cgn->largura               = 40;
        $cgn->leitura               = true;
        $cgn->dispForm              = '';
        $this->cag_nome               = $cgn->crShow();

        $vag                        = new MyCampo('rh_quadro_cargo','quf_vagas');
        $vag->id = $vag->nome       = "quf_vagas[$pos]";
        $vag->valor = $vag->selecionado = isset($dados['quf_vagas'])? $dados['quf_vagas']: 0;
        $vag->ordem                 = $pos;
        $vag->obrigatorio           = true;
        $vag->largura               = 20;
        $vag->leitura               = $show;
        $vag->dispForm              = '';
        $vag->label  = $vag->place              = '';
        $vag->funcBlur              = 'gravaQuadro(this)';
        $this->quf_vagas            = $vag->crInput();

        $sal                        = new MyCampo('rh_quadro_cargo','quf_salario');
        $sal->id = $sal->nome       = "quf_salario[$pos]";
        $sal->valor = $sal->selecionado = isset($dados['quf_salario'])? $dados['quf_salario']: '0.00';
        $sal->ordem                 = $pos;
        $sal->largura               = 25;
        $sal->leitura               = $show;
        $sal->dispForm              = '';
        $sal->label = $sal->place              = '';
        $this->quf_salario               = $sal->crInput();

        $par                        = new MyCampo('rh_quadro_cargo','quf_participacao');
        $par->id = $par->nome       = "quf_participacao[$pos]";
        $par->valor = $par->selecionado = isset($dados['quf_participacao'])? $dados['quf_participacao']: '0.00';
        $par->ordem                 = $pos;
        $par->obrigatorio           = true;
        $par->largura               = 20;
        $par->leitura               = $show;
        $par->dispForm              = '';
        $par->funcBlur              = 'gravaQuadro(this)';
        $par->label = $par->place              = '';
        $this->quf_participacao               = $par->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Função";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('".base_url("RhQuadro/add_campo")."','cargos',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Função";
        $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        $del->funcChan = "exclui_campo('cargos',this)";
        $this->bt_del   = $del->crBotao();

    }


    /**
     * Gravação
     * store
     *
     * @rejorn void
     */
    public function store()
    {
        $ret = [];
        $dados = $this->request->getPost();
        // debug($dados, true);
        $dados_fun = [
            'qua_id'           => $dados['qua_id'],
            'emp_id'           => $dados['emp_id'],
            'set_id'           => $dados['set_id'],
        ];
        // debug($dados_fun,true);
        if ($this->quadro->save($dados_fun)) {
            $ret['erro'] = false;
            $qua_id = $this->quadro->getInsertID();
            if($dados['qua_id'] != ''){
                $qua_id = $dados['qua_id'];
            }
            // if(isset($dados['quf_id'])){
                // $data_atu = date('Y-m-d H:i:s');
                // $quf_exc = $this->common->deleteReg('dbRh','rh_quadro_cargo',"qua_id = ".$qua_id);
                // foreach($dados['quf_id'] as $key => $value){
                    $dados_quf = [
                        'quf_id'    => $dados['quf_id'],
                        'qua_id'    => $qua_id,
                        'cag_id'    => $dados['cag_id'], 
                        'quf_vagas' => $dados['quf_vagas'], 
                        'quf_salario'   => $dados['quf_salario'], 
                        'quf_participacao'  => $dados['quf_participacao'],                         
                    ];
                    // debug($dados_quf);
                    if($this->quadrocargo->save($dados_quf)){
                        $ret['erro'] = false;
                        $quf_id = $this->quadrocargo->getInsertID();
                        if($dados['quf_id'] != ''){
                            $quf_id = $dados['quf_id'];
                        }
                    } else {
                        $ret['erro'] = true;
                        $ret['msg'] = 'Não foi possível gravar as cargos do Quadro!!!';
                        session()->setFlashdata('msg', $ret['msg']);
                        $ret['url'] = site_url($this->data['controler']);
                    }
                // }
            // }
            if(!$ret['erro']){
                $ret['qua_id'] = $qua_id;
                $ret['quf_id'] = $quf_id;

                $ret['msg'] = 'Quadro gravado com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            }
        } else {
            $ret['erro'] = true;
            $erros = $this->quadro->errors();
            $ret['msg'] = 'Não foi possível gravar o Quadro, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
