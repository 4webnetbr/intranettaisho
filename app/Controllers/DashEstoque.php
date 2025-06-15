<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;


class DashEstoque extends BaseController
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
        $this->contagem    = new  EstoquContagemModel();
        $this->entrada     = new EstoquEntradaModel();
        $this->saida       = new EstoquSaidaModel();
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

        $colunas = ['id','Produto','Ult Contagem','Contagem','Entrada','Saída','Saldo','Und'];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'saldoestoque';  
        $this->data['campos']     	= $campos;  
        return view('vw_dashestoque.php', $this->data);
    }
    
    public function def_campos(){
        
        $empresas = explode(',',session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa'; 
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';        
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = '2col';
        $emp->largura               = 50;
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
        $dep->largura               = 50;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'empresa';
        $dep->dispForm              = '2col';
        $dep->funcChan              = 'carrega_saldos()';
        $this->dash_deposito        = $dep->crDepende();
        
    }
	public function busca_dados()
	{
        // debug($dados_emp);
		$filtro  		= $this->request->getVar();
		// debug($filtro, false);
		$empresa        = [$filtro['empresa']];
		$deposito       = $filtro['deposito'];

        $ret = [];
        $saldos       = $this->produto->getSaldos($deposito);
        // debug($saldos);
        $ct = 0;
        $prods =[];
        for($s=0;$s<count($saldos);$s++){
            $prod = $saldos[$s];
            if($prod['qtia_ultima_contagem'] + $prod['total_entradas'] + $prod['total_saidas'] > 0 ){
                $prods[$ct][0] = $prod['pro_id'];
                $prods[$ct][1] = $prod['pro_nome'];
                $prods[$ct][2] = dataDbToBr($prod['data_ultima_contagem']);
                $prods[$ct][3] = formataQuantia($prod['qtia_ultima_contagem'])['qtia'];
                $prods[$ct][4] = formataQuantia($prod['total_entradas'])['qtia'];
                $prods[$ct][5] = formataQuantia($prod['total_saidas'])['qtia'];
                $prods[$ct][6] = formataQuantia($prod['saldo'])['qtia'];
                $prods[$ct][7] = $prod['und_sigla'];
                $ct++;
            }
        }
        $ret['data'] = $prods;
        echo json_encode($ret);
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
