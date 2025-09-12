<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquFornecedorModel;


class EstRelCompra extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $produto;
    public $compra;
 

	public function __construct(){
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->empresa    = new  ConfigEmpresaModel();
        // $this->produto    = new  EstoquProdutoModel();
        $this->compra       = new EstoquCompraModel();
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
        $campos[0] = $this->periodo;
        $campos[1] = $this->dash_empresa;
        $campos[2] = $this->dash_fornecedor;

        $colunas = ['Id','Empresa - Fornecedor','N° Pedido','Data','Produto','Quantia','Und','R$ Unit','R$ Total','Usuário'];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'relcompras';  
        $this->data['campos']     	= $campos;  
        return view('vw_relcompra', $this->data);
    }
    
    public function def_campos()
    {

        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo'; 
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->tamanho     = 30;
        $periodo->funcChan    = 'carrega_compras()';
        $periodo->dispForm    = '3col';
        $this->periodo        = $periodo->crDaterange();
        
        $empresas = explode(',',session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';        
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_compras()';
        $emp->dispForm              = '3col';
        $emp->largura               = 40;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crSelect();
        
        $fornec = [];
        $fornecedor = new EstoquFornecedorModel();
        $dados_for = $fornecedor->getFornecedor();
        $fornec = array_column($dados_for, 'for_completo', 'for_id');
        asort($fornec);
        // debug($fornec);
        $forn                        = new MyCampo();
        $forn->nome                  = 'fornecedor'; 
        $forn->id                    = 'fornecedor';
        $forn->label = $forn->place   = 'Fornecedor(es)';        
        $forn->valor = $forn->selecionado = '';
        $forn->opcoes       = $fornec;
        $forn->dispForm              = '3col';
        $forn->largura      = 40;
        $forn->funcChan    = 'carrega_compras()';
        $this->dash_fornecedor       = $forn->crSelect();
        
    }

	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getVar();
		// debug($filtro, false);
		$inicio         = $filtro['inicio'];
		$fim            = $filtro['fim'];
        $inicio         = dataBrToDb($inicio);
        $fim            = dataBrToDb($fim);
		$empresa        = [$filtro['empresa']];
		$fornecedor     = $filtro['fornecedor'];

        $ret = [];

        $compras       = $this->compra->getRelCompra($fornecedor, $empresa, $inicio, $fim);
        $com_ids_assoc = array_column($compras, 'com_id');
        $log = buscaLogTabela('est_compra', $com_ids_assoc);
        // $this->data['edicao'] = false;
        foreach ($compras as &$com) {
            // Verificar se o log já está disponível para esse ana_id
            $com['com_usuario'] = $log[$com['com_id']]['usua_alterou'] ?? '';
        }
        // debug($compras);
        $prods = [];
        for($p=0;$p<count($compras);$p++){
            $prod = $compras[$p];
            $prods[$p][0] = $prod['com_id'];
            $prods[$p][1] = $prod['emp_apelido'].' - '.$prod['for_razao'];
            $prods[$p][2] = $prod['com_id'];
            $prods[$p][3] = dataDbToBr($prod['com_data']);
            $prods[$p][4] = $prod['pro_nome'];
            $prods[$p][5] = ($prod['und_sigla']=='und'||$prod['und_sigla']=='cx')?intval($prod['cop_quantia']):floatval($prod['cop_quantia']);
            $prods[$p][6] = $prod['und_sigla'];
            $prods[$p][7] = floatToMoeda($prod['cop_valor']);
            $prods[$p][8] = floatToMoeda($prod['cop_total']);
            $prods[$p][9] = $prod['com_usuario'];
        }
        // debug(count($prods));
        // $ret['compras'] = [];
        $ret['data'] = $prods;
        echo json_encode($ret);
    }


}
