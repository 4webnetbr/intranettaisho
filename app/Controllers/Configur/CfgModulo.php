<?php 

namespace App\Controllers\Configur;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigModuloModel;
use DatabaseException;

Class CfgModulo extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $modulo;

    public function __construct()
    {
		$this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
		$this->modulo 		= new ConfigModuloModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->data['colunas'] = montaColunasLista($this->data, 'mod_id,');
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
        $dados_modulo = $this->modulo->getModulo();
        $modulos = [
            'data' => montaListaColunas($this->data, 'mod_id', $dados_modulo, 'mod_nome'),
        ];

        echo json_encode($modulos);
    }

    public function add($modal = false)
    {
        $this->defCampos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->mod_id;
        $campos[0][1] = $this->mod_nome;
        $campos[0][2] = $this->mod_icon;
        $campos[0][3] = $this->mod_ativo;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            // $this->data['destino']    = 'store/modal';
            echo view('vw_edicao_modal', $this->data);
        }
    }

    public function edit($id){
		$dados_modulo = $this->modulo->find($id);
		$this->defCampos($dados_modulo);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $this->mod_id; 
        $campos[0][1] = $this->mod_nome;
        $campos[0][2] = $this->mod_icon;
        $campos[0][3] = $this->mod_ativo;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_modulo', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->modulo->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Módulo Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Módulo, Verifique!<br><br>';
            $ret['msg'] .= 'Este Módulo possui relacionamentos em outros cadastros!';
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'mod_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'mod_ativo' => 'I'
            ];
        }
        $ret = [];
        try {
            $this->modulo->update($id, $dad_atin);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Módulo Alterado com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Módulo, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }

    public function defCampos($dados = false)
    {
        $mid            = new MyCampo('cfg_modulo', 'mod_id');
        $mid->valor     = (isset($dados['mod_id'])) ? $dados['mod_id'] : '';
        $this->mod_id   = $mid->crOculto();

        $nome           =  new MyCampo('cfg_modulo', 'mod_nome');
        $nome->valor    = (isset($dados['mod_nome'])) ? $dados['mod_nome'] : '';
        $this->mod_nome = $nome->crInput();

        $icon           =  new MyCampo('cfg_modulo', 'mod_icone');
        $icon->tipo     = 'icone';
        $icon->valor    = (isset($dados['mod_icone'])) ? $dados['mod_icone'] : '';
        $this->mod_icon = $icon->crInput();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ           = new MyCampo('cfg_modulo', 'mod_ativo');
        $ativ->valor    = (isset($dados['mod_ativo'])) ? $dados['mod_ativo'] : 'A';
        $ativ->selecionado    = $ativ->valor;
        $ativ->opcoes   = $opcat;
        $this->mod_ativo = $ativ->cr2opcoes();
    }

    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['mod_id'] == '') {
            if ($this->modulo->insert($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->modulo->errors();
                $ret['erro'] = true;
            }
        } else {
            if ($this->modulo->update($postado['mod_id'], $postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->modulo->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) {
            $ret['msg']  = 'Não foi possível gravar o Módulo, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Módulo gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }

}
