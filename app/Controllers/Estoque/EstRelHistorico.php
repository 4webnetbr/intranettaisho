<?php namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use Decimal\Decimal;

class EstRelHistorico extends BaseController
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
        $campos[0] = $this->periodo;
        $campos[1] = $this->dash_empresa;
        $campos[2] = $this->dash_deposito;
        $campos[3] = $this->dash_produto;

        $colunas = ['Ord','Ord','Código', 'Data Ref','Contagem','Entrada','Saída','Saldo','Und','Data Efet',''];

        $this->data['cols']     	= $colunas;  
        $this->data['nome']     	= 'relhistorico';  
        $this->data['campos']     	= $campos;  
        return view('vw_relhistorico', $this->data);
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
        $periodo->funcChan    = 'carrega_historico()';
        $periodo->dispForm    = '4col';
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
        $emp->funcChan              = 'carrega_historico()';
        $emp->dispForm              = '4col';
        $emp->largura               = 30;
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
        $dep->largura               = 30;
        $dep->urlbusca              = base_url('buscas/busca_deposito_empresa');
        $dep->pai                   = 'empresa';
        $dep->dispForm              = '4col';
        $dep->funcChan              = 'carrega_historico()';
        $this->dash_deposito        = $dep->crDepende();
        
        $prods = [];
        $produtos       = $this->produto->getProduto();
        $prods = array_column($produtos, 'pro_nome', 'pro_id');
        $pro                        = new MyCampo();
        $pro->nome                  = 'produto'; 
        $pro->id                    = 'produto';
        $pro->label = $dep->place   = 'Produto(s)';        
        $pro->valor = $pro->selecionado = '';
        $pro->opcoes                = $prods;
        $pro->largura               = 40;
        $pro->obrigatorio           = true;
        // $pro->urlbusca              = base_url('buscas/busca_produto');
        $pro->dispForm              = '4col';
        $pro->funcChan              = 'carrega_historico()';
        $this->dash_produto         = $pro->crSelect();

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
		$deposito       = $filtro['deposito'];
		$produto        = $filtro['produto'];

        $ret = [];
        $prods = [];
        if($deposito != null){
            $produtos       = $this->produto->getRelHistorico($empresa, $deposito, $produto, $inicio, $fim);
            $ult_conta   = 0;
            $tot_entra   = 0;
            $tot_saida   = 0;
            $unid = '';
            $ctp = 0;
            for($p=0;$p<count($produtos);$p++){
                $prod = $produtos[$p];
                $unid = $prod['und_sigla'];
                if($prod['tipo'] == 'Contagem' && $ctp > 0){
                    $saldo = ($ult_conta + $tot_entra) - $tot_saida;
                    $conta = formataQuantia(isset($ult_conta)?$ult_conta:0);
                    $entra = formataQuantia(isset($tot_entra)?$tot_entra:0);
                    $saida = formataQuantia(isset($tot_saida)?$tot_saida:0);
                    $saldo = formataQuantia(isset($saldo)?$saldo:0);
                    $prods[$ctp][0] = $ctp;
                    $prods[$ctp][1] = '<div class="m-0 fst-italic fw-bolder">'.$ctp.'</div>';
                    $prods[$ctp][2] = '<h6 class="m-0 fst-italic fw-bolder">Total antes da Contagem</h6>';
                    $prods[$ctp][3] = '';
                    // $prods[$ctp][3] = dataDbToBr($prod['data_ref']);
                    $prods[$ctp][4] = '<div class="m-0 fst-italic fw-bolder">'.$conta['qtia'].'</div>';
                    $prods[$ctp][5] = '<div class="m-0 fst-italic fw-bolder">'.$entra['qtia'].'</div>';
                    $prods[$ctp][6] = '<div class="m-0 fst-italic fw-bolder">'.$saida['qtia'].'</div>';
                    $prods[$ctp][7] = '<div class="m-0 fst-italic fw-bolder">'.$saldo['qtia'].'</div>';
                    $prods[$ctp][8] = '<div class="m-0 fst-italic fw-bolder">'.$unid.'</div>';
                    $prods[$ctp][9] = '';
                    $prods[$ctp][10] = '';
                    // $prods[$ctp][10] = '';
                    $tot_entra = 0;
                    $tot_saida = 0;
                    $ctp++;
                }
                $prods[$ctp][0] = $ctp;
                $prods[$ctp][1] = $ctp;
                $prods[$ctp][2] = $prod['cod_marca'];
                $prods[$ctp][3] = dataDbToBr($prod['data_ref']);
                // $frc_quant = (int) substr(strpbrk($prod['quantia'], '.,'), 1);
                if($prod['tipo'] == 'Contagem'){
                    $qtia = formataQuantia(isset($prod['quantia'])?$prod['quantia']:0);
                    $prods[$ctp][2] = 'CONTAGEM';
                    $prods[$ctp][4] = $qtia['qtia'];
                    $prods[$ctp][5] = '';
                    $prods[$ctp][6] = '';
                    $ult_conta = $prod['quantia'];
                } else if($prod['tipo'] == 'Entrada'){
                    $qtia = formataQuantia(isset($prod['quantia'])?$prod['quantia']:0);
                    $prods[$ctp][4] = '';
                    $prods[$ctp][5] = $qtia['qtia'];
                    $prods[$ctp][6] = '';
                    $tot_entra += $prod['quantia'];
                } else if($prod['tipo'] == 'Saída'){
                    $qtia = formataQuantia(isset($prod['quantia'])?$prod['quantia']:0);
                    $prods[$ctp][4] = '';
                    $prods[$ctp][5] = '';
                    $prods[$ctp][6] = $qtia['qtia'];
                    $tot_saida += $prod['quantia'];
                }
                $saldo = ($ult_conta + $tot_entra) - $tot_saida;
                // $frc_saldo = (int) substr(strpbrk($saldo, '.,'), 1);
                // debug('Saldo '.$saldo);
                $qtia = formataQuantia($saldo);
                // debug('Retornou '.$qtia['qtia']);
                $prods[$ctp][7] = $qtia['qtia'];
                $prods[$ctp][8] = $unid;
                $prods[$ctp][9] = dataDbToBr($prod['datahora']);
                $prods[$ctp][10] = '';
                $ctp++;
            }
        }
        $saldo = ($ult_conta + $tot_entra) - $tot_saida;
        $conta = formataQuantia(isset($ult_conta)?$ult_conta:0);
        $entra = formataQuantia(isset($tot_entra)?$tot_entra:0);
        $saida = formataQuantia(isset($tot_saida)?$tot_saida:0);
        $qsald = formataQuantia(isset($saldo)?$saldo:0);
        $prods[$ctp][0] = $ctp;
        $prods[$ctp][1] = '<div class="m-0 fst-italic fw-bolder">'.$ctp.'</div>';
        $prods[$ctp][2] = '<h5 class="m-0 fst-italic fw-bolder">TOTAL</h5>';
        $prods[$ctp][3] = '';
        $prods[$ctp][4] = '<div class="m-0 fst-italic fw-bolder">'.$conta['qtia'].'</div>';
        $prods[$ctp][5] = '<div class="m-0 fst-italic fw-bolder">'.$entra['qtia'].'</div>';
        $prods[$ctp][6] = '<div class="m-0 fst-italic fw-bolder">'.$saida['qtia'].'</div>';
        $prods[$ctp][7] = '<div class="m-0 fst-italic fw-bolder">'.$qsald['qtia'].'</div>';
        $prods[$ctp][8] = '<div class="m-0 fst-italic fw-bolder">'.$unid.'</div>';
        $prods[$ctp][9] = '';
        $prods[$ctp][10] = '';
        sort($prods);
        $ret['data'] = $prods;
        // debug($ret['data']);
        echo json_encode($ret);
    }


}
