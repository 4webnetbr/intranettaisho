<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumTurnoModel;

class RhTurno extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $turno;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->turno       = new RechumTurnoModel(); 
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
        $this->data['colunas'] = montaColunasLista($this->data,'tur_id');
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

            $dados_turnos = $this->turno->getTurno();
            $turnos = [             
                'data' => montaListaColunas($this->data,'tur_id',$dados_turnos,'tur_nome'),
            ];
        // }
        echo json_encode($turnos);
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
        $campos[0][0] = $this->tur_id;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->tur_nome;
        $campos[0][3] = $this->tur_inicio;
        $campos[0][4] = $this->tur_final;

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
        $dados_turno = $this->turno->getTurno($id)[0];
        // debug($dados_turno);
        $this->def_campos($dados_turno, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->tur_id;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->tur_nome;
        $campos[0][3] = $this->tur_inicio;
        $campos[0][4] = $this->tur_final;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_turno', $id);

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
            $this->turno->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Turno Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Turno, Verifique!';
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
        $id = new MyCampo('rh_turno','tur_id');
        $id->valor = isset($dados['tur_id']) ? $dados['tur_id'] : '';
        $this->tur_id = $id->crOculto();

        $empresas           = explode(',',session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('rh_turno','emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id'])? $dados['emp_id']: '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $this->emp_id               = $emp->crSelect();

        $nome                       = new MyCampo('rh_turno','tur_nome');
        $nome->obrigatorio          = true;
        $nome->valor                = isset($dados['tur_nome'])? $dados['tur_nome']: '';
        $nome->largura               = 50;
        $nome->tamanho               = 50;
        $nome->leitura               = $show;
        $this->tur_nome             = $nome->crInput();

        $inic                       = new MyCampo('rh_turno','tur_inicio');
        $inic->obrigatorio          = true;
        $inic->valor                = isset($dados['tur_inicio'])? $dados['tur_inicio']: '';
        $inic->leitura               = $show;
        $this->tur_inicio             = $inic->crInput();

        $fina                       = new MyCampo('rh_turno','tur_final');
        $fina->obrigatorio          = true;
        $fina->valor                = isset($dados['tur_final'])? $dados['tur_final']: '';
        $fina->leitura               = $show;
        $this->tur_final             = $fina->crInput();
        
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
        $dados_fun = [
            'tur_id'           => $dados['tur_id'],
            'emp_id'           => $dados['emp_id'],
            'tur_nome'         => $dados['tur_nome'],
            'tur_inicio'         => $dados['tur_inicio'],
            'tur_final'         => $dados['tur_final'],
        ];
        // debug($dados_fun,true);
        $salvar = $this->turno->save($dados_fun);
        if ($salvar) {
            $ret['erro'] = false;
            $ret['msg'] = 'Função gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->turno->errors();
            $ret['msg'] = 'Não foi possível gravar a Função, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
