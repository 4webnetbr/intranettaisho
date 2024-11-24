<?php
namespace App\Controllers\Estoque;
use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstCompra extends BaseController {
    public $data = [];
    public $permissao = '';
    public $compra;
    public $common;
    public $empresa;
    public $pedido;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->compra = new EstoquCompraModel();
        $this->common    = new CommonModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->pedido       = new EstoquPedidoModel();
        
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
        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']     	= 'compra';  
        $this->data['colunas']      = montaColunasLista($this->data,'com_id');
        $this->data['url_lista']    = base_url($this->data['controler'].'/lista');
        $this->data['campos']     	= $campos;  
        $this->data['script'] = "<script>carrega_lista('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1){
        
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
        $emp->dispForm              = 'linha';
        $emp->largura               = 50;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        if($tipo == 1){
            $emp->funcChan              = "carrega_lista(this,'EstCompra/lista','compra')";
        } else {
            $emp->funcChan              = "atualizaProdutosCompra()";
        }

        $this->dash_empresa         = $emp->crSelect();
    }
    
    public function atualizaProdutosCompra(){
        $dados = $this->request->getPost();
        $empresa = $dados['emp_id'];
        $campos = $this->montalistaProdutos(false, $empresa);
        $ret['campos'] = $campos;
        echo json_encode($ret);
    }

    /**
    * Listagem
    * lista
    *
    * @return void
    */
    public function lista()
    {
        $param = $_REQUEST['param'];
        if($param == 'undefined'){
            $param = [0];
        } else {
            $param = [$param];
        }
        // $campos = montaColunasCampos($this->data, 'com_id');
        // debug($campos, true);
        $dados_compr = $this->compra->getCompra(false, $param);
        for ($dc=0; $dc < count($dados_compr) ; $dc++) { 
            // $dados_compr[$dc]['d'] = '';
            $com = $dados_compr[$dc];
            $log = buscaLog('est_compra', $com['com_id']);
            $dados_compr[$dc]['com_usuario'] = $log['usua_alterou'];
            $qtia = formataQuantia(isset($dados_compr[$dc]['cop_quantia'])?$dados_compr[$dc]['cop_quantia']:0);
            $dados_compr[$dc]['cop_quantia'] = $qtia['qtia'];
        }
        $compr = [
            'data' => montaListaColunas($this->data, 'com_id', $dados_compr, 'pro_nome'),
        ];
            // cache()->save('compr', $compr, 60000);
        // }

        echo json_encode($compr);
    }


    
    /**
    * Inclusão
    * add
    *
    * @return void
    */
    public function add()
    {
        $this->def_campos_lista(2);
        $secao[0] = 'Produtos Pedidos';
        $campos[0][0] = $this->dash_empresa;

        $campos[0][1] = "<div id='produtosCompra' class='col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";

        $campospro = $this->montalistaProdutos(false);

        $campost[0] = array_merge($campos[0], $campospro[0]);

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campost;
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);
        
    }

    public function montalistaProdutos($dados_ped = false, $empresa = false){
        if(!$empresa){
            $empresas = explode(',',session()->get('usu_empresa'));
        } else {
            $empresas[0] = $empresa;
        }
        $compra = false;
        $produt = false;
        if($dados_ped){
            $compra = $dados_ped['com_id'];
            $produt = $dados_ped['pro_id'];
            $produtos =  $this->compra->getCompraProd($compra, $produt);
        } else {
            $produtos =  $this->pedido->getPedidoProd(false, false, $empresas[0]);
        }
        // debug($produtos);
        
        $cabecalho = "<div class='row col-12 bg-primary'>";
        $cabecalho .= "<div class='col-6 text-center float-start bg-primary text-white'><h5>Fornecedor</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Entrega</h5></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white'><h5>Qtia</h5></div>";
        $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white'><h5>Custo</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Total</h5></div>";
        $cabecalho .= "</div>";
        $campospr[0]  = [];
        $grupo = '';
        $campospr[0][count($campospr[0])] = "<div class='bg-primary p-2 '>";
        $campospr[0][count($campospr[0])] = "<h4 class='text-white'>Produtos Pedidos</h4>";
        $campospr[0][count($campospr[0])] = "</div>";
        $campospr[0][count($campospr[0])] = $cabecalho;
        $campospr[0][count($campospr[0])] = "<div id='Produtos' class='col-12 overflow-y-auto' style='max-height:59vh;height:59vh'>";

        $count = 0;
        for($p=0;$p<count($produtos);$p++){            
            $this->def_campos_prod($produtos[$p], $p);
            $campospr[0][count($campospr[0])] = "<div class='row col-12 border border-2 ".(++$count%2 ? "odd" : "even")."'>";
            $campospr[0][count($campospr[0])] = "<div class='row col-12 text-start float-start'>";
            $campospr[0][count($campospr[0])] = "<div class='col-8 text-start float-start'>";
            $campospr[0][count($campospr[0])] = $this->pro_nome;
            $campospr[0][count($campospr[0])] = $this->ped_id;
            $campospr[0][count($campospr[0])] = $this->pro_id;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->ped_qtia;
            $campospr[0][count($campospr[0])] = $this->und_id;
            $campospr[0][count($campospr[0])] = $this->und_sigla;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='row col-12 text-start float-start'>";
            $campospr[0][count($campospr[0])] = "<div class='col-6 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->com_id;
            $campospr[0][count($campospr[0])] = $this->cop_id;
            $campospr[0][count($campospr[0])] = $this->for_id;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->com_previsao;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-1 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->cop_quantia;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-1 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->cop_valor;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-end d-block'>";
            $campospr[0][count($campospr[0])] = $this->cop_total;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "</div>";
        }
        $campospr[0][count($campospr[0])] = "</div>";

        return $campospr;
    }


    
    /**
    * Show
    * show
    *
    * @param mixed $id 
    * @return void
    */
    public function show($id)
    {
        $this->edit($id, true);
    }

    /**
    * Edição
    * edit
    *
    * @param mixed $id 
    * @return void
    */
    public function edit($id, $show = false)
    {
        $dados_com = $this->compra->getCompra($id)[0];
        // debug($dados_ped, true);
        $this->def_campos_lista(2);
        $secao[0] = 'Informe a Quantia do Produto';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][1] = "<div id='produtosPed' class='accordion col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";
        
        $campospro = $this->montalistaProdutos($dados_com);

        $campost[0] = array_merge($campos[0], $campospro[0]);

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campost;
        $this->data['destino'] = '';
        echo view('vw_edicao', $this->data);
        
    }

    /**
    * Edição
    * edit
    *
    * @param mixed $id 
    * @return void
    */
    public function edit2($id, $show = false)
    {
        $dados_com = $this->compra->getCompra($id)[0];
        // debug($dados_com);
        $this->def_campos($dados_com, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->com_id;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->for_id;
        $campos[0][3] = $this->com_data;
        $campos[0][4] = $this->com_previsao;

        $secao[1] = 'Produtos';
        $displ[1] = 'tabela';
        $dados_cop = $this->compra->getCompraProd($id);
        // debug($dados_cop);
        if (count($dados_cop) > 0) {
            for ($c = 0; $c < count($dados_cop); $c++) {
                $this->def_campos_prod($dados_cop[$c], $c, $show);
                $campos[1][$c] = [];
                $campos[1][$c][count($campos[1][$c])] = $this->cop_id;  
                $campos[1][$c][count($campos[1][$c])] = $this->pro_id;
                $campos[1][$c][count($campos[1][$c])] = $this->und_id;
                $campos[1][$c][count($campos[1][$c])] = $this->cop_quantia;
                $campos[1][$c][count($campos[1][$c])] = $this->cop_valor;
                $campos[1][$c][count($campos[1][$c])] = $this->cop_total;
                if($show){
                    $campos[1][$c][count($campos[1][$c])] = '';
                    $campos[1][$c][count($campos[1][$c])] = '';
                } else {
                    $campos[1][$c][count($campos[1][$c])] = $this->bt_add;
                    $campos[1][$c][count($campos[1][$c])] = $this->bt_del;
                }
            }
        } else {
            $this->def_campos_cont(false, 0, $show);
            $campos[1][0] = [];
            $campos[1][0][count($campos[1][0])] = $this->cop_id;  
            $campos[1][0][count($campos[1][0])] = $this->pro_id;
            $campos[1][0][count($campos[1][0])] = $this->und_id;
            $campos[1][0][count($campos[1][0])] = $this->cop_quantia;
            $campos[1][0][count($campos[1][0])] = $this->cop_valor;
            $campos[1][0][count($campos[1][0])] = $this->cop_total;
            if($show){
                $campos[1][0][count($campos[1][0])] = '';
                $campos[1][0][count($campos[1][0])] = '';
            } else {
                $campos[1][0][count($campos[1][0])] = $this->bt_add;
                $campos[1][0][count($campos[1][0])] = $this->bt_del;
            }
    }

        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ; 
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';
        $this->data['script'] = "<script>acerta_botoes_rep('produtos');</script>";

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
            $dados_com = $this->compra->getCompra($id)[0];
            $pedido = $dados_com['ped_id'];

            $this->compra->delete($id);
            $com_exc = $this->common->deleteReg('dbEstoque','est_compra_produto',"com_id = ".$id);
            $dados_ped = [
                'ped_id' => $pedido,
                'ped_status' => 'P',
            ];
            $this->pedido->save($dados_ped);
        
            $ret['erro'] = false;
            $ret['msg']  = 'Compra Excluída com Sucesso';
            session()->setFlashdata('msg', 'Compra Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Compra, Verifique!<br>';
        }
        echo json_encode($ret);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_prod($dados = false, $ord = 0)
    {
        if(!$dados){
            return;
        }
        $pedid = new MyCampo('est_pedido','ped_id');
        $pedid->ordem = $ord;
        $pedid->valor = isset($dados['ped_id'])?$dados['ped_id']:'';
        $this->ped_id = $pedid->crOculto();

        $compr = new MyCampo('est_compra','com_id');
        $compr->ordem = $ord;
        $compr->valor = isset($dados['com_id'])?$dados['com_id']:'';
        $this->com_id = $compr->crOculto();

        $proco = new MyCampo('est_compra_produto','cop_id');
        $proco->ordem = $ord;
        $proco->valor = isset($dados['cop_id'])?$dados['cop_id']:'';
        $this->cop_id = $proco->crOculto();

        $proid = new MyCampo('est_pedido','pro_id');
        $proid->ordem = $ord;
        $proid->valor = $dados['pro_id'];
        $this->pro_id = $proid->crOculto();

        $gru                        = new MyCampo('est_grupoproduto','gru_nome');
        $gru->valor = $gru->selecionado = $dados['gru_nome'];
        $gru->label                 = '';
        $gru->ordem                 = $ord;
        $gru->largura               = 40;
        $gru->leitura               = true;
        $gru->dispForm              = '';
        $this->gru_nome               = $gru->crInput();

        $pro                        = new MyCampo('est_produto','pro_nome');
        $pro->valor = $pro->selecionado = $dados['pro_nome'];
        $pro->label                 = '';
        $pro->ordem                 = $ord;
        $pro->largura               = 50;
        $pro->leitura               = true;
        $pro->dispForm              = '';
        $this->pro_nome               = $pro->crShow();

        // debug($dados);
        $und                        = new MyCampo('est_unidades','und_sigla');
        $und->valor = $und->selecionado  = isset($dados['und_sigla_compra'])?$dados['und_sigla_compra']:$dados['und_sigla'];
        $und->label                 = '';
        $und->ordem                 = $ord;
        $und->largura               = 8;
        $und->dispForm              = '';
        $this->und_sigla               = $und->crShow();

        $und                        = new MyCampo('est_unidades','und_id');
        $und->valor = $und->selecionado  = isset($dados['und_id_compra'])?$dados['und_id_compra']:$dados['und_id'];
        $und->ordem                 = $ord;
        $this->und_id               = $und->crOculto();

        $qti                        = new MyCampo('est_pedido','ped_qtia');
        $qti->tipo                  = 'sonumero';
        $qti->decimal               = 0;
        $qti->valor = $qti->selecionado  = isset($dados['ped_qtia'])?$dados['ped_qtia']:0;
        $qti->label                 = '';
        $qti->ordem                 = $ord;
        $qti->largura               = 12;
        $qti->dispForm              = '';
        $qti->leitura               = true;
        $this->ped_qtia             = $qti->crInput();

        $fornec = [];
        // if(isset($dados['for_id'])){
            $fornecedor = new EstoquFornecedorModel();
            $dados_for = $fornecedor->getFornecedor();
            $fornec = array_column($dados_for, 'for_completo', 'for_id');
        // }

        $forn                       = new MyCampo('est_compra','for_id');
        $forn->valor = $forn->selecionado  = isset($dados['for_id'])? $dados['for_id']: '';
        $forn->ordem                = $ord;
        // $forn->cadModal             = base_url('EstFornecedor/add/modal=true');
        $forn->label                = '';
        $forn->opcoes               = $fornec;
        $forn->largura              = 60;
        // $forn->urlbusca             = base_url('buscas/buscaFornecedor');
        $forn->leitura              = false;
        $forn->dispForm             = '';
        $this->for_id               = $forn->crSelect();

        $previsao = new DateTime("+5 days");
        $pre                        = new MyCampo('est_compra','com_previsao');
        $pre->valor                 = isset($dados['com_previsao'])? $dados['com_previsao']: $previsao->format('Y-m-d');
        $pre->label                 = '';
        $pre->ordem                 = $ord;
        $pre->largura               = 16;
        $pre->dispForm              = '';
        $this->com_previsao         = $pre->crInput();

        $qtia = formataQuantia(isset($dados['cop_quantia'])?$dados['cop_quantia']:0);

        $qti                        = new MyCampo('est_compra_produto','cop_quantia');
        $qti->tipo                  = 'quantia';
        $qti->label                 = '';
        $qti->ordem                 = $ord;
        $qti->largura               = 10;
        $qti->valor                 = $qtia['qtiv'];
        $qti->decimal               = $qtia['dec'];
        $qti->dispForm              = '';
        $qti->place                 = '';
        $this->cop_quantia          = $qti->crInput();

        $val                        = new MyCampo('est_compra_produto','cop_valor');
        $val->label                 = '';
        $val->ordem                 = $ord;
        $val->largura               = 12;
        $val->valor                 = isset($dados['cop_valor'])? $dados['cop_valor']: '';
        $val->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');gravaCompra(this)";
        $val->dispForm              = '';
        $val->place                 = '';
        $this->cop_valor            = $val->crInput();

        $tot                        = new MyCampo('est_compra_produto','cop_total');
        $tot->label                 = '';
        $tot->ordem                 = $ord;
        $tot->largura               = 20;
        $tot->classep               = 'text-end';
        $tot->valor                 = isset($dados['cop_total'])? $dados['cop_total']: '';
        $tot->dispForm              = '';
        $tot->leitura               = true;
        $tot->place                 = '';
        $this->cop_total            = $tot->crInput();

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
        $erros = [];
        $dados_com = [
            'com_id'    => $dados['com_id'],
            'com_data'  => date('Y-m-d'),
            'com_previsao'  => $dados['com_previsao'],
            'emp_id'    => $dados['emp_id'],
            'for_id'    => $dados['for_id'],
            'ped_id'    => $dados['ped_id'],
            'com_valor' => $dados['cop_total'],
        ];
        if ($this->compra->save($dados_com)) {
            if($dados['com_id'] == ''){
                $com_id = $this->compra->getInsertID();
            } else {
                $com_id = $dados['com_id'];
            }
            $data_atu = date('Y-m-d H:i:s');
            $dados_pro = [
                'cop_id'    => $dados['cop_id'],
                'com_id'    => $com_id,
                'pro_id'    => $dados['pro_id'],
                'und_id'    => $dados['und_id'],
                'cop_quantia'   => floatval($dados['cop_quantia']),
                'cop_valor'   => $dados['cop_valor'],
                'cop_total'   => $dados['cop_total'],
                'cop_atualizado' => $data_atu
            ];
            // debug($dados_pro);
            if($dados['cop_id'] != ''){
                $salva = $this->common->updateReg('dbEstoque','est_compra_produto','cop_id = '.$dados['cop_id'],$dados_pro);
                $cop_id = $dados['cop_id'];
            } else {
                $salva = $this->common->insertReg('dbEstoque','est_compra_produto',$dados_pro);
                $cop_id = $salva;
            }
            if (!$salva) {
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
            } else {
                $dados_ped = [
                    'ped_id' => $dados['ped_id'],
                    'ped_status' => 'C',
                ];
                $this->pedido->save($dados_ped);
                $ret['erro'] = false;
                $ret['com_id'] = $com_id;
                $ret['cop_id'] = $cop_id;
                $ret['msg'] = 'Compra gravada com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            }
        } else {
            $erros = $this->compra->errors();
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Compra, Verifique!';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        }
        echo json_encode($ret);
    }

}

    // /**
    //  * Definição de Campos
    //  * def_campos
    //  *
    //  * @param bool $dados 
    //  * @return void
    //  */
    // public function def_campos_prod2($dados = false, $pos = 0, $show = false)
    // {
    //     $copid                     = new MyCampo('est_compra_produto','cop_id');
    //     $copid->nome               = "cop_id[$pos]";
    //     $copid->ordem              = $pos;
    //     $copid->repete             = true;
    //     $copid->valor              = isset($dados['cop_id']) ? $dados['cop_id'] : '';
    //     $this->cop_id              = $copid->crOculto();

    //     $prods = [];
    //     if($dados){
    //         $produto = new EstoquProdutoModel();
    //         $dados_pro = $produto->getProduto($dados['pro_id']);
    //         $prods = array_column($dados_pro, 'pro_nome', 'pro_id');
    //     }

    //     $pro                        = new MyCampo('est_compra_produto','pro_id');
    //     $pro->id = $pro->nome       = "pro_id[$pos]";
    //     $pro->valor = $pro->selecionado = isset($dados['pro_id'])? $dados['pro_id']: '';
    //     $pro->ordem              = $pos;
    //     $pro->repete             = true;
    //     $pro->opcoes                = $prods;
    //     $pro->largura               = 40;
    //     $pro->obrigatorio           = true;
    //     $pro->urlbusca              = base_url('buscas/busca_produto');
    //     $pro->cadModal              = base_url('EstProduto/add/modal=true');
    //     $pro->dispForm              = 'col-6';
    //     $pro->leitura               = $show;
    //     $pro->funcChan              = 'buscaDadosProduto(this)';
    //     $this->pro_id               = $pro->crSelbusca();

    //     $unidades = new EstoquUndMedidaModel();
    //     $dados_und = $unidades->getUndMedida();
    //     $unids = array_column($dados_und, 'und_nome', 'und_id');

    //     $und                        = new MyCampo('est_produto','und_id');
    //     $und->id = $und->nome       = "und_id[$pos]";
    //     $und->ordem                 = $pos;
    //     $und->repete                = true;
    //     $und->valor = $und->selecionado  = isset($dados['und_id'])? $dados['und_id']: '';
    //     $und->opcoes                = $unids;
    //     $und->largura               = 25;
    //     $und->dispForm              = 'col-6';
    //     $und->leitura               = true;
    //     $this->und_id               = $und->crSelect();

    //     $qti                        = new MyCampo('est_compra_produto','cop_quantia');
    //     $qti->id = $qti->nome       = "cop_quantia[$pos]";
    //     $qti->ordem                 = $pos;
    //     $qti->repete                = true;
    //     $qti->obrigatorio           = true;
    //     $qti->valor                 = isset($dados['cop_quantia'])? $dados['cop_quantia']: '';
    //     $qti->dispForm              = 'col-4';
    //     $qti->leitura               = $show;
    //     $this->cop_quantia          = $qti->crInput();

    //     $val                        = new MyCampo('est_compra_produto','cop_valor');
    //     $val->id = $val->nome       = "cop_valor[$pos]";
    //     $val->ordem                 = $pos;
    //     $val->repete                = true;
    //     $val->obrigatorio           = true;
    //     $val->valor                 = isset($dados['cop_valor'])? $dados['cop_valor']: '';
    //     $val->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total')";
    //     $val->dispForm              = 'col-4';
    //     $val->leitura               = $show;
    //     $this->cop_valor            = $val->crInput();

    //     $tot                        = new MyCampo('est_compra_produto','cop_total');
    //     $tot->id = $tot->nome       = "cop_total[$pos]";
    //     $tot->ordem                 = $pos;
    //     $tot->repete                = true;
    //     $tot->leitura               = true;
    //     $tot->valor                 = isset($dados['cop_total'])? $dados['cop_total']: '';
    //     $tot->dispForm              = 'col-4';
    //     $tot->leitura               = $show;
    //     $this->cop_total            = $tot->crInput();

    //     $atrib['data-index'] = $pos;
    //     $add            = new MyCampo();
    //     $add->attrdata  = $atrib;
    //     $add->nome      = "bt_add[$pos]";
    //     $add->id        = "bt_add[$pos]";
    //     $add->i_cone    = "<i class='fas fa-plus'></i>";
    //     $add->place     = "Adicionar Produto";
    //     $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
    //     $add->funcChan = "addCampo('".base_url("EstCompra/add_campo")."','produtos',this)";
    //     $this->bt_add   = $add->crBotao();

    //     $del            = new MyCampo();
    //     $del->attrdata  = $atrib;
    //     $del->nome      = "bt_del[$pos]";
    //     $del->id        = "bt_del[$pos]";
    //     $del->i_cone     = "<i class='fas fa-trash'></i>";
    //     $del->place     = "Excluir Produto";
    //     $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
    //     $del->funcChan = "exclui_campo('produtos',this)";
    //     $this->bt_del   = $del->crBotao();
    // }


    // /**
    // * Gravação
    // * store
    // *
    // * @return void
    // */
    // public function store2()
    // {
    //     $ret = [];
    //     $ret['erro'] = false;
    //     $dados = $this->request->getPost();
    //     $erros = [];
    //     $total_cop = 0;
    //     if(isset($dados['pro_id']) && count($dados['pro_id']) > 0){
    //         foreach($dados['pro_id'] as $key => $value){
    //             $total_cop += floatval(moedaToFloat($dados['cop_total'][$key]));
    //         }
    //     } else {
    //         $ret['erro'] = true;
    //         $ret['msg'] = 'É necessário informar pelo menos 1 Produto, Verifique!';
    //     }
    //     if(!$ret['erro']){
    //         $dados_com = [
    //             'com_id'    => $dados['com_id'],
    //             'com_data'  => $dados['com_data'],
    //             'com_previsao'  => $dados['com_previsao'],
    //             'emp_id'    => $dados['emp_id'],
    //             'for_id'    => $dados['for_id'],
    //             'com_valor' => $total_cop
    //         ];
    //         if ($this->compra->save($dados_com)) {
    //             $com_id = $this->compra->getInsertID();
    //             $data_atu = date('Y-m-d H:i:s');
    //             if($dados['com_id'] != ''){
    //                 $com_id = $dados['com_id'];
    //             }
    //             if(isset($dados['pro_id'])){
    //                 $data_atu = date('Y-m-d H:i:s');
    //                 foreach($dados['pro_id'] as $key => $value){
    //                     $dados_pro = [
    //                         'cop_id'    => $dados['cop_id'][$key],
    //                         'com_id'    => $com_id,
    //                         'pro_id'    => $dados['pro_id'][$key],
    //                         'und_id'    => $dados['und_id'][$key],
    //                         'cop_quantia'   => floatval($dados['cop_quantia'][$key]),
    //                         'cop_valor'   => floatval($dados['cop_valor'][$key]),
    //                         'cop_total'   => floatval($dados['cop_total'][$key]),
    //                         'cop_atualizado' => $data_atu
    //                     ];
    //                     if($dados['cop_id'][$key] != ''){
    //                         $salva = $this->common->updateReg('dbEstoque','est_compra_produto','cop_id = '.$dados['cop_id'][$key],$dados_pro);
    //                     } else {
    //                         $salva = $this->common->insertReg('dbEstoque','est_compra_produto',$dados_pro);
    //                     }
    //                     if (!$salva) {
    //                         $ret['erro'] = true;
    //                         $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
    //                         break;
    //                     }
    //                 }
    //                 $com_exc = $this->common->deleteReg('dbEstoque','est_compra_produto',"com_id = ".$com_id." AND cop_atualizado != '".$data_atu."'");
    //             }
    //             cache()->clean();
    //             $ret['erro'] = false;
    //             $ret['msg'] = 'Compra gravada com Sucesso!!!';
    //             session()->setFlashdata('msg', $ret['msg']);
    //             $ret['url'] = site_url($this->data['controler']);
    //         } else {
    //             $erros = $this->compra->errors();
    //             $ret['erro'] = true;
    //             $ret['msg'] = 'Não foi possível gravar a Compra, Verifique!';
    //             foreach ($erros as $erro) {
    //                 $ret['msg'] .= $erro . '<br>';
    //             }
    //         }
    //     }
    //     echo json_encode($ret);
    // }

    // /**
    //  * Definição de Campos
    //  * def_campos
    //  *
    //  * @param bool $dados 
    //  * @return void
    //  */
    // public function def_campos($dados = false, $show = false)
    // {
    //     $id = new MyCampo('est_compra','com_id');
    //     $id->valor = isset($dados['com_id']) ? $dados['com_id'] : '';
    //     $this->com_id = $id->crOculto();

    //     $hoje = new DateTime();
    //     $dat                        = new MyCampo('est_compra','com_data');
    //     $dat->valor                 = isset($dados['com_data'])? $dados['com_data']: $hoje->format("Y-m-d");
    //     $dat->largura               = 20;
    //     $dat->leitura               = $show;
    //     $this->com_data             = $dat->crInput();

    //     $previsao = new DateTime("+5 days");
    //     $pre                        = new MyCampo('est_compra','com_previsao');
    //     $pre->valor                 = isset($dados['com_previsao'])? $dados['com_previsao']: $previsao->format('Y-m-d');
    //     $pre->largura               = 20;
    //     $pre->leitura               = $show;
    //     $this->com_previsao         = $pre->crInput();
        
    //     $empresas           = explode(',',session()->get('usu_empresa'));
    //     $empresa            = new ConfigEmpresaModel();
    //     $dados_emp          = $empresa->getEmpresa($empresas);
    //     $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

    //     $emp                        = new MyCampo('est_compra','emp_id');
    //     $emp->valor = $emp->selecionado = isset($dados['emp_id'])? $dados['emp_id']: '';
    //     $emp->obrigatorio           = true;
    //     $emp->opcoes                = $opc_emp;
    //     $emp->largura               = 50;
    //     $emp->leitura               = $show;
    //     $this->emp_id               = $emp->crSelect();

    //     $fornec = [];
    //     if($dados){
    //         $fornecedor = new EstoquFornecedorModel();
    //         $dados_for = $fornecedor->getFornecedor($dados['for_id']);
    //         $fornec = array_column($dados_for, 'for_completo', 'for_id');
    //     }

    //     $forn               = new MyCampo('est_compra','for_id');
    //     $forn->obrigatorio  = true;
    //     $forn->valor        = isset($dados['for_id'])? $dados['for_id']: '';
    //     $forn->cadModal     = base_url('EstFornecedor/add/modal=true');
    //     $forn->opcoes       = $fornec;
    //     $forn->largura      = 50;
    //     $forn->urlbusca     = base_url('buscas/buscaFornecedor');
    //     $forn->leitura      = $show;
    //     $this->for_id       = $forn->crSelbusca();
    // }        
        
    
    // /**
    // * Inclusão
    // * add
    // *
    // * @return void
    // */
    // public function add2()
    // {
    //     $this->def_campos();

    //     $secao[0] = 'Dados Gerais';
    //     $campos[0][0] = $this->com_id;
    //     $campos[0][1] = $this->emp_id;
    //     $campos[0][2] = $this->for_id;
    //     $campos[0][3] = $this->com_data;
    //     $campos[0][4] = $this->com_previsao;

    //     $this->def_campos_prod();

    //     $secao[1] = 'Produtos';
    //     $displ[1] = 'tabela';
    //     $campos[1][0][0] = $this->cop_id;
    //     $campos[1][0][1] = $this->pro_id;
    //     $campos[1][0][2] = $this->und_id;
    //     $campos[1][0][3] = $this->cop_quantia;
    //     $campos[1][0][4] = $this->cop_valor;
    //     $campos[1][0][5] = $this->cop_total;
    //     $campos[1][0][6] = $this->bt_add;
    //     $campos[1][0][7] = $this->bt_del;

    //     $this->data['secoes'] = $secao;
    //     $this->data['displ'] = $displ; 
    //     $this->data['campos'] = $campos;
    //     $this->data['destino'] = 'store';
    //     $this->data['script'] = "<script>acerta_botoes_rep('produtos');</script>";

    //     echo view('vw_edicao', $this->data);
    // }

    // public function add_campo($ind){
    //     $this->def_campos_prod(false, $ind);

    //     $campo = [];
    //     $campo[count($campo)] = $this->cop_id;
    //     $campo[count($campo)] = $this->pro_id;
    //     $campo[count($campo)] = $this->und_id;
    //     $campo[count($campo)] = $this->cop_quantia;
    //     $campo[count($campo)] = $this->cop_valor;
    //     $campo[count($campo)] = $this->cop_total;
    //     $campo[count($campo)] = $this->bt_add;
    //     $campo[count($campo)] = $this->bt_del;

    //     echo json_encode($campo);
    //     exit;
    // }
    
    // /**
    // * Listagem
    // * lista
    // *
    // * @return void
    // */
    // public function produtos()
    // {
    //     $comp = request()->getVar();
    //     $prods = [];
    //     $dados_prods = $this->compra->getCompraProd($comp['compra']);
    //     for ($p=0; $p < count($dados_prods) ; $p++) { 
    //         array_push($prods, $dados_prods[$p]);
    //     }
    //     echo json_encode($prods);
    // }
    
    // /**
    // * Listagem
    // * lista
    // *
    // * @return void
    // */
    // public function lista_prod()
    // {
    //     $param = $_REQUEST['param'];
    //     if($param == 'undefined'){
    //         $param = [0];
    //     } else {
    //         $param = [$param];
    //     }
    //     // if (!$compr_ = cache('compr'.$param)) {
    //         // $empresas           = explode(',',session()->get('usu_empresa'));
    //         $campos = montaColunasCampos($this->data, 'com_id','d');
    //         // debug($campos, true);
    //         $dados_compr = $this->compra->getCompra(false, $param);
    //         for ($dc=0; $dc < count($dados_compr) ; $dc++) { 
    //             $dados_compr[$dc]['d'] = '';
    //             $com = $dados_compr[$dc];
    //             $log = buscaLog('est_compra', $com['com_id']);
    //             $dados_compr[$dc]['com_usuario'] = $log['usua_alterou'];
    //         }
    //         $compras = montaListaColunas($this->data, 'com_id', $dados_compr, $campos[1], true);
    //         for ($cp=0; $cp < count($compras) ; $cp++) { 
    //             $comp =$compras[$cp];
    //             $compras[$cp]['col_details'] =[
    //                 'tit' => ['Produto','Und','Qtia','Unit','Total'],
    //                 'tam' => ['col-5','col-1','col-2','col-2','col-2'],
    //                 'cam' => ['pro_nome','und_sigla','cop_quantia','cop_valor','cop_total'],
    //             ];
    //             $dados_prods = $this->compra->getCompraProd($comp[0]);
    //             for ($p=0; $p < count($dados_prods) ; $p++) { 
    //                 if(floor($dados_prods[$p]['cop_quantia']) == $dados_prods[$p]['cop_quantia']){
    //                     $dados_prods[$p]['cop_quantia'] = intval($dados_prods[$p]['cop_quantia']);
    //                 }
    //                 $dados_prods[$p]['cop_valor'] = floatToMoeda($dados_prods[$p]['cop_valor']);
    //                 $dados_prods[$p]['cop_total'] = floatToMoeda($dados_prods[$p]['cop_total']);
    //                 $compras[$cp]['details'][$p] = $dados_prods[$p];
    //             }
    //         }
    //         $compr['data'] = $compras;
    //     //     cache()->save('compr'.$param, $compr, 60000);
    //     // }
    //     // debug($compr, true);
    //     echo json_encode($compr);
    // }
