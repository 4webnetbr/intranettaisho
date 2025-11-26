<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquGrupoCompraModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstCompraNaochegou extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $compra;
    public $common;
    public $empresa;
    public $pedido;
    public $produto;
    public $marca;
    public $grupocompra;
    public $dash_empresa;
    public $for_id;
    public $cop_valor;
    public $cop_total;
    public $cof_previsao;
    public $com_id;
    public $cop_id;
    public $und_id;
    public $ped_id;
    public $pro_id;
    public $mar_id;
    public $cop_quantia;
    public $ped_qtia;
    public $und_sigla;
    public $unidades;
    public $bt_dup;

    public $for_id_1;
    public $cof_id_1;
    public $cof_preco_1;
    public $cof_validade_1;

    public $for_id_2;
    public $cof_id_2;
    public $cof_preco_2;
    public $cof_validade_2;

    public $for_id_3;
    public $cof_id_3;
    public $cof_preco_3;
    public $cof_validade_3;
    public $cot_id;
    public $grupo;
    public $dados_mar;
    public $dados_for;
    public $fornecedor;

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
        $this->produto       = new EstoquProdutoModel();
        $this->marca       = new EstoquMarcaModel();
        $this->grupocompra  = new EstoquGrupoCompraModel();
        $this->unidades     = new EstoquUndMedidaModel();
        $this->fornecedor = new EstoquFornecedorModel();
        $this->data['scripts'] = 'my_consulta';

        // $this->dados_mar = $this->marca->getMarca();
        $this->dados_for = $this->fornecedor->getFornecedor();

        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }
    /**
     * Erro de Acesso
     * erro
     */
    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
        $this->def_campos_lista(1);
        $campos[0] = $this->dash_empresa;

        $this->data['nome']         = 'naochegou';
        $this->data['colunas']      = montaColunasLista($this->data, 'cop_id');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista_prod');
        $this->data['campos']         = $campos;
        $this->data['script'] = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1, $dados = false, $show = false)
    {
        $idempresa = false;
        if ($dados) {
            $idempresa = $dados['emp_id'];
        }
        $empresas = explode(',', session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        // $empresa = $_COOKIE['empresaSelecionada'] ?? null;
        // if($empresa == null){
        //     $empresa = $idempresa;
        // }
        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';
        $emp->valor = $emp->selecionado  = $dados['emp_id'] ?? $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        // $emp->dispForm              = 'col-6 linha';
        $emp->largura               = 40;
        $emp->leitura               = $show;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        if ($tipo == 1) {
            $emp->funcChan              = "carrega_lista(this,'" . base_url($this->data['controler'] . '/lista_prod') . "','compra')";
        } else {
            $emp->funcChan              = "carrega_lista(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
        }
        $this->dash_empresa         = $emp->crSelect();

        $dados_grc = $this->grupocompra->getGrupocompra();
        $grucs = array_column($dados_grc, 'grc_nome', 'grc_id');
        $grucs = ['0' => ':: Todos ::'] + $grucs;

        $grc                        = new MyCampo('est_produto', 'grc_id');
        $grc->valor = $grc->selecionado = '1';
        $grc->label                 = 'Grupo';
        $grc->opcoes                = $grucs;
        $grc->largura               = 40;
        $grc->dispForm              = 'col-6 linha';
        $grc->funcChan              = "carrega_lista_cotacao(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
        $this->grupo                = $grc->crSelect();

        $fornec = [];
        // $fornecedor = new EstoquFornecedorModel();
        // $dados_for = $fornecedor->getFornecedor();
        $fornec = array_column($this->dados_for, 'for_completo', 'for_id');

        $forn                       = new MyCampo('est_compra', 'for_id');
        $forn->opcoes               = $fornec;
        $forn->valor = $forn->selecionado = $dados['for_id'] ?? '';
        $forn->largura              = 60;
        $forn->leitura              = $show;
        // $forn->dispForm              = 'col-12';
        $this->for_id               = $forn->crSelect();

    }


    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista_prod()
    {
        // if (!$compr = cache('compr')) {
        $campos = montaColunasCampos($this->data, 'cop_id');

        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = [0];
        } else {
            $param = [$param];
        }

        $dados_prods = $this->compra->getCompraProd(false, false, 'N', $param);

        $botao[0] = [
            'url'    => base_url('/EstCompraNaochegou/cancela/chave'),
            'funcao' => "cancela(\"url\",\"\");",
            'classe' => 'btn btn-outline-danger border-0 btn-sm ',
            'title'  => 'Cancela',
            'icone'  => 'fa fa-cancel',
        ];
        foreach ($dados_prods as &$com) {
            // Verificar se o log já está disponível para esse ana_id
            $com['com_usuario'] = $log[$com['com_id']]['usua_alterou'] ?? '';
            $qtia = formataQuantia(isset($com['cop_quantia']) ? $com['cop_quantia'] : 0);
            
            $com['cop_quantia'] = $qtia['qtia'];
            // $com['d'] = '';
        }
        $this->data['botoes'] = $botao;
        // $compr = [
            //     'data' => montaListaColunas($this->data, 'com_id', $dados_compr, 'com_id'),
            // ];
            $this->data['exclusao'] = false;
        $compr = montaListaColunas($this->data, 'cop_id', $dados_prods, $campos[1]);
        // debug($compr);
        $compras['data'] = $compr;
        cache()->save('cont', $compras, 60000);
        // }
        // debug($compr, true);
        echo json_encode($compras);
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
        $dados_com = $this->compra->getCompraCop($id);
        // debug($dados_com);
        // $this->def_campos_prod_comprados($dados_com, $show);

        $this->def_campos_lista(2, $dados_com[0], true);
        $secao[0] = 'Dados da Compra';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][1] = $this->for_id;

        $this->def_campos_prod_comprados($dados_com[0]);
        $campos[0][count($campos[0])]   = $this->pro_id;
        $campos[0][count($campos[0])]   = $this->ped_qtia;
        $campos[0][count($campos[0])]   = $this->und_sigla;
        $campos[0][count($campos[0])]   = $this->com_id . ' ' . $this->cop_id . ' ' . $this->ped_id . ' ' . $this->mar_id . ' ' . $this->und_id . ' ' . $this->cop_quantia;
        $campos[0][count($campos[0])]   = $this->cop_valor;
        $campos[0][count($campos[0])]   = $this->cop_total;
        $campos[0][count($campos[0])]   = $this->cop_previsao;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Exclusão
     * delete
     *
     * @param mixed $id
     * @return void
     */
    public function cancela($id)
    {
        $ret = [];
        try {
            // $dados_com = $this->compra->getCompra($id)[0];

            $dados_pro = $this->compra->getCompraCop($id);
            $pedido = array_column($dados_pro, 'ped_id');
            // debug($pedido, true);
            $dados_cop = [
                'cop_id' => $id,
                'cop_status' => 'C',
            ];
            $com_exc = $this->common->updateReg('dbEstoque', 'est_compra_produto', "cop_id = " . $id, $dados_cop, $id);

            for ($pd = 0; $pd < count($pedido) ; $pd++) {
                if($pedido[$pd] != ''){
                    $dados_ped = [
                        'ped_id' => $pedido[$pd],
                        'ped_status' => 'P',
                    ];
                    $this->pedido->save($dados_ped);
                }
            }

            $ret['erro'] = false;
            $ret['msg']  = 'Compra Cancelada com Sucesso';
            session()->setFlashdata('msg', 'Compra Cancelada com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Cancelar a Compra, Verifique!<br>';
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
    public function def_campos_prod_comprados($dados = false, $pos = 0, $show = false)
    {
        if (!$dados) {
            return;
        }
        // debug($dados, true);
        $pedid = new MyCampo('est_pedido', 'ped_id');
        $pedid->nome = $pedid->id               = "ped_id[$pos]";
        $pedid->ordem              = $pos;
        $pedid->valor = ($dados['ped_id']) ? $dados['ped_id'] : '';
        $this->ped_id = $pedid->crOculto();

        $marca = new MyCampo('est_compra_produto', 'mar_id');
        $marca->nome = $marca->id               = "mar_id[$pos]";
        $marca->ordem              = $pos;
        $marca->valor = ($dados['mar_id']) ? $dados['mar_id'] : '';
        $this->mar_id = $marca->crOculto();

        $compr = new MyCampo('est_compra', 'com_id');
        $compr->nome = $compr->id               = "com_id[$pos]";
        $compr->ordem              = $pos;
        $compr->valor = $dados['com_id'];
        $this->com_id = $compr->crOculto();

        $proco = new MyCampo('est_compra_produto', 'cop_id');
        $proco->nome = $proco->id               = "cop_id[$pos]";
        $proco->ordem              = $pos;
        $proco->valor = $dados['cop_id'];
        $this->cop_id = $proco->crOculto();

        $undco = new MyCampo('est_compra_produto', 'und_id');
        $undco->nome = $undco->id               = "und_id[$pos]";
        $undco->ordem              = $pos;
        $undco->valor = $dados['und_id'];
        $this->und_id = $undco->crOculto();

        $produts = [];
        $dados_pro = $this->produto->getProduto();
        $produts = array_column($dados_pro, 'pro_nome', 'pro_id');

        $proid = new MyCampo('est_compra_produto', 'pro_id');
        $proid->nome = $proid->id               = "pro_id[$pos]";
        $proid->ordem              = $pos;
        $proid->valor = $proid->selecionado = $dados['pro_id'];
        $proid->opcoes = $produts;
        $proid->largura = 60;
        $proid->leitura = true;
        $this->pro_id = $proid->crSelect();

        $qtia = formataQuantia($dados['ped_qtia']);
        $qtp                        = new MyCampo('est_pedido', 'ped_qtia');
        $qtp->nome = $qtp->id               = "ped_qtia[$pos]";
        $qtp->ordem              = $pos;
        $qtp->tipo                  = 'quantia';
        $qtp->label                 = 'Pedido';
        $qtp->largura               = 10;
        $qtp->valor                 = $qtia['qtiv'];
        $qtp->decimal               = $qtia['dec'];
        $qtp->leitura               = true;
        $this->ped_qtia             = $qtp->crInput();

        $und                        = new MyCampo('est_unidades', 'und_sigla');
        $und->nome = $und->id               = "und_sigla[$pos]";
        $und->ordem              = $pos;
        $und->label                 = 'Und Compra';
        $und->largura               = 30;
        $und->valor                 = $dados['und_sigla'];
        $und->leitura               = true;
        $this->und_sigla             = $und->crInput();


        $previsao = new DateTime("+2 days");
        $pre                        = new MyCampo('est_compra_produto', 'cop_previsao');
        $pre->nome = $pre->id                  = "cop_previsao[$pos]";
        $pre->ordem                 = $pos;
        $pre->valor                 = $dados['cop_previsao'] ?? $dados['com_previsao'];
        $pre->largura               = 16;
        $this->cop_previsao         = $pre->crInput();

        $qtia = formataQuantia($dados['cop_quantia']);

        $qti                        = new MyCampo('est_compra_produto', 'cop_quantia');
        $qti->nome = $qti->id       = "cop_quantia[$pos]";
        $qti->ordem              = $pos;
        $qti->tipo                  = 'quantia';
        $qti->largura               = 10;
        $qti->valor                 = $qtia['qtiv'];
        $qti->decimal               = $qtia['dec'];
        $qti->leitura = true;
        $qti->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
        $this->cop_quantia          = $qti->crInput();

        $val                        = new MyCampo('est_compra_produto', 'cop_valor');
        $val->nome = $val->id               = "cop_valor[$pos]";
        $val->ordem              = $pos;
        $val->largura               = 15;
        $val->valor                 = $dados['cop_valor'];
        $val->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
        $val->leitura = true;
        $this->cop_valor            = $val->crInput();

        $tot                        = new MyCampo('est_compra_produto', 'cop_total');
        $tot->nome = $tot->id               = "cop_total[$pos]";
        $tot->ordem              = $pos;
        $tot->largura               = 20;
        $tot->classep               = 'text-end';
        $tot->valor                 = $dados['cop_total'];
        $tot->leitura               = true;
        $this->cop_total            = $tot->crInput();
    }

    // /**
    //  * Gravação
    //  * store
    //  *
    //  * @return void
    //  */
    // public function storecot()
    // {
    //     $ret = ['erro' => false];
    //     $dados = $this->request->getPost();
    //     $erros = [];
    //     $totalGeral = 0;

    //     // Verificação mínima de campos obrigatórios
    //     if (empty($dados['compras']['itens'])) {
    //         $ret['erro'] = true;
    //         $ret['msg'] = 'Dados incompletos enviados.';
    //         return $this->response->setJSON($ret);
    //     }

    //     // Somar o total de todos os itens
    //     foreach ($dados['compras']['itens'] as $pedido) {
    //         foreach ($pedido as $produto) {
    //             foreach ($produto as $marca) {
    //                 $valor = moedaToFloat($marca['total']); // Ex: "R$ 10,50" → 10.50
    //                 $totalGeral += floatval($valor);
    //             }
    //         }
    //     }
    //     // Dados da compra
    //     $dados_com = [
    //         'com_data'     => date('Y-m-d'),
    //         'emp_id'       => isset($dados['emp_id']) ? $dados['emp_id'] : $dados['empresa'],
    //         'for_id'       => $dados['compras']['forid'],
    //         'com_valor'    => $totalGeral,
    //     ];

    //     if ($this->compra->save($dados_com)) {
    //         $com_id = $this->compra->getInsertID();
    //         $data_atu = date('Y-m-d H:i:s');
    //         $erroGravacao = false;

    //         foreach ($dados['compras']['itens'] as $pedidoArray) {
    //             foreach ($pedidoArray as $produtoArray) {
    //                 foreach ($produtoArray as $marcaArray) {
    //                     // debug($marcaArray['marca']);
    //                     $mar_id  = $this->marca->getMarca($marcaArray['marca']);
    //                     // debug($mar_id);
    //                     $dados_pro = [
    //                         'com_id'        => $com_id,
    //                         'pro_id'        => $marcaArray['produto'],
    //                         'ped_id'        => $marcaArray['pedido'],
    //                         'mar_id'        => $marcaArray['marca'],
    //                         'und_id'        => $mar_id[0]['und_marca'],
    //                         'cop_quantia'   => $marcaArray['quantia'],
    //                         'cop_previsao'  => $marcaArray['previsao'],
    //                         'cop_valor'     => moedaToFloat($marcaArray['valor']),
    //                         'cop_total'     => moedaToFloat($marcaArray['total']),
    //                         'cop_atualizado' => $data_atu
    //                     ];
    //                     // debug($dados_pro);

    //                     $cop_id = $this->common->insertReg('dbEstoque', 'est_compra_produto', $dados_pro);
    //                     // debug($cop_id);

    //                     if (!is_int($cop_id) || $cop_id <= 0) {
    //                         $ret['erro'] = true;
    //                         $ret['msg'] = 'Não foi possível gravar os produtos. Verifique!';
    //                         $erroGravacao = true;
    //                         break 3; // Sai de todos os loops aninhados
    //                     }

    //                     // Atualiza o pedido (fora do IF para não repetir em loop sem necessidade)
    //                     $this->pedido->save([
    //                         'ped_id' => $marcaArray['pedido'],
    //                         'ped_status' => 'C',
    //                     ]);
    //                 }
    //             }
    //         }

    //         if (!$erroGravacao) {
    //             $ret['erro'] = false;
    //             $ret['com_id'] = $com_id;
    //             $ret['cop_id'] = $cop_id;
    //             $ret['msg'] = 'Compra gravada com Sucesso!';
    //         }
    //     } else {
    //         $ret['erro'] = true;
    //         $ret['msg'] = 'Não foi possível gravar a Compra. Verifique!<br>';
    //         foreach ($this->compra->errors() as $erro) {
    //             $ret['msg'] .= $erro . '<br>';
    //         }
    //     }

    //     return $this->response->setJSON($ret);
    // }

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
        // debug($dados, true);
        $erros = [];
        $data_atu = date('Y-m-d H:i:s');
        $dados_pro = [
            // 'cop_id'    => $dados['cop_id'][$key],
            'cop_previsao'   => $dados['cop_previsao'][0],
            'cop_status'   => 'P',
            'cop_atualizado' => $data_atu
        ];
        $salva = $this->common->updateReg('dbEstoque', 'est_compra_produto', 'cop_id ='.$dados['cop_id'][0], $dados_pro,$dados['cop_id'][0]);
        $cop_id = $salva;
        if (!$salva) {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível atualizar o produto, Verifique!';
        } else {
            $ret['erro'] = false;
            $ret['cop_id'] = $cop_id;
            $ret['msg'] = 'Produto atualizado com Sucesso!!!';
            // session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }
}

// public function montalistaProdutos($dados_ped = false, $empresa = false){
//     if(!$empresa){
//         $empresas = explode(',',session()->get('usu_empresa'));
//     } else {
//         $empresas[0] = $empresa;
//     }
//     $compra = false;
//     $produt = false;
//     if($dados_ped){
//         $compra = $dados_ped['com_id'];
//         $produt = $dados_ped['pro_id'];
//         $produtos =  $this->compra->getCompraProd($compra, $produt);
//     } else {
//         $produtos =  $this->pedido->getPedidoProd(false, false, $empresas[0]);
//     }
//     // debug($produtos);

//     $cabecalho = "<div class='row col-12 bg-primary'>";
//     $cabecalho .= "<div class='col-6 text-center float-start bg-primary text-white'><h5>Fornecedor</h5></div>";
//     $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Entrega</h5></div>";
//     $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white'><h5>Qtia</h5></div>";
//     $cabecalho .= "<div class='col-1 text-center float-start bg-primary text-white'><h5>Custo</h5></div>";
//     $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Total</h5></div>";
//     $cabecalho .= "</div>";
//     $campospr[0]  = [];
//     $grupo = '';
//     $campospr[0][count($campospr[0])] = "<div class='bg-primary p-2 '>";
//     $campospr[0][count($campospr[0])] = "<h4 class='text-white'>Produtos Pedidos</h4>";
//     $campospr[0][count($campospr[0])] = "</div>";
//     $campospr[0][count($campospr[0])] = $cabecalho;
//     $campospr[0][count($campospr[0])] = "<div id='Produtos' class='col-12 overflow-y-auto' style='max-height:59vh;height:59vh'>";

//     $count = 0;
//     for($p=0;$p<count($produtos);$p++){
//         $this->def_campos_prod($produtos[$p], $p);
//         $campospr[0][count($campospr[0])] = "<div class='row col-12 border border-2 ".(++$count%2 ? "odd" : "even")."'>";
//         $campospr[0][count($campospr[0])] = "<div class='row col-12 text-start float-start'>";
//         $campospr[0][count($campospr[0])] = "<div class='col-8 text-start float-start'>";
//         $campospr[0][count($campospr[0])] = $this->pro_nome;
//         $campospr[0][count($campospr[0])] = $this->ped_id;
//         $campospr[0][count($campospr[0])] = $this->pro_id;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
//         $campospr[0][count($campospr[0])] = $this->ped_qtia;
//         $campospr[0][count($campospr[0])] = $this->und_id;
//         $campospr[0][count($campospr[0])] = $this->und_sigla;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='row col-12 text-start float-start'>";
//         $campospr[0][count($campospr[0])] = "<div class='col-6 text-end float-start'>";
//         $campospr[0][count($campospr[0])] = $this->com_id;
//         $campospr[0][count($campospr[0])] = $this->cop_id;
//         $campospr[0][count($campospr[0])] = $this->for_id;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
//         $campospr[0][count($campospr[0])] = $this->com_previsao;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-1 text-end float-start'>";
//         $campospr[0][count($campospr[0])] = $this->cop_quantia;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-1 text-end float-start'>";
//         $campospr[0][count($campospr[0])] = $this->cop_valor;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-end d-block'>";
//         $campospr[0][count($campospr[0])] = $this->cop_total;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "</div>";
//     }
//     $campospr[0][count($campospr[0])] = "</div>";

//     return $campospr;
// }
