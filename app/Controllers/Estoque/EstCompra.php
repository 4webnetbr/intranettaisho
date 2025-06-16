<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;
class EstCompra extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $compra;
    public $common;
    public $empresa;
    public $pedido;
    public $produto;
    public $dash_empresa;
    public $for_id;
    public $cop_valor;
    public $cop_total;
    public $com_previsao;
    public $com_id;
    public $cop_id;
    public $und_id;
    public $ped_id;
    public $pro_id;
    public $cop_quantia;
    public $ped_qtia;
    public $und_sigla;
    public $unidades;
    public $marca;
    public $bt_dup;

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
        $this->unidades     = new EstoquUndMedidaModel();
        $this->marca        = new EstoquMarcaModel();

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
        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']         = 'compra';
        $this->data['colunas']      = montaColunasLista($this->data, 'com_id');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista');
        $this->data['campos']         = $campos;
        $this->data['script'] = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1, $idempresa = false)
    {
        $empresas = explode(',', session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');
        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';
        $emp->valor = $emp->selecionado  = $idempresa ? $idempresa : $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = 'linha';
        $emp->largura               = 50;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        if ($tipo == 1) {
            $emp->funcChan              = "carrega_lista(this,'EstCompra/lista','compra')";
        } else {
            $emp->funcChan              = "carrega_lista_edit(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
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
        if ($param == 'undefined') {
            $param = [0];
        } else {
            $param = [$param];
        }
        // $campos = montaColunasCampos($this->data, 'com_id');
        // debug($campos, true);
        $dados_compr = $this->compra->getCompraLista(false, $param);
        $com_ids_assoc = array_column($dados_compr, 'com_id');
        $log = buscaLogTabela('est_compra', $com_ids_assoc);
        // $this->data['edicao'] = false;
        foreach ($dados_compr as &$com) {
            // Verificar se o log já está disponível para esse ana_id
            $com['com_usuario'] = $log[$com['com_id']]['usua_alterou'] ?? '';
            // }

            // for ($dc = 0; $dc < count($dados_compr); $dc++) {
            //     // $dados_compr[$dc]['d'] = '';
            //     $com = $dados_compr[$dc];
            //     $log = buscaLog('est_compra', $com['com_id']);
            //     $dados_compr[$dc]['com_usuario'] = $log['usua_alterou'];
            $qtia = formataQuantia(isset($com['cop_quantia']) ? $com['cop_quantia'] : 0);
            // $dados_compr[$dc]['cop_quantia'] = $qtia['qtia'];
            $com['cop_quantia'] = $qtia['qtia'];
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
        $campos[0] = $this->dash_empresa;

        $this->data['nome']         = 'produtos';
        $this->data['colunas']      = ['Id', 'Grupo', 'Produto', 'Fornecedor', 'Data Solic', 'Solicitado', 'Sugestão', 'Und Compra', 'Compra', 'Valor Compra', 'Total Compra', 'Previsão', 'Justificativa', 'Ação'];
        $this->data['url_lista']    = base_url($this->data['controler'] . '/listaadd');
        $this->data['campos']         = $campos;
        // $this->data['camposedit']   = $camposedit;
        $this->data['destino']         = '';
        $this->data['script']       = "<script>carrega_lista_edit('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function listaadd($empresa = false)
    {
        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = false;
        }
        $empresas           = explode(',', $param);
        $produtos =  $this->pedido->getPedido(false, $empresas[0]);
        // debug($produtos, true);
        $campos[0] = 'pro_id';
        $campos[count($campos)] = 'grc_nome';
        $campos[count($campos)] = 'pro_nome';
        $campos[count($campos)] = 'for_id';
        $campos[count($campos)] = 'ped_datains';
        // $campos[count($campos)] = 'minmax';
        $campos[count($campos)] = 'ped_qtia';
        $campos[count($campos)] = 'sugestao';
        $campos[count($campos)] = 'und_sigla';
        $campos[count($campos)] = 'cop_quantia';
        $campos[count($campos)] = 'cop_valor';
        $campos[count($campos)] = 'cop_total';
        $campos[count($campos)] = 'com_previsao';
        $campos[count($campos)] = 'ped_justifica';
        $campos[count($campos)] = 'bt_dup';

        $dados_compr = [];
        for ($dc = 0; $dc < count($produtos); $dc++) {
            $prod = $produtos[$dc];
            // debug($prod);
            $saldo = ($prod['saldo'] != null) ? $prod['saldo'] : 0;
            $sugestao = ($prod['ped_sugestao'] != null) ? $prod['ped_sugestao'] : 0;
            ;
            $dados_compr[$dc]['pro_id']      = $prod['pro_id'];
            $dados_compr[$dc]['grc_nome']    = $prod['grc_nome'];
            $dados_compr[$dc]['ped_datains']    = $prod['ped_datains'];
            $dados_compr[$dc]['ped_qtia']    = $prod['ped_qtia'];
            // $dados_compr[$dc]['sugestao']    = "<div class='text-end'>$sugestao</div>".$texthint;
            $dados_compr[$dc]['sugestao']    = "<div class='text-end'>$sugestao</div>";
            $dados_compr[$dc]['und_id_compra']   = $prod['und_id_compra'];
            $dados_compr[$dc]['und_sigla']   = $prod['und_sigla_compra'];
            $dados_compr[$dc]['ped_data']    = "<div id='ped_data[$dc]'>" . dataDbToBr($prod['ped_datains']) . "</div>";
            $dados_compr[$dc]['ped_id']      = $prod['ped_id'];

            $this->def_campos_prod($dados_compr[$dc], $dc);
            $dados_compr[$dc]['pro_nome']       = $prod['pro_nome'];
            $dados_compr[$dc]['for_id']         = $this->for_id;
            $dados_compr[$dc]['cop_valor']      = $this->cop_valor;
            $dados_compr[$dc]['cop_total']      = $this->cop_total;
            $dados_compr[$dc]['com_previsao']   = $this->com_previsao;
            $dados_compr[$dc]['ped_justifica']  = $prod['ped_justifica'];
            $dados_compr[$dc]['bt_dup']           = $this->bt_dup;
            // debug($dados_compr[$dc]);

            $dados_compr[$dc]['cop_quantia'] = $this->com_id . ' ' . $this->cop_id . ' ' . $this->ped_id . ' ' . $this->pro_id . ' ' . $this->und_id . ' ' . $this->cop_quantia;
        }
        $compr = [
            'data' => montaListaEditColunas($campos, 'pro_id', $dados_compr, $campos[1]),
        ];
        // debug($pedid, true);
        // cache()->save('pedid', $pedid, 60000);
        // }

        echo json_encode($compr);
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
        $dados_com = $this->compra->getCompraLista($id)[0];
        // debug($dados_com);
        $this->def_campos_prod_comprados($dados_com, $show);

        $this->def_campos_lista(2, $dados_com['emp_id']);
        $secao[0] = 'Produto Comprado';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][count($campos[0])]   = $this->for_id;
        $campos[0][count($campos[0])]   = $this->pro_id;
        $campos[0][count($campos[0])]   = $this->ped_qtia;
        $campos[0][count($campos[0])]   = $this->und_sigla;
        $campos[0][count($campos[0])]   = $this->com_id . ' ' . $this->cop_id . ' ' . $this->ped_id . ' ' . $this->und_id . ' ' . $this->cop_quantia;
        $campos[0][count($campos[0])]   = $this->cop_valor;
        $campos[0][count($campos[0])]   = $this->cop_total;
        $campos[0][count($campos[0])]   = $this->com_previsao;


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
    public function delete($id)
    {
        $ret = [];
        try {
            $dados_com = $this->compra->getCompra($id)[0];
            $pedido = $dados_com['ped_id'];

            $this->compra->delete($id);
            $com_exc = $this->common->deleteReg('dbEstoque', 'est_compra_produto', "com_id = " . $id);
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
    public function def_campos_prod_comprados($dados = false, $show = false)
    {
        if (!$dados) {
            return;
        }
        // debug($dados, true);
        $pedid = new MyCampo('est_pedido', 'ped_id');
        $pedid->valor = ($dados['ped_id']) ? $dados['ped_id'] : '';
        $this->ped_id = $pedid->crOculto();

        $compr = new MyCampo('est_compra', 'com_id');
        $compr->valor = $dados['com_id'];
        $this->com_id = $compr->crOculto();

        $proco = new MyCampo('est_compra_produto', 'cop_id');
        $proco->valor = $dados['cop_id'];
        $this->cop_id = $proco->crOculto();

        $undco = new MyCampo('est_compra_produto', 'und_id');
        $undco->valor = $dados['und_id'];
        $this->und_id = $undco->crOculto();

        $produts = [];
        $dados_pro = $this->produto->getProduto();
        $produts = array_column($dados_pro, 'pro_nome', 'pro_id');

        $proid = new MyCampo('est_compra_produto', 'pro_id');
        $proid->valor = $proid->selecionado = $dados['pro_id'];
        $proid->opcoes = $produts;
        $proid->largura = 60;
        $proid->leitura = true;
        $this->pro_id = $proid->crSelect();

        $qtia = formataQuantia($dados['ped_qtia']);
        $qtp                        = new MyCampo('est_pedido', 'ped_qtia');
        $qtp->tipo                  = 'quantia';
        $qtp->label                 = 'Pedido';
        $qtp->largura               = 10;
        $qtp->valor                 = $qtia['qtiv'];
        $qtp->decimal               = $qtia['dec'];
        $qtp->leitura               = true;
        $this->ped_qtia             = $qtp->crInput();

        $und                        = new MyCampo('est_unidades', 'und_sigla');
        $und->label                 = 'Und Compra';
        $und->largura               = 30;
        $und->valor                 = $dados['und_sigla'];
        $und->leitura               = true;
        $this->und_sigla             = $und->crInput();


        $fornec = [];
        $fornecedor = new EstoquFornecedorModel();
        $dados_for = $fornecedor->getFornecedor();
        $fornec = array_column($dados_for, 'for_completo', 'for_id');

        $forn                       = new MyCampo('est_compra', 'for_id');
        $forn->valor = $forn->selecionado  = $dados['for_id'];
        $forn->opcoes               = $fornec;
        $forn->largura              = 60;
        $forn->leitura              = false;
        $this->fogr_id               = $forn->crSelect();

        $previsao = new DateTime("+2 days");
        $pre                        = new MyCampo('est_compra', 'com_previsao');
        $pre->valor                 = $dados['com_previsao'] ?? $dados['cop_previsao'];
        $pre->largura               = 16;
        $this->com_previsao         = $pre->crInput();

        $qtia = formataQuantia($dados['cop_quantia']);

        $qti                        = new MyCampo('est_compra_produto', 'cop_quantia');
        $qti->tipo                  = 'quantia';
        $qti->largura               = 10;
        $qti->valor                 = $qtia['qtiv'];
        $qti->decimal               = $qtia['dec'];
        $qti->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
        $this->cop_quantia          = $qti->crInput();

        $val                        = new MyCampo('est_compra_produto', 'cop_valor');
        $val->largura               = 15;
        $val->valor                 = $dados['cop_valor'];
        $val->funcBlur              = "calculaTotal(this,'cop_quantia', 'cop_valor', 'cop_total');";
        $this->cop_valor            = $val->crInput();

        $tot                        = new MyCampo('est_compra_produto', 'cop_total');
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
                        $mar_id  = $this->marca->getMarca($marcaArray['marca']);

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
        $erros = [];
        $dados_com = [
            'com_id'    => $dados['com_id'],
            'com_data'  => date('Y-m-d'),
            'com_previsao'  => $dados['com_previsao'],
            'emp_id'    => (isset($dados['emp_id'])) ? $dados['emp_id'] : $dados['empresa'],
            'for_id'    => $dados['for_id'],
            'ped_id'    => $dados['ped_id'],
            'com_valor' => $dados['cop_total'],
        ];
        if ($this->compra->save($dados_com)) {
            if ($dados['com_id'] == '') {
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
            if ($dados['cop_id'] != '') {
                $salva = $this->common->updateReg('dbEstoque', 'est_compra_produto', 'cop_id = ' . $dados['cop_id'], $dados_pro);
                $cop_id = $dados['cop_id'];
            } else {
                $salva = $this->common->insertReg('dbEstoque', 'est_compra_produto', $dados_pro);
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
