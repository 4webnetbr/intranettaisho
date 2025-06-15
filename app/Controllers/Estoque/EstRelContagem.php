<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;


class EstRelContagem extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $produto;
    public $contagem;
    public $entrada;
    public $saida;


	public function __construct(){
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->empresa    = new  ConfigEmpresaModel();
        $this->produto    = new  EstoquProdutoModel();
        $this->contagem   = new EstoquContagemModel();
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }
 
    public function index()
    {
        $this->def_campos();
        $campos[0] = $this->dash_empresa;
        $campos[1] = $this->dash_deposito;
        $campos[2] = $this->dash_contagem;

        $colunas = ['cta_id', 'Data', 'Grupo', 'Produto','Quantia','Und',''];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'relcontagem';  
        $this->data['campos']     	= $campos;  
        return view('vw_relcontagem', $this->data);
    }
    
    public function def_campos()
    {
        $empresas = explode(',',session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';        
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_contagem()';
        $emp->dispForm              = '3col';
        $emp->largura               = 40;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crSelect();
        
        $depos = [];
        $deposito       = new EstoquDepositoModel();
        $dados_dep      = $deposito->getDeposito(false,$empresas);
        $depos = array_column($dados_dep, 'dep_nome', 'dep_id');

        $dep                        = new MyCampo();
        $dep->nome                  = 'deposito'; 
        $dep->id                    = 'deposito';
        $dep->label = $dep->place   = 'Depósito(s)';        
        $dep->valor = $dep->selecionado = '';
        $dep->obrigatorio           = true;
        $dep->opcoes                = $depos;
        $dep->largura               = 40;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'empresa';
        $dep->dispForm              = '3col';
        $dep->funcChan              = 'carrega_contagem()';
        $this->dash_deposito        = $dep->crDepende();

        $cont                        = new MyCampo();
        $cont->nome                  = 'contagem'; 
        $cont->id                    = 'contagem';
        $cont->label = $cont->place   = 'Contagens(s)';        
        $cont->valor = $cont->selecionado = '';
        $cont->obrigatorio           = true;
        $cont->largura               = 40;
        $cont->urlbusca              = base_url('buscas/busca_contagem_deposito');
        $cont->pai                   = 'deposito';
        $cont->opcoes                = [];
        $cont->dispForm              = '3col';
        $cont->funcChan              = 'carrega_contagem()';
        $this->dash_contagem        = $cont->crDepende();
        
    }

	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getVar();
		// debug($filtro, false);
		$conta_id       = $filtro['contagem'];

        $ret = [];

        $contag       = $this->contagem->getContagem($conta_id);
        // debug($saidas);
        // debug(count($saidas));
        $prods = [];
        for($p=0;$p<count($contag);$p++){
            $prod = $contag[$p];
            $prods[$p][0] = $prod['cta_id'];
            $prods[$p][1] = dataDbToBr($prod['cta_data']);
            $prods[$p][2] = $prod['gru_nome'];
            $prods[$p][3] = $prod['pro_nome'];
            $qtia = formataQuantia($prod['ctp_quantia']);
            $prods[$p][4] = $qtia['qtia'];
            $prods[$p][5] = $prod['und_sigla'];
            $prods[$p][6] = '';
        }
        // debug(count($prods));
        // $ret['saidas'] = [];
        $ret['data'] = $prods;
        echo json_encode($ret);
    }


}
