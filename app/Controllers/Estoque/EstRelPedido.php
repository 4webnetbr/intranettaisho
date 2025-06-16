<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquPedidoModel;


class EstRelPedido extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $produto;
    public $pedido;
 

	public function __construct(){
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->empresa    = new  ConfigEmpresaModel();
        // $this->produto    = new  EstoquProdutoModel();
        $this->pedido       = new EstoquPedidoModel();
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

        $colunas = ['Id','Empresa','Data','Produto','Quantia','Und',''];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'relpedidos';  
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
        $periodo->funcChan    = 'carrega_pedidos()';
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
        $emp->funcChan              = 'carrega_pedidos()';
        $emp->dispForm              = '3col';
        $emp->largura               = 40;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crSelect();
        
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

        $ret = [];

        $pedidos       = $this->pedido->getRelPedido($empresa, $inicio, $fim);
        // debug($pedidos);
        $prods = [];
        for($p=0;$p<count($pedidos);$p++){
            $prod = $pedidos[$p];
            $prods[$p][0] = $prod['ped_id'];
            $prods[$p][1] = $prod['emp_apelido'];
            $prods[$p][2] = dataDbToBr($prod['ped_data']);
            $prods[$p][3] = $prod['pro_nome'];
            $prods[$p][4] = ($prod['und_sigla_compra']=='und'||$prod['und_sigla_compra']=='cx')?intval($prod['ped_qtia']):floatval($prod['ped_qtia']);
            $prods[$p][5] = $prod['und_sigla_compra'];
            // $prods[$p][6] = floatToMoeda($prod['cop_valor']);
            // $prods[$p][7] = floatToMoeda($prod['cop_total']);
            $prods[$p][6] = '';
        }
        // debug(count($prods));
        // $ret['pedidos'] = [];
        $ret['data'] = $prods;
        echo json_encode($ret);
    }


}
