<?php
namespace App\Controllers\Estoque;
use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstPedido extends BaseController {
    public $data = [];
    public $permissao = '';
    public $pedido;
    public $common;
    public $empresa;
    public $produto;
    public $unidades;

    /**
    * Construtor da Classe
    * construct
    */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->pedido = new EstoquPedidoModel();
        $this->common    = new CommonModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->produto       = new EstoquProdutoModel();
        $this->unidades     = new EstoquUndMedidaModel();
        
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
        
        $this->data['nome']     	= 'pedido'; 
        $this->data['colunas']      = montaColunasLista($this->data,'ped_id');
        $this->data['url_lista']    = base_url($this->data['controler'].'/lista');
        $this->data['campos']     	= $campos;  
        $this->data['script']       = "<script>carrega_lista('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
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
        $emp->dispForm              = 'linha';
        $emp->largura               = 50;
        if(count($empresas) == 1){
            $emp->leitura           = true;
        }
        if($tipo == 1){
            $emp->funcChan              = "carrega_lista(this, 'EstPedido/lista','pedido');";
        } else {
            // $camposedit = "fields: [{label:\'Quantia:\',name: \'ped_qtia\'},]";
            $emp->funcChan              = "carrega_lista_edit(this, '".base_url($this->data['controler'].'/listaadd')."','produtos');";
        }
        $this->dash_empresa         = $emp->crSelect();
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
            $param = false;
        }

        // if (!$pedid = cache('pedid')) {
            $empresas           = explode(',',$param);
            $campos = montaColunasCampos($this->data, 'ped_id');
            // debug($campos);
            $dados_pedid = $this->pedido->getPedido(false, $empresas);
            for ($dc=0; $dc < count($dados_pedid) ; $dc++) { 
                $ped = $dados_pedid[$dc];
                $log = buscaLog('est_pedido', $ped['ped_id']);
                $dados_pedid[$dc]['ped_usuario'] = $log['usua_alterou'];
            }
            // debug($dados_pedid);
            $pedid = [
                'data' => montaListaColunas($this->data, 'ped_id', $dados_pedid, $campos[1]),
            ];
            // debug($pedid);
            cache()->save('pedid', $pedid, 60000);
        // }

        echo json_encode($pedid);
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
        $secao[0] = 'Informe a Quantia do Produto';
        $campos[0] = $this->dash_empresa;

        $this->data['nome']     	= 'produtos'; 
        $this->data['colunas']      = ['Id','Grupo','Produto','Sugestão','Quantia','Und Compra','Pedido'];
        $this->data['url_lista']    = base_url($this->data['controler'].'/listaadd');
        $this->data['campos']     	= $campos;  
        // $this->data['camposedit']   = $camposedit;  
        $this->data['destino']     	= '';  
        $this->data['script']       = "<script>carrega_lista_edit('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
        echo view('vw_lista_filtrada', $this->data);
        
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
        $this->def_campos_lista(2);
        $secao[0] = 'Informe a Quantia do Produto';
        $campos[0] = $this->dash_empresa;

        $this->data['nome']     	= 'produtos'; 
        $this->data['colunas']      = ['Id','Grupo','Produto','Sugestão','Quantia','Und Compra','Pedido'];
        $this->data['url_lista']    = base_url($this->data['controler'].'/listaadd');
        $this->data['campos']     	= $campos;  
        // $this->data['camposedit']   = $camposedit;  
        $this->data['destino']     	= '';  
        $this->data['script']       = "<script>carrega_lista_edit('empresa', '".$this->data['url_lista']."','".$this->data['nome']."');</script>";
        echo view('vw_lista_filtrada', $this->data);

        // $dados_ped = $this->pedido->getPedido($id)[0];
        // // debug($dados_ped, true);
        // $this->def_campos_lista(2);
        // $secao[0] = 'Informe a Quantia do Produto';
        // $campos[0][0] = $this->dash_empresa;
        // $campos[0][1] = "<div id='produtosPed' class='accordion col-12 justify-content-around align-items-start overflow-y-auto' style='max-height:70vh'>";
        
        // $campospro = $this->montalistaProdutos($dados_ped);

        // $campost[0] = array_merge($campos[0], $campospro[0]);

        // $this->data['secoes'] = $secao;
        // $this->data['campos'] = $campost;
        // $this->data['destino'] = '';
        // echo view('vw_edicao', $this->data);
        
    }

    public function listaadd($empresa = false){
        $param = $_REQUEST['param'];
        if($param == 'undefined'){
            $param = false;
        }
        $empresas           = explode(',',$param);
        $produtos =  $this->produto->getProdutoPedido(false, $empresas[0]);
        // debug($produtos);

        $campos[0] = 'pro_id';
        $campos[1] = 'gru_nome';
        $campos[2] = 'pro_nome';
        // $campos[3] = 'und_consumo';
        // $campos[4] = 'saldo';
        $campos[3] = 'sugestao';
        $campos[4] = 'ped_qtia';
        $campos[5] = 'und_sigla';
        $campos[6] = 'ped_data';

        $dados_pedid = [];
        for ($dc=0; $dc < count($produtos) ; $dc++) { 
            $prod = $produtos[$dc];
            $saldo = $prod['saldo'];
            $minimo = $prod['mmi_minimo'];
            $maximo = ($prod['mmi_maximo'] != null)?$prod['mmi_maximo']:0;
            $sugestao = 0;
            $sugestan = 0;
            if($maximo > 0){
                if($saldo > 0 && $prod['und_id_compra'] != $prod['und_id_saldo'] && $prod['und_id_compra'] != null && $prod['und_id_saldo'] != null){
                    // conversao
                    $conv = $this->unidades->getConversaoDePara($prod['und_id_saldo'], $prod['und_id_compra']);
                    if(count($conv) > 0){
                        // debug($conv);
                        $expressao = $saldo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                        eval('$saldo = ' . $expressao . ';');
                        $sugestao = intval($maximo) - intval($saldo);
                        // $sugestan = $sugestao;
                        // $expressao = $sugestao . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                        // eval('$sugestao = ' . $expressao . ';');

                        // debug($saldores);
                    }
                }
            }
            if($sugestao < 0){
                $sugestao = 0;
            }
            // debug($prod);
            $dados_pedid[$dc]['pro_id']      = $prod['pro_id'];
            $dados_pedid[$dc]['gru_nome']    = $prod['gru_nome'];
            $dados_pedid[$dc]['pro_nome']    = $prod['pro_nome'];
            // $dados_pedid[$dc]['und_consumo'] = $prod['und_sigla'];
            // $dados_pedid[$dc]['saldo']       = formataQuantia($saldo,3)['qtia'];
            $dados_pedid[$dc]['sugestao']    = formataQuantia($sugestao,3)['qtia'];
            $dados_pedid[$dc]['und_sigla']   = $prod['und_sigla_compra'];
            $dados_pedid[$dc]['ped_data']    = "<div id='ped_data[$dc]'>".dataDbToBr($prod['ped_data'])."</div>";
            $dados_pedid[$dc]['ped_id']      = $prod['ped_id'];
            $dados_pedid[$dc]['ped_qtia']    = $prod['ped_qtia'];
            
            $this->def_campos_prod($dados_pedid[$dc], $dc);
            
            $dados_pedid[$dc]['ped_qtia'] = $this->ped_id.' '.$this->pro_id.' '.$this->ped_qtia;
        }
        $pedid = [
            'data' => montaListaEditColunas($campos, 'pro_id', $dados_pedid, $campos[1]),
        ];
        // debug($pedid, true);
            // cache()->save('pedid', $pedid, 60000);
        // }

        echo json_encode($pedid);
    }

    public function montalistaProdutos($dados_ped = false, $empresa = false){
        if(!$empresa){
            $empresas = explode(',',session()->get('usu_empresa'));
        } else {
            $empresas[0] = $empresa;
        }
        $pedido = false;
        $produt = false;
        if($dados_ped){
            $pedido = $dados_ped['ped_id'];
            $produt = $dados_ped['pro_id'];
            $produtos =  $this->pedido->getPedidoProd($pedido, $produt);
        } else {
            $produtos =  $this->produto->getProdutoPedido(false, $empresas[0]);
        }
        
        $cabecalho = "<div class='col-12 bg-primary'>";
        $cabecalho .= "<div class='col-3 text-center float-start bg-primary text-white'><h5>Grupo</h5></div>";
        $cabecalho .= "<div class='col-5 text-center float-start bg-primary text-white'><h5>Produto</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Und</h5></div>";
        $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Quantia</h5></div>";
        $cabecalho .= "</div>";
        $campospr[0]  = [];
        $campospr[0][count($campospr[0])]  = $cabecalho;
        $grupo = '';
        for($p=0;$p<count($produtos);$p++){
            $this->def_campos_prod($produtos[$p], $p);
            // if($produtos[$p]['gru_nome'] != $grupo){
                $grupo = $produtos[$p]['gru_nome'];
                // if($p > 0){
                //     $campospr[0][count($campospr[0])] = "</div>";
                //     $campospr[0][count($campospr[0])] = "</div>";
                // }
            //     $campospr[0][count($campospr[0])] = "<div class='accordion-item col-lg-6 col-12 float-start p-2 border border-primary'>";
            //     $campospr[0][count($campospr[0])] = "<div class='accordion-button bg-primary p-2 collapsed' data-bs-toggle='collapse' data-bs-target='#collapsePed$p' aria-expanded='true' aria-controls='collapsePed$p'>";
            //     $campospr[0][count($campospr[0])] = "<h4 class='text-white'>$grupo</h4>";
            //     $campospr[0][count($campospr[0])] = "</div>";
            //     $campospr[0][count($campospr[0])] = $cabecalho;
            //     $campospr[0][count($campospr[0])] = "<div id='collapsePed$p' class='accordion-collapse collapse col-12 overflow-y-auto' style='max-height:50vh'>";
            // }
            $campospr[0][count($campospr[0])] = "<div class='col-3 text-start float-start'>";
            $campospr[0][count($campospr[0])] = $grupo;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-5 text-start float-start'>";
            $campospr[0][count($campospr[0])] = $this->pro_nome;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-center float-start'>";
            $campospr[0][count($campospr[0])] = $this->und_id;
            $campospr[0][count($campospr[0])] = "</div>";
            $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
            $campospr[0][count($campospr[0])] = $this->ped_id;
            $campospr[0][count($campospr[0])] = $this->pro_id;
            $campospr[0][count($campospr[0])] = $this->ped_qtia;
            $campospr[0][count($campospr[0])] = "</div>";
        }
        $campospr[0][count($campospr[0])] = "</div>";
        // $campospr[0][count($campospr[0])] = "</div>";

        return $campospr;
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
            $this->pedido->delete($id);
            $ped_exc = $this->common->deleteReg('dbEstoque','est_pedido',"ped_id = ".$id);
            $ret['erro'] = false;
            $ret['msg']  = 'Pedido Excluído com Sucesso';
            $ret['id'] = "";
            $ret['ped_data'] = "";
        // session()->setFlashdata('msg', 'Pedido Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Pedido, Verifique!<br>';
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
        // debug($dados, true);
        $pedid = new MyCampo('est_pedido','ped_id');
        $pedid->ordem = $ord;
        $pedid->valor = isset($dados['ped_id'])?$dados['ped_id']:'';
        $this->ped_id = $pedid->crOculto();

        $proid = new MyCampo('est_pedido','pro_id');
        $proid->ordem = $ord;
        $proid->valor = $dados['pro_id'];
        $this->pro_id = $proid->crOculto();

        // $gru                        = new MyCampo('est_grupoproduto','gru_nome');
        // $gru->valor = $gru->selecionado = $dados['gru_nome'];
        // $gru->label                 = '';
        // $gru->ordem                 = $ord;
        // $gru->largura               = 40;
        // $gru->leitura               = true;
        // $gru->dispForm              = '';
        // $this->gru_nome               = $gru->crInput();

        // $pro                        = new MyCampo('est_produto','pro_nome');
        // $pro->valor = $pro->selecionado = $dados['pro_nome'];
        // $pro->label                 = '';
        // $pro->ordem                 = $ord;
        // $pro->largura               = 35;
        // $pro->leitura               = true;
        // $pro->dispForm              = '';
        // $pro->classep                 = ' text-nowrap overflow-hidden';
        // $this->pro_nome               = $pro->crShow();

        // $dados_und = $this->unidades->getUndMedida($dados['und_id_compra'])[0];

        // $und                        = new MyCampo('est_unidades','und_sigla');
        // $und->valor = $und->selecionado  = $dados_und['und_sigla'];
        // $und->label                 = '';
        // $und->ordem                 = $ord;
        // $und->largura               = 13;
        // $und->dispForm              = '';
        // $und->classep                 = ' text-nowrap overflow-hidden';
        // $this->und_id               = $und->crShow();

        $qti                        = new MyCampo('est_pedido','ped_qtia');
        $qti->tipo                  = 'quantia';
        $qti->decimal               = 0;
        $qti->valor = $qti->selecionado  = isset($dados['ped_qtia'])?$dados['ped_qtia']:0;
        $qti->label                 = '';
        $qti->ordem                 = $ord;
        $qti->largura               = 20;
        $qti->dispForm              = '';
        $qti->leitura               = false;
        $qti->funcBlur              =  'gravaPedido(this)';
        $qti->size                  = 5;
        $qti->maxLength             = 5;
        $qti->minimo                = 0;
        $qti->maximo                = 1000;
        $qti->classediv             = 'mb-0 float-end';
        $this->ped_qtia             = $qti->crInput();
    }

    public function atualizaProdutosPedido(){
        $dados = $this->request->getPost();
        $empresa = $dados['emp_id'];
        $campos = $this->listaadd($empresa);
        $ret['campos'] = $campos;
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
        $ret['erro'] = false;
        $dados = $this->request->getPost();
        $erros = [];
        $data = new  \DateTime();
        if(!$ret['erro']){
            $dados_ped = [
                'ped_id'    => $dados['ped_id'],
                'ped_data'  => $data->format('Y-m-d'),
                'pro_id'    => $dados['pro_id'],
                'emp_id'    => $dados['emp_id'],
                'ped_qtia'  => $dados['ped_qtia'],
            ];
            if ($this->pedido->save($dados_ped)) {
                if($dados['ped_id'] == ''){
                    $ped_id = $this->pedido->getInsertID();
                } else {
                    $ped_id = $dados_ped['ped_id'];
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Pedido gravado com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['id'] = $ped_id;
                $ret['ped_data'] = dataDbToBr($dados_ped['ped_data']);
            } else {
                $erros = $this->pedido->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar o Pedido, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }
        echo json_encode($ret);
    }
}
