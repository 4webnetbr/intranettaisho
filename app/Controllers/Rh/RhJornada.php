<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumJornadaModel;

class RhJornada extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $jornada;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->jornada       = new RechumJornadaModel(); 
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
        $this->data['colunas'] = montaColunasLista($this->data,'jor_id');
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

            $dados_jornadas = $this->jornada->getJornada();
            $jornadas = [             
                'data' => montaListaColunas($this->data,'jor_id',$dados_jornadas,'jor_nome'),
            ];
        // }
        echo json_encode($jornadas);
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
        $campos[0][0] = $this->jor_id;
        $campos[0][count($campos[0])] = $this->emp_id;
        $campos[0][count($campos[0])] = $this->jor_nome;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start'>&nbsp;</div>";
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start text-center'><h5>Expediente</h5></div>";
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start text-center'><h5>Intervalo</h5></div>";
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Segunda-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_segexpinicio;
        $campos[0][count($campos[0])] = $this->jor_segexpfim;
        $campos[0][count($campos[0])] = $this->jor_segintinicio;
        $campos[0][count($campos[0])] = $this->jor_segintfim;
        $campos[0][count($campos[0])] = $this->jor_seghorasdia;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Terça-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_terexpinicio;
        $campos[0][count($campos[0])] = $this->jor_terexpfim;
        $campos[0][count($campos[0])] = $this->jor_terintinicio;
        $campos[0][count($campos[0])] = $this->jor_terintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Quarta-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_quaexpinicio;
        $campos[0][count($campos[0])] = $this->jor_quaexpfim;
        $campos[0][count($campos[0])] = $this->jor_quaintinicio;
        $campos[0][count($campos[0])] = $this->jor_quaintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Quinta-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_quiexpinicio;
        $campos[0][count($campos[0])] = $this->jor_quiexpfim;
        $campos[0][count($campos[0])] = $this->jor_quiintinicio;
        $campos[0][count($campos[0])] = $this->jor_quiintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Sexta-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_sexexpinicio;
        $campos[0][count($campos[0])] = $this->jor_sexexpfim;
        $campos[0][count($campos[0])] = $this->jor_sexintinicio;
        $campos[0][count($campos[0])] = $this->jor_sexintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Sábado</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_sabexpinicio;
        $campos[0][count($campos[0])] = $this->jor_sabexpfim;
        $campos[0][count($campos[0])] = $this->jor_sabintinicio;
        $campos[0][count($campos[0])] = $this->jor_sabintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Domingo</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_domexpinicio;
        $campos[0][count($campos[0])] = $this->jor_domexpfim;
        $campos[0][count($campos[0])] = $this->jor_domintinicio;
        $campos[0][count($campos[0])] = $this->jor_domintfim;

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
        $dados_jornada = $this->jornada->getJornada($id)[0];
        // debug($dados_jornada);
        $this->def_campos($dados_jornada, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->jor_id;
        $campos[0][count($campos[0])] = $this->emp_id;
        $campos[0][count($campos[0])] = $this->jor_nome;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start'>&nbsp;</div>";
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start text-center'><h5>Expediente</h5></div>";
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start text-center'><h5>Intervalo</h5></div>";
        $campos[0][count($campos[0])] = "<div class='row col-3 float-start mt-3'><h5>Segunda-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_segexpinicio;
        $campos[0][count($campos[0])] = $this->jor_segexpfim;
        $campos[0][count($campos[0])] = $this->jor_segintinicio;
        $campos[0][count($campos[0])] = $this->jor_segintfim;
        $campos[0][count($campos[0])] = $this->jor_seghorasdia;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Terça-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_terexpinicio;
        $campos[0][count($campos[0])] = $this->jor_terexpfim;
        $campos[0][count($campos[0])] = $this->jor_terintinicio;
        $campos[0][count($campos[0])] = $this->jor_terintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Quarta-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_quaexpinicio;
        $campos[0][count($campos[0])] = $this->jor_quaexpfim;
        $campos[0][count($campos[0])] = $this->jor_quaintinicio;
        $campos[0][count($campos[0])] = $this->jor_quaintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Quinta-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_quiexpinicio;
        $campos[0][count($campos[0])] = $this->jor_quiexpfim;
        $campos[0][count($campos[0])] = $this->jor_quiintinicio;
        $campos[0][count($campos[0])] = $this->jor_quiintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Sexta-feira</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_sexexpinicio;
        $campos[0][count($campos[0])] = $this->jor_sexexpfim;
        $campos[0][count($campos[0])] = $this->jor_sexintinicio;
        $campos[0][count($campos[0])] = $this->jor_sexintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Sábado</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_sabexpinicio;
        $campos[0][count($campos[0])] = $this->jor_sabexpfim;
        $campos[0][count($campos[0])] = $this->jor_sabintinicio;
        $campos[0][count($campos[0])] = $this->jor_sabintfim;
        $campos[0][count($campos[0])] = "<div class='row col-4 float-start mt-3'><h5>Domingo</h5></div>";
        $campos[0][count($campos[0])] = $this->jor_domexpinicio;
        $campos[0][count($campos[0])] = $this->jor_domexpfim;
        $campos[0][count($campos[0])] = $this->jor_domintinicio;
        $campos[0][count($campos[0])] = $this->jor_domintfim;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_jornada', $id);

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
            $this->jornada->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Jornada Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Jornada, Verifique!';
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
        $id = new MyCampo('rh_jornada','jor_id');
        $id->valor = isset($dados['jor_id']) ? $dados['jor_id'] : '';
        $this->jor_id = $id->crOculto();

        $empresas           = explode(',',session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('rh_jornada','emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id'])? $dados['emp_id']: '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $this->emp_id               = $emp->crSelect();

        $nome                       = new MyCampo('rh_jornada','jor_nome');
        $nome->obrigatorio          = true;
        $nome->valor                = isset($dados['jor_nome'])? $dados['jor_nome']: '';
        $nome->largura               = 50;
        $nome->tamanho               = 50;
        $nome->leitura               = $show;
        $this->jor_nome             = $nome->crInput();

        $sein                       = new MyCampo('rh_jornada','jor_segexpinicio');
        $sein->dispForm             = 'col-2';
        $sein->valor                = isset($dados['jor_segexpinicio'])? $dados['jor_segexpinicio']: '';
        $sein->leitura               = $show;
        $this->jor_segexpinicio             = $sein->crInput();

        $sefi                       = new MyCampo('rh_jornada','jor_segexpfim');
        $sefi->dispForm             = 'col-2';
        $sefi->valor                = isset($dados['jor_segexpfim'])? $dados['jor_segexpfim']: '';
        $sefi->leitura               = $show;
        $this->jor_segexpfim             = $sefi->crInput();

        $siin                       = new MyCampo('rh_jornada','jor_segintinicio');
        $siin->dispForm             = 'col-2';
        $siin->valor                = isset($dados['jor_segintinicio'])? $dados['jor_segintinicio']: '';
        $siin->leitura               = $show;
        $this->jor_segintinicio             = $siin->crInput();

        $sifi                       = new MyCampo('rh_jornada','jor_segintfim');
        $sifi->dispForm             = 'col-2';
        $sifi->valor                = isset($dados['jor_segintfim'])? $dados['jor_segintfim']: '';
        $sifi->leitura               = $show;
        $this->jor_segintfim             = $sifi->crInput();

        $shdi                       = new MyCampo('rh_jornada','jor_seghorasdia');
        $shdi->dispForm             = 'col-1';
        $shdi->valor                = isset($dados['jor_seghorasdia'])? $dados['jor_seghorasdia']: '';
        $shdi->leitura              = true;
        $this->jor_seghorasdia      = $shdi->crInput();

        $tein                       = new MyCampo('rh_jornada','jor_terexpinicio');
        $tein->dispForm             = 'col-2';
        $tein->valor                = isset($dados['jor_terexpinicio'])? $dados['jor_terexpinicio']: '';
        $tein->leitura               = $show;
        $this->jor_terexpinicio             = $tein->crInput();

        $tefi                       = new MyCampo('rh_jornada','jor_terexpfim');
        $tefi->dispForm             = 'col-2';
        $tefi->valor                = isset($dados['jor_terexpfim'])? $dados['jor_terexpfim']: '';
        $tefi->leitura               = $show;
        $this->jor_terexpfim             = $tefi->crInput();

        $tiin                       = new MyCampo('rh_jornada','jor_terintinicio');
        $tiin->dispForm             = 'col-2';
        $tiin->valor                = isset($dados['jor_terintinicio'])? $dados['jor_terintinicio']: '';
        $tiin->leitura               = $show;
        $this->jor_terintinicio             = $tiin->crInput();

        $tifi                       = new MyCampo('rh_jornada','jor_terintfim');
        $tifi->dispForm             = 'col-2';
        $tifi->valor                = isset($dados['jor_terintfim'])? $dados['jor_terintfim']: '';
        $tifi->leitura               = $show;
        $this->jor_terintfim             = $tifi->crInput();

        $qein                       = new MyCampo('rh_jornada','jor_quaexpinicio');
        $qein->dispForm             = 'col-2';
        $qein->valor                = isset($dados['jor_quaexpinicio'])? $dados['jor_quaexpinicio']: '';
        $qein->leitura               = $show;
        $this->jor_quaexpinicio             = $qein->crInput();

        $qefi                       = new MyCampo('rh_jornada','jor_quaexpfim');
        $qefi->dispForm             = 'col-2';
        $qefi->valor                = isset($dados['jor_quaexpfim'])? $dados['jor_quaexpfim']: '';
        $qefi->leitura               = $show;
        $this->jor_quaexpfim             = $qefi->crInput();

        $qiin                       = new MyCampo('rh_jornada','jor_quaintinicio');
        $qiin->dispForm             = 'col-2';
        $qiin->valor                = isset($dados['jor_quaintinicio'])? $dados['jor_quaintinicio']: '';
        $qiin->leitura               = $show;
        $this->jor_quaintinicio             = $qiin->crInput();

        $qifi                       = new MyCampo('rh_jornada','jor_quaintfim');
        $qifi->dispForm             = 'col-2';
        $qifi->valor                = isset($dados['jor_quaintfim'])? $dados['jor_quaintfim']: '';
        $qifi->leitura               = $show;
        $this->jor_quaintfim             = $qifi->crInput();

        $kein                       = new MyCampo('rh_jornada','jor_quiexpinicio');
        $kein->dispForm             = 'col-2';
        $kein->valor                = isset($dados['jor_quiexpinicio'])? $dados['jor_quiexpinicio']: '';
        $kein->leitura               = $show;
        $this->jor_quiexpinicio             = $kein->crInput();

        $kefi                       = new MyCampo('rh_jornada','jor_quiexpfim');
        $kefi->dispForm             = 'col-2';
        $kefi->valor                = isset($dados['jor_quiexpfim'])? $dados['jor_quiexpfim']: '';
        $kefi->leitura               = $show;
        $this->jor_quiexpfim             = $kefi->crInput();

        $kiin                       = new MyCampo('rh_jornada','jor_quiintinicio');
        $kiin->dispForm             = 'col-2';
        $kiin->valor                = isset($dados['jor_quiintinicio'])? $dados['jor_quiintinicio']: '';
        $kiin->leitura               = $show;
        $this->jor_quiintinicio             = $kiin->crInput();

        $kifi                       = new MyCampo('rh_jornada','jor_quiintfim');
        $kifi->dispForm             = 'col-2';
        $kifi->valor                = isset($dados['jor_quiintfim'])? $dados['jor_quiintfim']: '';
        $kifi->leitura               = $show;
        $this->jor_quiintfim             = $kifi->crInput();

        $xein                       = new MyCampo('rh_jornada','jor_sexexpinicio');
        $xein->dispForm             = 'col-2';
        $xein->valor                = isset($dados['jor_sexexpinicio'])? $dados['jor_sexexpinicio']: '';
        $xein->leitura               = $show;
        $this->jor_sexexpinicio             = $xein->crInput();

        $xefi                       = new MyCampo('rh_jornada','jor_sexexpfim');
        $xefi->dispForm             = 'col-2';
        $xefi->valor                = isset($dados['jor_sexexpfim'])? $dados['jor_sexexpfim']: '';
        $xefi->leitura               = $show;
        $this->jor_sexexpfim             = $xefi->crInput();

        $xiin                       = new MyCampo('rh_jornada','jor_sexintinicio');
        $xiin->dispForm             = 'col-2';
        $xiin->valor                = isset($dados['jor_sexintinicio'])? $dados['jor_sexintinicio']: '';
        $xiin->leitura               = $show;
        $this->jor_sexintinicio             = $xiin->crInput();

        $xifi                       = new MyCampo('rh_jornada','jor_sexintfim');
        $xifi->dispForm             = 'col-2';
        $xifi->valor                = isset($dados['jor_sexintfim'])? $dados['jor_sexintfim']: '';
        $xifi->leitura               = $show;
        $this->jor_sexintfim             = $xifi->crInput();

        $aein                       = new MyCampo('rh_jornada','jor_sabexpinicio');
        $aein->dispForm             = 'col-2';
        $aein->valor                = isset($dados['jor_sabexpinicio'])? $dados['jor_sabexpinicio']: '';
        $aein->leitura               = $show;
        $this->jor_sabexpinicio             = $aein->crInput();

        $aefi                       = new MyCampo('rh_jornada','jor_sabexpfim');
        $aefi->dispForm             = 'col-2';
        $aefi->valor                = isset($dados['jor_sabexpfim'])? $dados['jor_sabexpfim']: '';
        $aefi->leitura               = $show;
        $this->jor_sabexpfim             = $aefi->crInput();

        $aiin                       = new MyCampo('rh_jornada','jor_sabintinicio');
        $aiin->dispForm             = 'col-2';
        $aiin->valor                = isset($dados['jor_sabintinicio'])? $dados['jor_sabintinicio']: '';
        $aiin->leitura               = $show;
        $this->jor_sabintinicio             = $aiin->crInput();

        $aifi                       = new MyCampo('rh_jornada','jor_sabintfim');
        $aifi->dispForm             = 'col-2';
        $aifi->valor                = isset($dados['jor_sabintfim'])? $dados['jor_sabintfim']: '';
        $aifi->leitura               = $show;
        $this->jor_sabintfim             = $aifi->crInput();

        $dein                       = new MyCampo('rh_jornada','jor_domexpinicio');
        $dein->dispForm             = 'col-2';
        $dein->valor                = isset($dados['jor_domexpinicio'])? $dados['jor_domexpinicio']: '--:--';
        $dein->leitura               = $show;
        $this->jor_domexpinicio             = $dein->crInput();

        $defi                       = new MyCampo('rh_jornada','jor_domexpfim');
        $defi->dispForm             = 'col-2';
        $defi->valor                = isset($dados['jor_domexpfim'])? $dados['jor_domexpfim']: '--:--';
        $defi->leitura               = $show;
        $this->jor_domexpfim             = $defi->crInput();

        $diin                       = new MyCampo('rh_jornada','jor_domintinicio');
        $diin->dispForm             = 'col-2';
        $diin->valor                = isset($dados['jor_domintinicio'])? $dados['jor_domintinicio']: '--:--';
        $diin->leitura               = $show;
        $this->jor_domintinicio             = $diin->crInput();

        $difi                       = new MyCampo('rh_jornada','jor_domintfim');
        $difi->dispForm             = 'col-2';
        $difi->valor                = isset($dados['jor_domintfim'])? $dados['jor_domintfim']: '';
        $difi->leitura               = $show;
        $this->jor_domintfim             = $difi->crInput();
        
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
        // $dados_fun = [
        //     'jor_id'           => $dados['jor_id'],
        //     'emp_id'           => $dados['emp_id'],
        //     'jor_nome'         => $dados['jor_nome'],
        //     'jor_inicio'         => $dados['jor_inicio'],
        //     'jor_final'         => $dados['jor_final'],
        // ];
        // debug($dados_fun,true);
        $salvar = $this->jornada->save($dados);
        if ($salvar) {
            $ret['erro'] = false;
            $ret['msg'] = 'Jornada gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->jornada->errors();
            $ret['msg'] = 'Não foi possível gravar a Jornada, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
