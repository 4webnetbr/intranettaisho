<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;


class EstRelMovimento extends BaseController
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

        $colunas = ['Código', 'Produto','Und','Ped Data','Ped Quantia','Compra Data','Compra Quantia','Compra Valor','Compra Total','Entrada Data','Entrada Quantia','Entrada Valor','Entrada Total'];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'relmovimentos';  
        $this->data['campos']     	= $campos;  
        return view('vw_relsaida', $this->data);
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
        $periodo->funcChan    = 'carrega_movimentos()';
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
        $emp->funcChan              = 'carrega_movimentos()';
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
        $prods = [];
        $movimentos       = $this->produto->getRelMovimento($empresa, $inicio, $fim);
        // debug($movimentos);
        // debug(count($movimentos));
        for($p=0;$p<count($movimentos);$p++){
            $prod = $movimentos[$p];
            $prods[$p][0] = $prod['pro_id'];
            $prods[$p][count($prods[$p])] = $prod['pro_nome'];
            $prods[$p][count($prods[$p])] = $prod['und_sigla'];
            $prods[$p][count($prods[$p])] = dataDbToBr($prod['ped_data']);
            $qtiap = '';
            if($prod['ped_qtia'] != null){
                $qtiap = formataQuantia($prod['ped_qtia'])['qtia'];
            }
            $prods[$p][count($prods[$p])] = $qtiap;
            $prods[$p][count($prods[$p])] = dataDbToBr($prod['com_data']);
            $qtiac = '';
            if($prod['cop_quantia'] != null){
                $qtiac = formataQuantia($prod['cop_quantia'])['qtia'];
            }
            $prods[$p][count($prods[$p])] = $qtiac;
            $prods[$p][count($prods[$p])] = floatToMoeda($prod['cop_valor']);
            $prods[$p][count($prods[$p])] = floatToMoeda($prod['cop_total']);
            $prods[$p][count($prods[$p])] = dataDbToBr($prod['ent_data']);
            $qtiae = '';
            if($prod['enp_quantia'] != null){
                $qtiae = formataQuantia($prod['enp_quantia'])['qtia'];
            }
            $prods[$p][count($prods[$p])] = $qtiae;
            $prods[$p][count($prods[$p])] = floatToMoeda($prod['enp_valor']);
            $prods[$p][count($prods[$p])] = floatToMoeda($prod['enp_total']);
            // $prods[$p][count($prods[$p])] = '';
        }
        // debug(count($prods));
        // $ret['movimentos'] = [];
        $ret['data'] = $prods;
        echo json_encode($ret);
    }


}
