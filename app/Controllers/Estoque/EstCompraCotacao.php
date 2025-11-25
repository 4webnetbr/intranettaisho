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

class EstCompraCotacao extends BaseController
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

        $this->data['nome']         = 'compra';
        $this->data['colunas']      = montaColunasLista($this->data, 'com_id', 'd');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista_prod');
        $this->data['campos']         = $campos;
        $this->data['script'] = "<script>carrega_lista_detail('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1, $dados = false)
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
        $emp->valor = $emp->selecionado  = $idempresa ? $idempresa : $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = 'col-6 linha';
        $emp->largura               = 40;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        if ($tipo == 1) {
            $emp->funcChan              = "carrega_lista_detail(this,'" . base_url($this->data['controler'] . '/lista_prod') . "','compra')";
        } else {
            $emp->funcChan              = "carrega_lista_cotacao(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
        }
        $this->dash_empresa         = $emp->crSelect();

        $dados_grc = $this->grupocompra->getGrupocompra();
        $grucs = array_column($dados_grc, 'grc_nome', 'grc_id');
        $grucs = ['0' => ':: Todos ::'] + $grucs;

        $grc                        = new MyCampo('est_produto', 'grc_id');
        $grc->valor                 = '1';
        $grc->selecionado           = [1];
        $grc->label                 = 'Grupo';
        $grc->opcoes                = $grucs;
        $grc->largura               = 40;
        $grc->dispForm              = 'col-6 linha';
        $grc->funcChan              = "carrega_lista_cotacao(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
        $this->grupo                = $grc->crMultiple();

        $fornec = [];
        // $fornecedor = new EstoquFornecedorModel();
        // $dados_for = $fornecedor->getFornecedor();
        $fornec = array_column($this->dados_for, 'for_completo', 'for_id');

        $forn                       = new MyCampo('est_compra', 'for_id');
        $forn->opcoes               = $fornec;
        $forn->valor = $forn->selecionado = $dados['for_id'] ?? '';
        $forn->largura              = 60;
        $forn->leitura              = false;
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
        $campos = montaColunasCampos($this->data, 'com_id', 'd');

        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = [0];
        } else {
            $param = [$param];
        }
        // $campos = montaColunasCampos($this->data, 'com_id');
        // debug($campos, true);
        $dados_compr = $this->compra->getCompra(false, $param);
        $com_ids_assoc = array_column($dados_compr, 'com_id');
        $log = buscaLogTabela('est_compra', $com_ids_assoc);

        $botao[0] = [
            'url'    => base_url('/CriaPdf2025/PedidoCompra/chave'),
            'funcao' => "redirec_blank(\"url\");",
            'classe' => 'btn btn-white btn-sm border border-2 border-dark',
            'title'  => 'Pedido',
            'icone'  => 'fa fa-print',
        ];
        // $this->data['edicao'] = false;
        foreach ($dados_compr as &$com) {
            // Verificar se o log já está disponível para esse ana_id
            $com['com_usuario'] = $log[$com['com_id']]['usua_alterou'] ?? '';
            $qtia = formataQuantia(isset($com['cop_quantia']) ? $com['cop_quantia'] : 0);

            $com['cop_quantia'] = $qtia['qtia'];
            $com['d'] = '';
        }
        $this->data['botoes'] = $botao;
        // $compr = [
        //     'data' => montaListaColunas($this->data, 'com_id', $dados_compr, 'com_id'),
        // ];
        $compr = montaListaColunas($this->data, 'com_id', $dados_compr, $campos[1], true);
        for ($cp = 0; $cp < count($compr); $cp++) {
            $cont = $compr[$cp];
            $compr[$cp]['col_details'] = [
                'tit' => ['Produto', 'Qtia', 'Und','R$ Unit', 'R$ Total', 'Previsão'],
                'tam' => ['col-5', 'col-1', 'col-2','col-1', 'col-1', 'col-2'],
                'cam' => ['pro_nome', 'cop_quantia', 'und_sigla', 'cop_valor', 'cop_total', 'cop_previsao'],
            ];
            $dados_prods = $this->compra->getCompraProd($cont[0]);
            for ($p = 0; $p < count($dados_prods); $p++) {
                $qtia = formataQuantia(isset($dados_prods[$p]['cop_quantia']) ? $dados_prods[$p]['cop_quantia'] : 0);
                $dados_prods[$p]['cop_quantia'] = $qtia['qtia'];
                $dados_prods[$p]['cop_valor'] = floatToMoeda($dados_prods[$p]['cop_valor']);
                $dados_prods[$p]['cop_total'] = floatToMoeda($dados_prods[$p]['cop_total']);
                $dados_prods[$p]['cop_previsao'] = dataDbToBr($dados_prods[$p]['cop_previsao'] ?? $dados_prods[$p]['com_previsao']);
                $compr[$cp]['details'][$p] = $dados_prods[$p];
            }
        }
        // debug($compr);
        $compras['data'] = $compr;
        cache()->save('cont', $compras, 60000);
        // }
        // debug($compr, true);
        echo json_encode($compras);
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
        $campos[0] = $this->dash_empresa;
        $campos[1] = $this->grupo;

        $this->data['nome']         = 'produtos';
        $this->data['colunas']      = ['Produto', 'Data Solic', 'Solicitado', 'Sugestão', 'Und Compra'];
        $this->data['url_lista']    = base_url($this->data['controler'] . '/listaadd');
        $this->data['campos']         = $campos;
        // $this->data['camposedit']   = $camposedit;
        $this->data['destino']         = '';
        $this->data['script']       = "<script>carrega_lista_cotacao('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_add_compra_cotacao_10', $this->data);
    }

    public function listaadd()
    {
        $param = $_REQUEST['param'];
        $param2 = $_REQUEST['param2'];

        if ($param == 'undefined') {
            $param = false;
        }
        if ($param2 == 'undefined' || $param2 == -1 || $param2 == 0) {
            $param2 = false;
        }
        if($param2){
            $param2 = explode(',', $param2); 
        }
        // debug($param2, true);
        $empresas = explode(',', $param);
        $produtos = $this->pedido->getProdutosCotados($empresas[0], $param2);
        // debug($produtos, true);
        $ret = [];
        $forIndexByProduct = [];
        $produto = [];
        $pro_id = '';

        // $inicio = microtime(true);

        foreach ($produtos as $prod) {
            $pro_id = $prod['pro_id'].'A';
                $ret[$pro_id] = [
                    'ped_id'        => $prod['ped_id'],
                    'pro_id'        => $prod['pro_id'],
                    'grc_nome'      => $prod['grc_nome'],
                    'pro_nome'      => $prod['pro_nome'],
                    'ped_datains'   => dataDbToBr($prod['ped_datains']),
                    'ped_qtia'      => $prod['ped_qtia'],
                    'ped_sugestao'  => $prod['ped_sugestao'],
                    'und_consumo'   => $prod['und_sigla'],
                    'und_compra'    => $prod['und_sigla_compra'],
                ];
        }
        return view('partials/accordion_lista_cotacao', ['produtos' => $ret]);

    }

    public function listafornprod()
    {
        $param = $_REQUEST['param'];
        $param2 = $_REQUEST['param2'];
        $param3 = $_REQUEST['param3'];

        if ($param == 'undefined') {
            $param = false;
        }
        if ($param2 == 'undefined' || $param2 == -1 || $param2 == 0) {
            $param2 = false;
        }

        $empresas = explode(',', $param);
        $produtos = $this->pedido->getFornecProdCotados($empresas[0], $param2,$param3);
        // debug($produtos, true);
        $ret = [];
        $pro_id = '';

        // $inicio = microtime(true);

        $fornec = 1;
        $pro_id = $param3;
        $produto = [];
        foreach ($produtos as $prod) {
            if(count($produto) == 0){
                $produto = [
                    'ped_id'        => $prod['ped_id'],
                    'pro_id'        => $prod['pro_id'],
                    'cot_id'        => $prod['cot_id'],
                    'mar_id'        => '',
                    'grc_nome'      => $prod['grc_nome'],
                    'pro_nome'      => $prod['pro_nome'],
                    'cop_id'        => $prod['ped_id'],
                    'ped_datains'   => dataDbToBr($prod['ped_datains']),
                    'ped_qtia'      => $prod['ped_qtia'],
                    'ped_sugestao'  => $prod['ped_sugestao'],
                    'und_consumo'   => $prod['und_sigla'],
                    'und_compra'    => $prod['und_sigla_compra'],
                ];
            }
            // Chama sua função normalmente
            $prod['cop_id'] = $prod['ped_id'];
            // debug($prod);
            $camp = $this->def_campos_forn($prod, $fornec, $pro_id);// $dc agora é o pro_id (chave única do produto)
            // debug($camp, true);
            // Copia os campos dinâmicos
            $ret["for_id_$fornec"]              = $camp["for_id"];
            $ret["cof_preco_$fornec"]           = $camp["cof_preco"];
            $ret["cof_precoundcompra_$fornec"]  = $camp["cof_precoundcompra"];
            $ret["cof_validade_$fornec"]        = $camp["cof_validade"];
            $ret["com_quantia_$fornec"]         = $camp["cop_quantia"];
            $ret["cop_previsao_$fornec"]        = $camp["cop_previsao"];
            $ret["ped_id_$fornec"]              = $camp["ped_id"];
            $ret["cot_id_$fornec"]              = $camp["cot_id"];
            $ret["cop_id_$fornec"]              = $camp["cop_id"];
            $ret["pro_id_$fornec"]              = $camp["pro_id"];
            $ret["mar_id_$fornec"]              = $camp["mar_id"];
            $ret["cof_id_$fornec"]              = $camp["cof_id"];
            $ret["cof_observacao_$fornec"] = isset($prod["cof_observacao"]) ? $prod["cof_observacao"] : '';

            $fornec++;
        }
        if($fornec <= 15 ){
            for($f = $fornec; $f<=15; $f++){
                // $this->def_campos_forn($produto, $i, $pro_id);// $dc agora é o pro_id (chave única do produto)
                $camp = $this->def_campos_forn($produto, $f, $pro_id);// $dc agora é o pro_id (chave única do produto)

                // Copia os campos dinâmicos
                $ret["for_id_$f"]              = $camp["for_id"];
                $ret["cof_preco_$f"]           = $camp["cof_preco"];
                $ret["cof_precoundcompra_$f"]  = $camp["cof_precoundcompra"];
                $ret["cof_validade_$f"]        = $camp["cof_validade"];
                $ret["com_quantia_$f"]         = $camp["cop_quantia"];
                $ret["cop_previsao_$f"]        = $camp["cop_previsao"];
                $ret["ped_id_$f"]              = $camp["ped_id"];
                $ret["cot_id_$f"]              = $camp["cot_id"];
                $ret["cop_id_$f"]              = $camp["cop_id"];
                $ret["pro_id_$f"]              = $camp["pro_id"];
                $ret["mar_id_$f"]              = $camp["mar_id"];
                $ret["cof_id_$f"]              = $camp["cof_id"];
                $ret["cof_observacao_$f"] = '';
            }
        }
        $ret['und_consumo']   = $produto['und_consumo'];
        $ret['und_compra']    = $produto['und_compra'];


        // log_message('debug', 'TEMPO TOTAL LOOP: ' . (microtime(true) - $inicio));
        // echo json_encode($ret);
        return view('partials/produtos_cotados_forn', ['produtos' => $ret]);

    }


    /**
     * Show
     * show
     *
     * @param mixed $id
     * @return void
     */
    public function show($id){
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
        $dados_com = $this->compra->getCompraLista($id);
        // debug($dados_com);
        // $this->def_campos_prod_comprados($dados_com, $show);

        $this->def_campos_lista(2, $dados_com[0]);
        $secao[0] = 'Dados da Compra';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][1] = $this->for_id;

        $secao[1] = 'Produtos';
        $displ[1] = 'tabela';
        // $dados_com = $this->compra->getCompra($id);
        if (count($dados_com) > 0) {
            for ($c = 0; $c < count($dados_com); $c++) {
                $this->def_campos_prod_comprados($dados_com[$c], $c, $show);
                $campos[1][$c] = [];
                $campos[1][$c][count($campos[1][$c])]   = $this->pro_id;
                $campos[1][$c][count($campos[1][$c])]   = $this->ped_qtia;
                $campos[1][$c][count($campos[1][$c])]   = $this->und_sigla;
                $campos[1][$c][count($campos[1][$c])]   = $this->com_id . ' ' . $this->cop_id . ' ' . $this->ped_id . ' ' . $this->mar_id . ' ' . $this->und_id . ' ' . $this->cop_quantia;
                $campos[1][$c][count($campos[1][$c])]   = $this->cop_valor;
                $campos[1][$c][count($campos[1][$c])]   = $this->cop_total;
                $campos[1][$c][count($campos[1][$c])]   = $this->cop_previsao;
                $campos[1][$c][count($campos[1][$c])]   = '';
                $campos[1][$c][count($campos[1][$c])]   = '';
            }
        } else {
            $campos[1][0] = [];
        }

        $this->data['secoes'] = $secao;
        $this->data['displ'] = $displ;
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
    public function delete($id)
    {
        $ret = [];
        try {
            $dados_com = $this->compra->getCompra($id)[0];

            $dados_pro = $this->compra->getCompraProd($id);
            $pedido = array_column($dados_pro, 'ped_id');
            // debug($pedido, true);
            $this->compra->delete($id);
            $com_exc = $this->common->deleteReg('dbEstoque', 'est_compra_produto', "com_id = " . $id);

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
    public function def_campos_forn($dados, $ord = 0, $indice = 0)
    {
        $ret = [];
        if (!$dados) {
            return;
        }
        if ($ord < 1 || $indice < 0) {
            return;
        }
        $attr = [
            'data-ordemcot' => $indice
        ];

        $pedid = new MyCampo('est_pedido', 'ped_id');
        $pedid->ordem = $ord;
        $pedid->attrdata = $attr;
        $pedid->valor = isset($dados['ped_id']) ? $dados['ped_id'] : '';
        $ret["ped_id"] = $pedid->crOculto();

        $cota = new MyCampo('est_cotacao', 'cot_id');
        $cota->ordem = $ord;
        $cota->attrdata = $attr;
        $cota->valor = isset($dados['cot_id']) ? $dados['cot_id'] : '';
        $ret["cot_id"] = $cota->crOculto();

        $chave = "cof_id";
        $cotf = new MyCampo('est_cotacao_fornec', 'cof_id');
        $cotf->ordem = $ord;
        $cotf->attrdata = $attr;
        $cotf->valor = isset($dados[$chave]) ? $dados[$chave] : '';
        $ret[$chave] = $cotf->crOculto();

        $proco = new MyCampo('est_cotacao_produto', 'ctp_id');
        $proco->ordem = $ord;
        $proco->attrdata = $attr;
        $proco->valor = isset($dados['cop_id']) ? $dados['cop_id'] : '';
        $ret["cop_id"] = $proco->crOculto();

        $proid = new MyCampo('est_pedido', 'pro_id');
        $proid->ordem = $ord;
        $proid->attrdata = $attr;
        $proid->valor = $dados['pro_id'];
        $ret["pro_id"] = $proid->crOculto();

        $valor = '';
        $marcs = [];
        $chave = "mar_id";
        // debug($dados);
        // debug($dados[$chave]);
        if(isset($dados[$chave]) && $dados[$chave] != null && $dados[$chave] != 0 && $dados[$chave] != '' ){
            $this->dados_mar = $this->marca->getMarca($dados[$chave]);
            $valor = $dados[$chave];
        } else {
            $this->dados_mar = $this->marca->getMarcaProd($dados['pro_id'], 'A'); // somente marcas aprovadas
        }
        // debug($this->dados_mar);
        $marcs = array_column($this->dados_mar, 'mar_nome', 'mar_id');
        // debug($marcs);
        
        $marc = new MyCampo('est_cotacao_fornec', 'mar_id');
        $marc->valor                = $marc->selecionado  = $valor;
        $marc->ordem                = $ord;
        $marc->attrdata             = $attr;
        $marc->label                = '';
        $marc->opcoes               = $marcs;
        $marc->largura              = 30;
        $marc->dispForm             = 'col-12';
        $marc->classep              = 'text-nowrap';
        $marc->leitura              = false;
        if (isset($dados[$chave]) && $dados[$chave] != null && $dados[$chave] != 0) {
            $marc->leitura = true;
        }
        $ret[$chave]             = $marc->crSelect();

        $fornec = [];
        $chave = "for_id";
        if (isset($dados[$chave]) && $dados[$chave] != null) {
            // $fornecedor = new EstoquFornecedorModel();
            // $dados_for = $fornecedor->getFornecedor($dados[$chave]);
            $fornec = array_column($this->dados_for, 'for_completo', 'for_id');
        }

        $busca = base_url('Buscas/buscaFornecedor');
        $forn                       = new MyCampo('est_cotacao_fornec', 'for_id');
        $forn->valor = $forn->selecionado  = isset($dados[$chave]) ? $dados[$chave] : '';
        $forn->ordem                = $ord;
        $forn->attrdata = $attr;
        $forn->label                = '';
        $forn->opcoes               = $fornec;
        $forn->largura              = 40;
        $forn->dispForm             = 'col-12';
        $forn->classep              = 'text-nowrap';
        $forn->urlbusca             = $busca;
        $forn->leitura              = false;
        if (isset($dados[$chave]) && $dados[$chave] != null) {
            $forn->leitura = true;
        }
        $ret[$chave]               = $forn->crSelbusca();


        $qtia = formataQuantia(isset($dados['ped_qtia']) ? $dados['ped_qtia'] : 0);

        $qti                        = new MyCampo('est_pedido', 'ped_qtia');
        $qti->tipo                  = 'quantia';
        $qti->ordem                 = $ord;
        $qti->label                 = '';
        $qti->attrdata = $attr;
        $qti->largura               = 15;
        $qti->valor                 = 0;
        $qti->decimal               = 0;
        $qti->dispForm              = 'col-12';
        $qti->funcBlur              = 'gravaPreCompra(this)';
        $qti->place                 = '';
        $ret["cop_quantia"]          = $qti->crInput();

        $chave = "cof_preco";
        $val                        = new MyCampo('est_cotacao_fornec', 'cof_preco');
        $val->label                 = '';
        $val->ordem                 = $ord;
        $val->attrdata = $attr;
        $val->largura               = 20;
        $val->valor                 = isset($dados[$chave]) ? $dados[$chave] : (isset($dados['cof_precoundcompra'])?$dados['cof_precoundcompra']:0);
        $val->dispForm              = 'col-12';
        $val->funcBlur              = 'gravaCotacao(this)';
        $val->place                 = '';
        if (isset($dados[$chave]) && $dados[$chave] != null) {
            $val->leitura = true;
        }
        $ret[$chave]            = $val->crInput();

        $chave = "cof_precoundcompra";
        $val                        = new MyCampo('est_cotacao_fornec', 'cof_precoundcompra');
        $val->label                 = '';
        $val->ordem                 = $ord;
        $val->attrdata = $attr;
        $val->largura               = 20;
        $val->valor                 = isset($dados[$chave]) ? $dados[$chave] : '0';
        $val->dispForm              = 'col-12';
        $val->funcBlur              = 'gravaCotacao(this)';
        $val->place                 = '';
        if (isset($dados[$chave]) && $dados[$chave] != null) {
            $val->leitura = true;
        }
        $ret[$chave]            = $val->crInput();

        $chave = "cof_validade";
        $val                        = new MyCampo('est_cotacao_fornec', 'cof_validade');
        $val->ordem                 = $ord;
        $val->label                 = '';
        $val->attrdata = $attr;
        $val->largura               = 30;
        $val->valor                 = isset($dados[$chave]) ? $dados[$chave] : '';
        $val->dispForm              = 'col-12 justify-content-center';
        $val->classediv              = 'text-center';
        $val->funcBlur              = 'gravaCotacao(this)';
        $val->place                 = '';
        // debug($dados[$chave]);
        $data = isset($dados[$chave]) ? $dados[$chave] : '';
        if($data != ''){
            $formato = 'Y-m-d';

            $d = DateTime::createFromFormat($formato, $data);

            if (isset($dados[$chave]) && $d && $d->format($formato) === $data && empty(DateTime::getLastErrors()['warning_count']) && empty(DateTime::getLastErrors()['error_count'])) {
                $val->leitura = true;
            }
        }
        $ret[$chave]            = $val->crInput();

        $previsao = new DateTime("+1 days");
        $chave = "cof_previsao";
        if(isset($dados[$chave]) && $dados[$chave] != ''){
            $previsao = new DateTime("+".$dados[$chave]." days");
        }
        $chave = "cop_previsao";
        $pre                        = new MyCampo('est_compra_produto', 'cop_previsao');
        $pre->valor                 = isset($dados[$chave]) ? $dados[$chave] : $previsao->format('Y-m-d');
        $pre->ordem                 = $ord;
        $pre->label                 = '';
        $pre->attrdata              = $attr;
        $pre->largura               = 30;
        $pre->dispForm              = 'col-12 justify-content-center';
        $pre->funcBlur              = 'gravaPreCompra(this)';
        $pre->classediv              = 'text-center';
        $ret[$chave]             = $pre->crInput();

        $chave = "cof_observacao";
        $obs                        = new MyCampo('est_cotacao_fornec', 'cof_observacao');
        $obs->label                 = '';
        $obs->ordem                 = $ord;
        $obs->attrdata = $attr;
        $obs->largura               = 20;
        $obs->valor                 = isset($dados[$chave]) ? $dados[$chave] : '0';
        $obs->dispForm              = 'col-12';
        $obs->place                 = '';
        $obs->leitura               = true;
        $ret[$chave]             = $val->crInput();

        return $ret;
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
        if (!$dados) {
            return;
        }
        $pedid = new MyCampo('est_pedido', 'ped_id');
        $pedid->ordem = $ord;
        $pedid->valor = isset($dados['ped_id']) ? $dados['ped_id'] : '';
        $this->ped_id = $pedid->crOculto();

        $compr = new MyCampo('est_compra', 'com_id');
        $compr->ordem = $ord;
        $compr->valor = isset($dados['com_id']) ? $dados['com_id'] : '';
        $this->com_id = $compr->crOculto();

        $proco = new MyCampo('est_compra_produto', 'cop_id');
        $proco->ordem = $ord;
        $proco->valor = isset($dados['cop_id']) ? $dados['cop_id'] : '';
        $this->cop_id = $proco->crOculto();

        $undco = new MyCampo('est_compra_produto', 'und_id');
        $undco->ordem = $ord;
        $undco->valor = isset($dados['und_id_compra']) ? $dados['und_id_compra'] : '';
        $this->und_id = $undco->crOculto();

        $proid = new MyCampo('est_pedido', 'pro_id');
        $proid->ordem = $ord;
        $proid->valor = $dados['pro_id'];
        $this->pro_id = $proid->crOculto();


        $fornec = [];
        $fornecedor = new EstoquFornecedorModel();
        $dados_for = $fornecedor->getFornecedor();
        $fornec = array_column($dados_for, 'for_completo', 'for_id');

        $forn                       = new MyCampo('est_compra', 'for_id');
        $forn->valor = $forn->selecionado  = isset($dados['for_id']) ? $dados['for_id'] : '';
        $forn->ordem                = $ord;
        $forn->label                = '';
        $forn->opcoes               = $fornec;
        $forn->largura              = 30;
        $forn->leitura              = false;
        $forn->dispForm             = '';
        $this->for_id               = $forn->crSelect();

        $previsao = new DateTime("+2 days");
        $pre                        = new MyCampo('est_compra', 'com_previsao');
        $pre->valor                 = isset($dados['com_previsao']) ? $dados['com_previsao'] : $previsao->format('Y-m-d');
        $pre->label                 = '';
        $pre->ordem                 = $ord;
        $pre->largura               = 16;
        $pre->dispForm              = '';
        $pre->funcBlur              = 'gravaCompra(this)';
        $this->com_previsao         = $pre->crInput();

        $qtia = formataQuantia(isset($dados['cop_quantia']) ? $dados['cop_quantia'] : 0);

        $qti                        = new MyCampo('est_compra_produto', 'cop_quantia');
        $qti->tipo                  = 'quantia';
        $qti->label                 = '';
        $qti->ordem                 = $ord;
        $qti->largura               = 10;
        $qti->valor                 = $qtia['qtiv'];
        $qti->decimal               = $qtia['dec'];
        $qti->dispForm              = '';
        $qti->place                 = '';
        $this->cop_quantia          = $qti->crInput();

        $val                        = new MyCampo('est_compra_produto', 'cop_valor');
        $val->label                 = '';
        $val->ordem                 = $ord;
        $val->largura               = 12;
        $val->valor                 = isset($dados['cop_valor']) ? $dados['cop_valor'] : '';
        $val->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
        $val->dispForm              = '';
        $val->place                 = '';
        $this->cop_valor            = $val->crInput();

        $tot                        = new MyCampo('est_compra_produto', 'cop_total');
        $tot->label                 = '';
        $tot->ordem                 = $ord;
        $tot->largura               = 16;
        $tot->classep               = 'text-end';
        $tot->valor                 = isset($dados['cop_total']) ? $dados['cop_total'] : '';
        $tot->dispForm              = '';
        $tot->leitura               = true;
        $tot->place                 = '';
        $this->cop_total            = $tot->crInput();

        $atrib['data-index'] = $ord;
        $dup            = new MyCampo();
        $dup->attrdata  = $atrib;
        $dup->nome      = "bt_dup[$ord]";
        $dup->id        = "bt_dup[$ord]";
        $dup->i_cone    = "<i class='fas fa-copy'></i>";
        $dup->place     = "Duplicar Solicitação";
        $dup->classep   = "btn-outline-success btn-sm bt-repete mt-4";
        $dup->funcChan  = "duplicarSolicitacao('" . base_url("EstPedido/dup_solicitacao/" . $dados['ped_id']) . "');";
        $this->bt_dup   = $dup->crBotao();
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
        $qtp->label                 = 'Qtia Pedido';
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
        $qti->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
        $this->cop_quantia          = $qti->crInput();

        $val                        = new MyCampo('est_compra_produto', 'cop_valor');
        $val->nome = $val->id               = "cop_valor[$pos]";
        $val->ordem              = $pos;
        $val->largura               = 15;
        $val->valor                 = $dados['cop_valor'];
        $val->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
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

    /**
     * Gravação
     * store
     *
     * @return void
     */
    public function storecot()
    {
        $ret = ['erro' => false];
        $dados = $this->request->getPost();
        $erros = [];
        $totalGeral = 0;

        // Verificação mínima de campos obrigatórios
        if (empty($dados['compras']['itens'])) {
            $ret['erro'] = true;
            $ret['msg'] = 'Dados incompletos enviados.';
            return $this->response->setJSON($ret);
        }

        // Somar o total de todos os itens
        foreach ($dados['compras']['itens'] as $pedido) {
            foreach ($pedido as $produto) {
                foreach ($produto as $marca) {
                    $valor = moedaToFloat($marca['total']); // Ex: "R$ 10,50" → 10.50
                    $totalGeral += floatval($valor);
                }
            }
        }
        // Dados da compra
        $dados_com = [
            'com_data'     => date('Y-m-d'),
            'emp_id'       => isset($dados['emp_id']) ? $dados['emp_id'] : $dados['empresa'],
            'for_id'       => $dados['compras']['forid'],
            'com_valor'    => $totalGeral,
        ];

        if ($this->compra->save($dados_com)) {
            $com_id = $this->compra->getInsertID();
            $data_atu = date('Y-m-d H:i:s');
            $erroGravacao = false;

            foreach ($dados['compras']['itens'] as $pedidoArray) {
                foreach ($pedidoArray as $produtoArray) {
                    foreach ($produtoArray as $marcaArray) {
                        // debug($marcaArray['marca']);
                        $mar_id  = $this->marca->getMarca($marcaArray['marca']);
                        // debug($mar_id);
                        $dados_pro = [
                            'com_id'        => $com_id,
                            'pro_id'        => $marcaArray['produto'],
                            'ped_id'        => $marcaArray['pedido'],
                            'mar_id'        => $marcaArray['marca'],
                            'und_id'        => $mar_id[0]['und_marca'],
                            'cop_quantia'   => $marcaArray['quantia'],
                            'cop_previsao'  => $marcaArray['previsao'],
                            'cop_valor'     => moedaToFloat($marcaArray['valor']),
                            'cop_total'     => moedaToFloat($marcaArray['total']),
                            'cop_atualizado' => $data_atu
                        ];
                        // debug($dados_pro);

                        $cop_id = $this->common->insertReg('dbEstoque', 'est_compra_produto', $dados_pro);
                        // debug($cop_id);

                        if (!is_int($cop_id) || $cop_id <= 0) {
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar os produtos. Verifique!';
                            $erroGravacao = true;
                            break 3; // Sai de todos os loops aninhados
                        }

                        // Atualiza o pedido (fora do IF para não repetir em loop sem necessidade)
                        $this->pedido->save([
                            'ped_id' => $marcaArray['pedido'],
                            'ped_status' => 'C',
                        ]);
                    }
                }
            }

            if (!$erroGravacao) {
                $ret['erro'] = false;
                $ret['com_id'] = $com_id;
                $ret['cop_id'] = $cop_id;
                $ret['msg'] = 'Compra gravada com Sucesso!';
            }
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Compra. Verifique!<br>';
            foreach ($this->compra->errors() as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        }

        return $this->response->setJSON($ret);
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
        // debug($dados, true);
        $erros = [];
        $total = 0;
        foreach ($dados['pro_id'] as $key => $value) {
            $tot = str_replace(",", ".", $dados['cop_total'][$key]);
            $total += $tot;
        }


        $dados_com = [
            'com_id'    => $dados['com_id'][0],
            'com_data'  => date('Y-m-d'),
            // 'com_previsao'  => $dados['com_previsao'],
            'emp_id'    => (isset($dados['emp_id'])) ? $dados['emp_id'] : $dados['empresa'],
            'for_id'    => $dados['for_id'],
            // 'ped_id'    => $dados['ped_id'],
            'com_valor' => $total,
        ];
        // debug($dados_com, true);
        if ($this->compra->save($dados_com)) {
            if ($dados['com_id'] == '') {
                $com_id = $this->compra->getInsertID();
            } else {
                $com_id = $dados['com_id'][0];
            }
            $data_atu = date('Y-m-d H:i:s');
            foreach ($dados['pro_id'] as $key => $value) {
                $qtia = str_replace(",", ".", $dados['cop_quantia'][$key]);

                $dados_pro = [
                    // 'cop_id'    => $dados['cop_id'][$key],
                    'com_id'    => $com_id,
                    'pro_id'    => $dados['pro_id'][$key],
                    'und_id'    => $dados['und_id'][$key],
                    'ped_id'    => $dados['ped_id'][$key],
                    'mar_id'    => $dados['mar_id'][$key],
                    'cop_quantia'   => floatval($qtia),
                    'cop_valor'   => $dados['cop_valor'][$key],
                    'cop_total'   => $dados['cop_total'][$key],
                    'cop_previsao'   => $dados['cop_previsao'][$key],
                    'cop_atualizado' => $data_atu
                ];
                $salva = $this->common->insertReg('dbEstoque', 'est_compra_produto', $dados_pro);
                $cop_id = $salva;
                if (!$salva) {
                    $ret['erro'] = true;
                    $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                    break;
                } else {
                    $cta_exc = $this->common->deleteReg('dbEstoque', 'est_compra_produto', "com_id = " . $com_id . " AND cop_atualizado != '" . $data_atu . "'");
                    $dados_ped = [
                        'ped_id' => $dados['ped_id'][$key],
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
            }
            $ret['erro'] = false;
            $ret['msg'] = 'Compra gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
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
