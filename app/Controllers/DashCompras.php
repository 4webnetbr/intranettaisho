<?php namespace App\Controllers;

use App\Libraries\MyCampo;
use App\Controllers\BaseController;
use App\Services\DashComprasService;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquPedidoModel;

class DashCompras extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $dash_empresa;
    public $dash_periodo;
    public $produto;
    public $solicitacao;
    public $compras;
    public $entrada;
    public $saida;
    protected $service;


	public function __construct(){
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao        = $this->data['permissao'];
        $this->empresa          = new  ConfigEmpresaModel();
        $this->solicitacao      = new EstoquPedidoModel();
        $this->compras          = new EstoquCompraModel();
        $this->entrada          = new EstoquEntradaModel();
        $this->saida            = new EstoquSaidaModel();
        $this->service          = new DashComprasService();

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
        $campos[] = $this->dash_periodo;
        $campos[] = $this->dash_empresa;
        
        $this->data['nome']     	= 'dashcompras';  
        $this->data['campos']     	= $campos;  
        return view('vw_dashcompras.php', $this->data);
    }
    
    public function def_campos()
    {
        
        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo'; 
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->funcChan    = 'carrega_dash_compras()';
        $periodo->dispForm    = '3col';
        $this->dash_periodo    = $periodo->crDaterange();
        
        $empresas = explode(',',session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';        
        $emp->selecionado           = $empresas;
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_dash_compras()';
        $emp->dispForm              = '2col';
        $emp->largura               = 50;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crMultiple();
        
    }

    public function busca_dados()
    {
        $post = $this->request->getPost();
        $ret  = $this->service->buscarDadosCompras(
            dataBrToDb($post['inicio']),
            dataBrToDb($post['fim']),
            $post['empresa'],
        );
        return $ret;
    }

    public function busca_dados2()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getVar();
		// debug($filtro, false);
		$empresa        = [$filtro['empresa']];
		$deposito       = $filtro['deposito'];

        $ret = [];

        $produtos       = $this->produto->getProduto();
        $prods = [];
        $ct = 0;
        for($p=0;$p<count($produtos);$p++){
            $prod = $produtos[$p];
            $prods[$ct][0] = $prod['pro_id'];
            $prods[$ct][1] = $prod['pro_nome'];
            $prods[$ct][2] = '';
            $prods[$ct][3] = 0;
            $prods[$ct][4] = 0;
            $prods[$ct][5] = 0;
            $prods[$ct][6] = 0;
            $prods[$ct][7] = $prod['und_sigla'];
            $temproduto = false;
            //busca contagem
            $cont = $this->contagem->getTotalContagem($prod['pro_id'], $deposito);
            // debug($cont);
            $data_base = false;
            if(count($cont) > 0 ){
                $data_base =  $cont[0]['data_contagem'];
                $prods[$ct][2] = dataDbToBr($data_base);
                $prods[$ct][3] = $cont[0]['qtia_contagem'];
                $temproduto = true;
            }
            // debug($data_base);
            $entra = $this->entrada->getTotalEntrada($prod['pro_id'], $deposito, $data_base);
            if($entra[0]['tot_entrada'] > 0 ){
                $prods[$ct][4] = $entra[0]['tot_entrada'];
                $temproduto = true;
            }
            $saida = $this->saida->getTotalSaida($prod['pro_id'], $deposito, $data_base);
            if($saida[0]['tot_saida'] > 0 ){
                $prods[$ct][5] = $saida[0]['tot_saida'];
                $temproduto = true;
            }
            if($temproduto){
                $prods[$ct][6] = $prods[$ct][3] +  $prods[$ct][4] - $prods[$ct][5];
                $prods[$ct][3] = formataQuantia($prods[$ct][3],3)['qtia'];
                $prods[$ct][4] = formataQuantia($prods[$ct][4],3)['qtia'];
                $prods[$ct][5] = formataQuantia($prods[$ct][5],3)['qtia'];
                $prods[$ct][6] = formataQuantia($prods[$ct][6],3)['qtia'];

                // $prods[$ct][3] = floor($prods[$ct][3]) != $prods[$ct][3]?number_format(floatval($prods[$ct][3]),3,',',''):intval($prods[$ct][3]);
                // $prods[$ct][4] = floor($prods[$ct][4]) != $prods[$ct][4]?number_format(floatval($prods[$ct][4]),3,',',''):intval($prods[$ct][4]);
                // $prods[$ct][5] = floor($prods[$ct][5]) != $prods[$ct][5]?number_format(floatval($prods[$ct][5]),3,',',''):intval($prods[$ct][5]);
                // $prods[$ct][6] = floor($prods[$ct][6]) != $prods[$ct][6]?number_format(floatval($prods[$ct][6]),3,',',''):intval($prods[$ct][6]);
                // $prods[$ct][3] = floor($prods[$ct][3]) != $prods[$ct][3]?floatval($prods[$ct][3]):intval($prods[$ct][3]);
                // $prods[$ct][4] = floor($prods[$ct][4]) != $prods[$ct][4]?floatval($prods[$ct][4]):intval($prods[$ct][4]);
                // $prods[$ct][5] = floor($prods[$ct][5]) != $prods[$ct][5]?floatval($prods[$ct][5]):intval($prods[$ct][5]);
                // $prods[$ct][6] = floor($prods[$ct][6]) != $prods[$ct][6]?floatval($prods[$ct][6]):intval($prods[$ct][6]);
                $ct++;
            }
        }
        if(!$temproduto){
            unset($prods[$ct]);
        }
        $ret['data'] = $prods;
        echo json_encode($ret);
    }


}
