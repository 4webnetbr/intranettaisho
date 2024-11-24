<<<<<<< HEAD
<?php 
namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\ContatoModel;
use App\Models\EnderecoModel;

class CfgEmpresa extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $contato;
    public $endereco;
    public $dicionario;
    public $common;
    /**
     * Construtor da Classe
=======
<?php

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Controllers\BuscasSapiens;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;

class CfgEmpresa extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $empresa;

    /**
     * Construtor da Tela
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
     * construct
     */
    public function __construct()
    {
<<<<<<< HEAD
        $this->data        = session()->getFlashdata('dados_tela');
        $this->permissao   = $this->data['permissao'];
        $this->empresa     = new ConfigEmpresaModel();
        // $this->contato        = new ContatoModel();
        // $this->endereco        = new EnderecoModel();
        $this->dicionario     = new ConfigDicDadosModel();
        $this->common        = new CommonModel();
=======
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->empresa     = new ConfigEmpresaModel();

>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    /**
     * Erro de Acesso
     * erro
     */
<<<<<<< HEAD
    function __erro()
=======
    public function __erro()
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
<<<<<<< HEAD
        $this->data['colunas'] = montaColunasLista($this->data,'emp_id');
        $this->data['url_lista'] = base_url($this->data['controler'].'/lista');
        echo view('vw_lista', $this->data);
    }

    /**
=======
        $this->data['colunas'] = montaColunasLista($this->data, 'emp_codfil,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        echo view('vw_lista', $this->data);
    }
    
        /**
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
<<<<<<< HEAD
        // if(!$empresas = cache('empresas')) {
            $empresa = 1;
            $dados_empresa = $this->empresa->getEmpresa(false, $empresa);
            $empresas = [             
                'data' => montaListaColunas($this->data,'emp_id',$dados_empresa,'emp_nome'),
            ];
            cache()->save('empresas', $empresas, 60000);
        // }
        echo json_encode($empresas);
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
        $campos[0][0] = $this->emp_id;
        $campos[0][1] = $this->emp_cnpj;
        $campos[0][2] = $this->emp_ie;
        $campos[0][3] = $this->emp_nome;
        $campos[0][4] = $this->emp_apelido;
        $campos[0][5] = $this->emp_codempresa;
        $campos[0][6] = $this->emp_codfilial;
        $campos[0][7] = $this->emp_cnae;
        $campos[0][8] = $this->emp_cnad;
        $campos[0][9] = $this->emp_obs;


        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
 
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
        $dados_empresa = $this->empresa->getEmpresa($id)[0];
        $this->def_campos($dados_empresa);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->emp_id;
        $campos[0][1] = $this->emp_cnpj;
        $campos[0][2] = $this->emp_ie;
        $campos[0][3] = $this->emp_nome;
        $campos[0][4] = $this->emp_apelido;
        $campos[0][5] = $this->emp_codempresa;
        $campos[0][6] = $this->emp_codfilial;
        $campos[0][7] = $this->emp_cnae;
        $campos[0][8] = $this->emp_cnad;
        $campos[0][9] = $this->emp_obs;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

=======

        if (!$empresas = cache('empresas')) {
            $this->integra();
        
            $campos = montaColunasCampos($this->data, 'emp_codfil');
            $dados_tela = $this->empresa->getEmpresa();
            $empresas = [
                'data' => montaListaColunas($this->data, 'emp_codfil', $dados_tela, $campos[1]),
            ];
            cache()->save('empresas', $empresas, 60000);
        }
        echo json_encode($empresas);

    }

    public function show($id){
		$dados_empresa = $this->empresa->find($id);
        $fields = $this->empresa->defCampos($dados_empresa, true);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $fields['emp_codfil']; 
        $campos[0][1] = $fields['emp_codemp'];
        $campos[0][2] = $fields['emp_nomfil'];
        $campos[0][3] = $fields['emp_sigfil'];
        $campos[0][4] = $fields['emp_numcgc'];
        $campos[0][5] = $fields['emp_insest'];
        
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
        $this->data['destino']    = 'store';
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_empresa', $id);

        echo view('vw_edicao', $this->data);
    }

    /**
<<<<<<< HEAD
     * Exclusão
     * delete
     *
     * @param mixed $id 
     * @return void
     */
    public function delete($id)
    {
        $this->empresa->delete($id);
        session()->setFlashdata('msg', 'Empresa Excluída com Sucesso');
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
        $id = new MyCampo('cfg_empresa','emp_id');
        // $id->tabela       = 'cfg_empresa';
        // $id->campo        = 'emp_id';
        $id->objeto       = 'oculto';
        $id->valor        = isset($dados['emp_id']) ? $dados['emp_id'] : '';
        $this->emp_id     = $id->crOculto();

        $cnpj =  new MyCampo('cfg_empresa','emp_cnpj');
        $cnpj->tipo           = 'cnpj';
        $cnpj->obrigatorio    = true;
        $cnpj->funcao_blur    = "pesquisaCNPJ(this.value, 10, 'emp' )";
        $cnpj->valor          = (isset($dados['emp_cnpj']))?$dados['emp_cnpj']:''; 
		$cnpj->dispForm       = '2col';
        $this->emp_cnpj         = $cnpj->crInput();

        $ie =  new MyCampo('cfg_empresa','emp_ie');
        $ie->valor          = (isset($dados['emp_ie']))?$dados['emp_ie']:''; 
		$ie->dispForm       = '2col';
        $this->emp_ie       = $ie->crInput();

		$nome = new MyCampo('cfg_empresa','emp_nome');
        $nome->obrigatorio = true;
		$nome->dispForm       = '2col';     
        $nome->valor       = isset($dados['emp_nome'])? $dados['emp_nome']: '';
        $this->emp_nome    = $nome->crInput();

		$apel = new MyCampo('cfg_empresa','emp_apelido');
        $apel->obrigatorio = true;
		$apel->dispForm       = '2col';
        $apel->valor       = isset($dados['emp_apelido'])? $dados['emp_apelido']: '';
        $this->emp_apelido    = $apel->crInput();

        $cnae =  new MyCampo('cfg_empresa','emp_cnae');
        $cnae->leitura        = true;
        $cnae->valor          = (isset($dados['emp_cnae']))?$dados['emp_cnae']:''; 
		$cnae->dispForm       = '2col';
        $this->emp_cnae       = $cnae->crInput();

        $cnad =  new MyCampo('cfg_empresa','emp_cnae_desc');
        $cnad->leitura        = true;
        $cnad->valor          = (isset($dados['emp_cnae_desc']))?$dados['emp_cnae_desc']:''; 
		$cnad->dispForm       = '2col';
        $this->emp_cnad       = $cnad->crInput();

		$code = new MyCampo('cfg_empresa','emp_codempresa');
        $code->obrigatorio = true;
		$code->dispForm       = '2col';
        $code->valor       = isset($dados['emp_codempresa'])? $dados['emp_codempresa']: '';
        $this->emp_codempresa    = $code->crInput();

		$codf = new MyCampo('cfg_empresa','emp_codfilial');
        $codf->obrigatorio = true;
		$codf->dispForm       = '2col';
        $codf->valor       = isset($dados['emp_codfilial'])? $dados['emp_codfilial']: '';
        $this->emp_codfilial    = $codf->crInput();
        
		$obs = new MyCampo('cfg_empresa','emp_obs');		// $obs->tipo_form   = 'inline';
        $obs->valor       = isset($dados['emp_obs'])? $dados['emp_obs']: '';
		$obs->dispForm       = '2col';
        $this->emp_obs    = $obs->crTexto();
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
        // debug($dados);
        $dados_emp = [
            'emp_id'            => $dados['emp_id'],
            'emp_nome'          => $dados['emp_nome'],
            'emp_apelido'       => $dados['emp_apelido'],
            'emp_cnpj'          => $dados['emp_cnpj'],
            'emp_ie'            => $dados['emp_ie'],
            'emp_status'        => 1,
            'emp_codempresa'    => $dados['emp_codempresa'],
            'emp_codfilial'     => $dados['emp_codfilial'],
            'emp_cnae'          => $dados['emp_cnae'],
            'emp_cnae_desc'     => $dados['emp_cnae_desc'],
            'emp_obs'           => $dados['emp_obs'],
        ];
        // debug($dados_emp);
        if ($this->empresa->save($dados_emp)) {
            if($dados['emp_id'] != ''){
                $emp_id = $dados['emp_id'];
            } else {
                $emp_id = $this->empresa->getInsertID();
            }
            cache()->clean();
            $ret['erro'] = false;
            $ret['msg'] = 'Empresa gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
            $ret['urledit'] = site_url($this->data['controler'] . '/edit/' . $emp_id);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Empresa, Verifique!';
            $ret['msg'] .= $this->empresa->getLastQuery();
        }
        echo json_encode($ret);
    }
}
?>

=======
     * integra
     */
    public function integra()
    {
        $busca = new BuscasSapiens();
        $r_emps = $busca->buscaEmpresas();
        $empss = [];
        for ($e = 0; $e < count($r_emps); $e++) {
            $emp = $r_emps[$e];
            $empss['emp_codemp'] = $emp->codEmp;
            $empss['emp_codfil'] = $emp->codFil;
            $empss['emp_nomfil'] = $emp->nomFil;
            $empss['emp_sigfil'] = $emp->sigFil;
            $empss['emp_numcgc'] = $emp->numCgc;
            $empss['emp_insest'] = $emp->insEst;
            $this->empresa->save($empss);
        }
    }
}
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
