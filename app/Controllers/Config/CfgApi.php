<?php
namespace App\Controllers\Config;
use App\Controllers\BaseController;
use App\Models\Config\ConfigApiModel;

class CfgApi extends BaseController {
    public $data = [];
    public $permissao = '';
    public $apis;
    public $apiempresa;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->apis = new ConfigApiModel();
        
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
        $this->data['colunas'] = montaColunasLista($this->data, 'api_id');
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
        // if (!$apis = cache('apis')) {
            $campos = montaColunasCampos($this->data, 'api_id');
            $dados_apis = $this->apis->getApi();
            // debug($dados_apis); 
            $apis = [
                'data' => montaListaColunas($this->data, 'api_id', $dados_apis, $campos[1]),
            ];
            cache()->save('apis', $apis, 60000);
        // }

        echo json_encode($apis);
    }
    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $fields = $this->apis->defCampos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['api_id'];  
        $campos[0][1] = $fields['api_nome'];
        $campos[0][2] = $fields['api_descricao'];
        $campos[0][3] = $fields['api_url'];
        $campos[0][4] = $fields['api_login'];
        $campos[0][5] = $fields['api_usuario'];
        $campos[0][6] = $fields['api_acckey'];
        $campos[0][7] = $fields['api_tokenkey'];

        $this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        echo view('vw_edicao', $this->data);
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
		$dados_api = $this->apis->find($id);
        $fields = $this->apis->defCampos($dados_api);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['api_id'];  
        $campos[0][1] = $fields['api_nome'];
        $campos[0][2] = $fields['api_descricao'];
        $campos[0][3] = $fields['api_url'];
        $campos[0][4] = $fields['api_login'];
        $campos[0][5] = $fields['api_usuario'];
        $campos[0][6] = $fields['api_acckey'];
        $campos[0][7] = $fields['api_tokenkey'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_api', $id);

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
            $this->apis->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Api Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a API, Verifique!<br><br>';
            $ret['msg'] .= 'Esta API possui relacionamentos em outros cadastros!'.$e->getMessage();
        }
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
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['api_id'] == '') {
            if ($this->apis->save($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->apis->errors();
                $ret['erro'] = true;
            }
        } else {
            if ($this->apis->update($postado['api_id'], $postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->apis->errors();
                $ret['erro'] = true;
            }
        }

        if ($ret['erro']) {
            $ret['msg']  = 'Não foi possível gravar o Api, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Api gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }
}
