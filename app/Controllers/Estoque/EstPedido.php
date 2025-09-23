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

class EstPedido extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $pedido;
    public $common;
    public $empresa;
    public $produto;
    public $unidades;
    public $dash_empresa;

    public $pro_id;
    public $ped_id;
    public $und_id;
    public $ped_qtia;
    public $und_sigla;
    public $sugestao;
    public $ped_justifica;
    public $gru_controlaestoque;

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

        $this->data['nome']         = 'pedido';
        $this->data['colunas']      = montaColunasLista($this->data, 'ped_id');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista');
        $this->data['campos']         = $campos;
        $this->data['script']       = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista($tipo = 1)
    {

        $empresas = explode(',', session()->get('usu_empresa'));
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
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        if ($tipo == 1) {
            $emp->funcChan              = "carrega_lista(this, 'EstPedido/lista','pedido');";
        } else {
            // $camposedit = "fields: [{label:\'Quantia:\',name: \'ped_qtia\'},]";
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
            $param = false;
        }

        // if (!$pedid = cache('pedid')) {
        $empresas           = explode(',', $param);
        $campos = montaColunasCampos($this->data, 'ped_id');
        // debug($campos);
        $dados_pedid = $this->pedido->getPedido(false, $empresas[0]);
        $ped_ids_assoc = array_column($dados_pedid, 'ped_id');
        $log = buscaLogTabela('est_pedido', $ped_ids_assoc);
        // $this->data['edicao'] = false;
        foreach ($dados_pedid as &$ped) {
            // Verificar se o log já está disponível para esse ana_id
            $ped['ped_usuario'] = $log[$ped['ped_id']]['usua_alterou'] ?? '';
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

        $this->data['nome']         = 'produtos';
        $this->data['colunas']      = ['Id', 'Grupo', 'Produto', 'Min/Máx', 'Saldo', 'Sugestão', 'Quantia', 'Und Compra', 'Justificativa', 'Pedido'];
        $this->data['url_lista']    = base_url($this->data['controler'] . '/listaadd');
        $this->data['campos']         = $campos;
        $this->data['destino']         = '';
        $this->data['script']       = "<script>carrega_lista_edit('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
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
        $dados_ped = $this->pedido->getPedido($id)[0];
        // debug($dados_com);
        $this->def_campos_prod_pedidos($dados_ped, $show);

        $this->def_campos_lista(2);
        $secao[0] = 'Produto Pedido';
        $campos[0][0] = $this->dash_empresa;
        $campos[0][count($campos[0])]   = $this->pro_id;
        // $campos[0][count($campos[0])]   = $this->sugestao;
        $campos[0][count($campos[0])]   = $this->ped_id . ' ' . $this->und_id . ' ' . $this->ped_qtia;
        $campos[0][count($campos[0])]   = $this->und_sigla;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    // public function listaadd($empresa = false)
    // {
    //     $param = $_REQUEST['param'];
    //     if ($param == 'undefined') {
    //         $param = false;
    //     }
    //     $empresas           = explode(',', $param);
    //     $produtos =  $this->produto->getProdutoPedido(false, $empresas[0]);
    //     // debug($produtos);

    //     $campos[0] = 'pro_id';
    //     $campos[count($campos)] = 'gru_nome';
    //     $campos[count($campos)] = 'pro_nome';
    //     $campos[count($campos)] = 'minmax';
    //     $campos[count($campos)] = 'saldo';
    //     $campos[count($campos)] = 'sugestao';
    //     $campos[count($campos)] = 'ped_qtia';
    //     $campos[count($campos)] = 'und_sigla';
    //     $campos[count($campos)] = 'ped_justifica';
    //     $campos[count($campos)] = 'ped_data';

    //     $dados_pedid = [];
    //     $ctdp = 0;
    //     for ($dc = 0; $dc < count($produtos); $dc++) {
    //         $prod = $produtos[$dc];
    //         // debug($prod);
    //         $saldo = $prod['saldo'];
    //         $consumo = $prod['con_consumo'];
    //         $duracao = $prod['con_duracao'];
    //         $tempore = $prod['con_tporeposicao'];
    //         $fcc     = ($prod['pro_fcc'] > 0) ? $prod['pro_fcc'] : 1;
    //         $saldo   = $prod['saldo'];
    //         // $saldo   = $saldo / $fcc;
    //         $sugestao = 0;
    //         $indice = 0;
    //         $minimo = $prod['mmi_minimo'] ?? 0;
    //         $maximo = $prod['mmi_maximo'] ?? 0;
    //         if ($maximo === 0) {
    //             $hoje = new DateTime();
    //             // Obtém o dia da semana atual (1 = Segunda, ..., 7 = Domingo)
    //             $diaSemanaAtual = $hoje->format('N');
    //             // Define o número do dia da semana desejado (quinta-feira = 4)
    //             $quintaFeira = 4;
    //             // Calcula quantos dias faltam para a próxima quinta-feira
    //             $diasFaltando = ($quintaFeira - $diaSemanaAtual + 7) % 7;
    //             // Se hoje for quinta-feira, a próxima será em 7 dias
    //             $diasFaltando = $diasFaltando == 0 ? 7 : $diasFaltando;

    //             if ($consumo > 0 || $duracao > 0) {
    //                 if ($consumo > 0) {
    //                     $indice = $consumo / 7;
    //                 } else {
    //                     $consumo = 0;
    //                 }
    //                 if ($duracao > 0) {
    //                     $indduracao = 1 / $duracao;
    //                     if ($indduracao > $consumo) {
    //                         $indice = $indduracao;
    //                     }
    //                 }
    //                 $indice     = $indice / $fcc;
    //                 $sugestao   = ($indice * $tempore) - $saldo;
    //                 $maximo     = ($indice * $tempore);
    //                 $minimo     = ($indice * $diasFaltando);
    //             }
    //             $sugestao = $sugestao / $fcc;
    //             if ($sugestao < 0) {
    //                 $sugestao = 0;
    //             }
    //         } else {
    //             $sugestao   = $maximo - $saldo;
    //             $sugestao = $sugestao / $fcc;
    //             if ($sugestao < 0) {
    //                 $sugestao = 0;
    //             }
    //             $minimo = ceil($minimo / $fcc);
    //             $maximo = ceil($maximo / $fcc);
    //             $saldo  = ceil($saldo / $fcc);
    //         }
    //         $sugestao = ceil($sugestao);

    //         // if ($sugestao > 0) {
    //         // debug($prod);
    //         $dados_pedid[$ctdp]['sugestao']    = formataQuantia(intval($sugestao), 3)['qtiv'];

    //         $dados_pedid[$ctdp]['pro_id']      = $prod['pro_id'];
    //         $dados_pedid[$ctdp]['gru_nome']    = $prod['gru_nome'];
    //         $dados_pedid[$ctdp]['pro_nome']    = $prod['pro_nome'];
    //         $dados_pedid[$ctdp]['minmax']      = "<div class='text-start d-inline-flex'>" . ceil($minimo) . "</div> - <div class='text-end d-inline-flex'>" . ceil($maximo) . "</div>";
    //         $dados_pedid[$ctdp]['saldo']       = formataQuantia($saldo, 2)['qtia'];
    //         $dados_pedid[$ctdp]['und_sigla']   = $prod['und_sigla_compra'];
    //         $dados_pedid[$ctdp]['ped_data']    = "<div id='ped_data[$dc]'>" . dataDbToBr($prod['ped_data']) . "</div>";
    //         $dados_pedid[$ctdp]['ped_id']      = $prod['ped_id'];
    //         $dados_pedid[$ctdp]['ped_qtia']    = $prod['ped_qtia'];
    //         $dados_pedid[$ctdp]['und_id']      = $prod['und_id_compra'];
    //         $dados_pedid[$ctdp]['ped_justifica']      = $prod['ped_justifica'];
    //         $dados_pedid[$ctdp]['gru_controlaestoque']      = $prod['gru_controlaestoque'];

    //         $this->def_campos_prod($dados_pedid[$ctdp], $ctdp);

    //         $dados_pedid[$ctdp]['sugestao']    = $this->sugestao;
    //         $dados_pedid[$ctdp]['ped_qtia'] = $this->ped_id . ' ' . $this->pro_id . ' ' . $this->und_id . ' ' . $this->ped_qtia;
    //         $dados_pedid[$ctdp]['ped_justifica']    = $this->ped_justifica . ' ' . $this->gru_controlaestoque;
    //         $ctdp++;
    //         // }
    //     }
    //     $pedid = [
    //         'data' => montaListaEditColunas($campos, 'pro_id', $dados_pedid, $campos[1]),
    //     ];
    //     // debug($pedid, true);
    //     // cache()->save('pedid', $pedid, 60000);
    //     // }

    //     echo json_encode($pedid);
    // }

    public function listaadd($empresa = false)
    {
        $param = $_REQUEST['param'] ?? false;
        if ($param === 'undefined') {
            $param = false;
        }

        $empresas = explode(',', $param);
        $produtos = $this->produto->getProdutoPedido( $empresas[0]);

        $campos = [
            'pro_id', 'gru_nome', 'pro_nome', 'minmax', 'saldo',
            'sugestao', 'ped_qtia', 'und_sigla', 'ped_justifica', 'ped_data'
        ];

        $dados_pedid = [];

        foreach ($produtos as $i => $prod) {
            $saldo     = $prod['saldo'];
            $consumo   = $prod['con_consumo'];
            $duracao   = $prod['con_duracao'];
            $reposicao = $prod['con_tporeposicao'];
            $fcc       = max($prod['pro_fcc'], 1); // nunca 0
            $minimo    = $prod['mmi_minimo'] ?? 0;
            $maximo    = $prod['mmi_maximo'] ?? 0;

            $indice = 0;
            $sugestao = 0;

            if ($maximo === 0) {
                $hoje = new DateTime();
                $diaSemanaAtual = $hoje->format('N');
                $diasFaltando = (4 - $diaSemanaAtual + 7) % 7;
                $diasFaltando = $diasFaltando === 0 ? 7 : $diasFaltando;

                if ($consumo > 0 || $duracao > 0) {
                    $indice = ($consumo > 0) ? ($consumo / 7) : 0;
                    $indDur = ($duracao > 0) ? (1 / $duracao) : 0;
                    $indice = max($indice, $indDur);
                    $indice /= $fcc;

                    $sugestao = ($indice * $reposicao) - $saldo;
                    $maximo = $indice * $reposicao;
                    $minimo = $indice * $diasFaltando;
                }

                $sugestao = max(0, $sugestao / $fcc);
            } else {
                $sugestao = max(0, ($maximo - $saldo) / $fcc);
                $minimo   = ceil($minimo / $fcc);
                $maximo   = ceil($maximo / $fcc);
                $saldo    = ceil($saldo / $fcc);
            }

            $sugestao = ceil($sugestao);

            $dados = [
                'pro_id'      => $prod['pro_id'],
                'gru_nome'    => $prod['gru_nome'],
                'pro_nome'    => $prod['pro_nome'],
                'minmax'      => "<div class='text-start d-inline-flex'>" . ceil($minimo) . "</div> - <div class='text-end d-inline-flex'>" . ceil($maximo) . "</div>",
                'saldo'       => formataQuantia($saldo, 2)['qtia'],
                'sugestao'    => formataQuantia((int)$sugestao, 3)['qtiv'],
                'und_sigla'   => $prod['und_sigla_compra'],
                'ped_data'    => "<div id='ped_data[$i]'>" . dataDbToBr($prod['ped_data']) . "</div>",
                'ped_id'      => $prod['ped_id'],
                'ped_qtia'    => $prod['ped_qtia'],
                'und_id'      => $prod['und_id_compra'],
                'ped_justifica' => $prod['ped_justifica'],
                'gru_controlaestoque' => $prod['gru_controlaestoque'],
            ];

            // Campos adicionais manipulados por lógica externa
            $this->def_campos_prod($dados, $i);

            // Evite sobrescrever 'sugestao' com string depois de formatar!
            $dados['sugestao'] = $this->sugestao;

            // Se necessário, concatene para exibição mas armazene valores separados!
            $dados['ped_qtia'] = "{$this->ped_id} {$this->pro_id} {$this->und_id} {$this->ped_qtia}";
            $dados['ped_justifica'] = "{$this->ped_justifica} {$this->gru_controlaestoque}";

            $dados_pedid[] = $dados;
        }

        $pedid = [
            'data' => montaListaEditColunas($campos, 'pro_id', $dados_pedid, 'gru_nome')
        ];

        echo json_encode($pedid);
    }

    // public function listaadd($empresa = false)
    // {
    //     $param = $_REQUEST['param'] ?? false;
    //     if ($param === 'undefined') {
    //         $param = false;
    //     }

    //     $empresas = explode(',', $param);
    //     $produtos = $this->produto->getProdutoPedido($empresas[0]);

    //     $campos = [
    //         'pro_id', 'gru_nome', 'pro_nome', 'minmax', 'saldo',
    //         'sugestao', 'ped_qtia', 'und_sigla', 'ped_justifica', 'ped_data'
    //     ];

    //     $dados_pedid = [];

    //     // Calcular uma única vez fora do loop
    //     $hoje = new DateTime();
    //     $diaSemanaAtual = $hoje->format('N');
    //     $diasFaltando = (4 - $diaSemanaAtual + 7) % 7;
    //     $diasFaltando = ($diasFaltando === 0) ? 7 : $diasFaltando;

    //     foreach ($produtos as $i => $prod) {
    //         $saldo     = (float) $prod['saldo'];
    //         $consumo   = (float) $prod['con_consumo'];
    //         $duracao   = (float) $prod['con_duracao'];
    //         $reposicao = (float) $prod['con_tporeposicao'];
    //         $fcc       = max((float) $prod['pro_fcc'], 1);
    //         $minimo    = (float) ($prod['mmi_minimo'] ?? 0);
    //         $maximo    = (float) ($prod['mmi_maximo'] ?? 0);
    //         $sugestao  = 0;

    //         if ($maximo === 0) {
    //             if ($consumo > 0 || $duracao > 0) {
    //                 $indiceConsumo = ($consumo > 0) ? ($consumo / 7) : 0;
    //                 $indiceDuracao = ($duracao > 0) ? (1 / $duracao) : 0;
    //                 $indice = max($indiceConsumo, $indiceDuracao) / $fcc;

    //                 $sugestao = max(0, (($indice * $reposicao) - $saldo) / $fcc);
    //                 $maximo = $indice * $reposicao;
    //                 $minimo = $indice * $diasFaltando;
    //             }
    //         } else {
    //             $sugestao = max(0, ($maximo - $saldo) / $fcc);
    //             $minimo   = $minimo / $fcc;
    //             $maximo   = $maximo / $fcc;
    //             $saldo    = $saldo / $fcc;
    //         }

    //         $sugestao = ceil($sugestao);
    //         $minimo   = ceil($minimo);
    //         $maximo   = ceil($maximo);
    //         $saldo    = ceil($saldo);

    //         // Formatação pesada separada para evitar gasto em processamento paralelo
    //         $saldo_formatado    = formataQuantia($saldo, 2)['qtia'];
    //         $sugestao_formatada = formataQuantia($sugestao, 3)['qtiv'];
    //         $data_formatada     = dataDbToBr($prod['ped_data']);

    //         // Dados principais
    //         $dados = [
    //             'pro_id'        => $prod['pro_id'],
    //             'gru_nome'      => $prod['gru_nome'],
    //             'pro_nome'      => $prod['pro_nome'],
    //             'minmax'        => "<div class='text-start d-inline-flex'>{$minimo}</div> - <div class='text-end d-inline-flex'>{$maximo}</div>",
    //             'saldo'         => $saldo_formatado,
    //             'sugestao'      => $sugestao_formatada,
    //             'und_sigla'     => $prod['und_sigla_compra'],
    //             'ped_data'      => "<div id='ped_data[$i]'>" . $data_formatada . "</div>",
    //             'ped_id'        => $prod['ped_id'],
    //             'ped_qtia'      => $prod['ped_qtia'],
    //             'und_id'        => $prod['und_id_compra'],
    //             'ped_justifica' => $prod['ped_justifica'],
    //             'gru_controlaestoque' => $prod['gru_controlaestoque'],
    //         ];

    //         // Esta função é crítica: se ela for lenta, ela é o gargalo principal!
    //         $this->def_campos_prod($dados, $i);

    //         // Inserção final com valores atualizados
    //         $dados['sugestao'] = $this->sugestao;
    //         $dados['ped_qtia'] = "{$this->ped_id} {$this->pro_id} {$this->und_id} {$this->ped_qtia}";
    //         $dados['ped_justifica'] = "{$this->ped_justifica} {$this->gru_controlaestoque}";

    //         $dados_pedid[] = $dados;
    //     }

    //     echo json_encode([
    //         'data' => montaListaEditColunas($campos, 'pro_id', $dados_pedid, 'gru_nome')
    //     ]);
    // }

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
            $ped_exc = $this->common->deleteReg('dbEstoque', 'est_pedido', "ped_id = " . $id);
            $ret['erro'] = false;
            $ret['msg']  = 'Pedido Excluído com Sucesso';
            $ret['id'] = "";
            $ret['ped_data'] = "";
            $ret['ped_justifica'] = "";
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
        if (!$dados) {
            return;
        }
        // debug($dados, true);
        $pedid = new MyCampo('est_pedido', 'ped_id');
        $pedid->ordem = $ord;
        $pedid->valor = isset($dados['ped_id']) ? $dados['ped_id'] : '';
        $this->ped_id = $pedid->crOculto();

        $undco = new MyCampo('est_pedido', 'und_id');
        $undco->ordem = $ord;
        $undco->valor = (isset($dados['und_id'])) ? $dados['und_id'] : '';
        $this->und_id = $undco->crOculto();

        $proid = new MyCampo('est_pedido', 'pro_id');
        $proid->ordem = $ord;
        $proid->valor = $dados['pro_id'];
        $this->pro_id = $proid->crOculto();

        $sug                        = new MyCampo();
        $sug->id = $sug->nome       = 'ped_sugestao';
        $sug->tipo                  = 'quantia';
        $sug->decimal               = 0;
        $sug->valor = $sug->selecionado  = isset($dados['sugestao']) ? $dados['sugestao'] : 0;
        $sug->label                 = '';
        $sug->ordem                 = $ord;
        $sug->leitura               = true;
        $sug->largura               = 10;
        $sug->dispForm              = '';
        $sug->leitura               = true;
        $sug->size                  = 5;
        $sug->maxLength             = 5;
        $sug->minimo                = 0;
        $sug->maximo                = 1000;
        $sug->classediv             = 'mb-0 float-end';
        $this->sugestao             = $sug->crInput();

        $qti                        = new MyCampo('est_pedido', 'ped_qtia');
        $qti->tipo                  = 'quantia';
        $qti->decimal               = 0;
        $qti->valor = $qti->selecionado  = isset($dados['ped_qtia']) ? $dados['ped_qtia'] : 0;
        $qti->label                 = '';
        $qti->ordem                 = $ord;
        $qti->largura               = 10;
        $qti->dispForm              = '';
        $qti->leitura               = false;
        $qti->funcBlur              =  'gravaPedido(this)';
        $qti->size                  = 5;
        $qti->maxLength             = 5;
        $qti->minimo                = 0;
        $qti->maximo                = 1000;
        $qti->classediv             = 'mb-0 float-end';
        $this->ped_qtia             = $qti->crInput();

        // debug($dados);
        $oculta = '';
        if (isset($dados['gru_controlaestoque']) && $dados['gru_controlaestoque'] == 'N') {
            $oculta = 'd-none';
        }
        $jus                        = new MyCampo('est_pedido', 'ped_justifica');
        $jus->objeto                = 'input';
        $jus->tipo                  = 'text';
        $jus->valor = $jus->selecionado  = isset($dados['ped_justifica']) ? $dados['ped_justifica'] : '';
        $jus->label                 = '';
        $jus->ordem                 = $ord;
        $jus->largura               = 15;
        $jus->maxLength             = 200;
        $jus->linhas                = 1;
        $jus->dispForm              = '';
        $jus->leitura               = false;
        $jus->funcBlur              =  'gravaPedido(this)';
        $jus->naocolar              = true;
        $jus->classediv             = "mb-0 float-start $oculta";
        $this->ped_justifica             = $jus->crInput();

        $cont = new MyCampo('est_grupoproduto', 'gru_controlaestoque');
        $cont->objeto                = 'input';
        $cont->ordem                 = $ord;
        $cont->valor = $cont->selecionado = isset($dados['gru_controlaestoque']) ? $dados['gru_controlaestoque'] : '';
        $this->gru_controlaestoque = $cont->crOculto();
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_prod_pedidos($dados = false, $show = false)
    {
        if (!$dados) {
            return;
        }
        // debug($dados, true);
        $pedid = new MyCampo('est_pedido', 'ped_id');
        $pedid->valor = $dados['ped_id'];
        $this->ped_id = $pedid->crOculto();

        $undco = new MyCampo('est_pedido', 'und_id');
        $undco->valor = $dados['und_id_compra'];
        $this->und_id = $undco->crOculto();

        $und                        = new MyCampo('est_unidades', 'und_sigla');
        $und->label                 = 'Und Compra';
        $und->largura               = 10;
        $und->valor                 = isset($dados['und_sigla_compra']) ? $dados['und_sigla_compra'] : '';
        $und->leitura               = true;
        $this->und_sigla             = $und->crInput();

        $produts = [];
        $dados_pro = $this->produto->getProduto();
        $produts = array_column($dados_pro, 'pro_nome', 'pro_id');

        $proid = new MyCampo('est_pedido', 'pro_id');
        $proid->valor = $proid->selecionado = $dados['pro_id'];
        $proid->opcoes = $produts;
        $proid->largura = 60;
        $proid->leitura = true;
        $this->pro_id = $proid->crSelect();

        $qti                        = new MyCampo('est_pedido', 'ped_qtia');
        $qti->tipo                  = 'quantia';
        $qti->decimal               = 0;
        $qti->valor = $qti->selecionado  = isset($dados['ped_qtia']) ? $dados['ped_qtia'] : 0;
        $qti->largura               = 20;
        $qti->leitura               = false;
        $qti->size                  = 5;
        $qti->maxLength             = 5;
        $qti->minimo                = 0;
        $qti->maximo                = 1000;
        $qti->classediv             = 'mb-0 float-end';
        $this->ped_qtia             = $qti->crInput();
    }

    public function atualizaProdutosPedido()
    {
        $dados = $this->request->getPost();
        $empresa = $dados['emp_id'];
        $campos = $this->listaadd($empresa);
        $ret['campos'] = $campos;
        echo json_encode($ret);
    }

    /**
     * dup_solicitacao
     *
     * @return void
     */
    public function dup_solicitacao($id, $empresa)
    {
        $dados = $this->pedido->getPedido($id, $empresa)[0];
        $ret = [];
        $ret['erro'] = false;
        $erros = [];
        $data = new  \DateTime();
        if (!$ret['erro']) {
            $dados_ped = [
                'ped_id'    => '',
                'ped_data'  => $data->format('Y-m-d'),
                'pro_id'    => $dados['pro_id'],
                'emp_id'    => (isset($dados['emp_id'])) ? $dados['emp_id'] : $empresa,
                'ped_qtia'  => $dados['ped_qtia'],
                'und_id'    => $dados['und_id_compra'],
                'ped_justifica'   => $dados['ped_justifica'],
                'ped_sugestao'    => $dados['ped_sugestao'],
            ];
            if ($this->pedido->save($dados_ped)) {
                if ($dados['ped_id'] == '') {
                    $ped_id = $this->pedido->getInsertID();
                } else {
                    $ped_id = $dados_ped['ped_id'];
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Pedido gravado com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['id'] = $ped_id;
                $ret['ped_data'] = dataDbToBr($dados_ped['ped_data']);
                $ret['url'] = site_url($this->data['controler']);
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
        if($dados['pro_id'] == 0){
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar o Pedido, Verifique!';
        }

        $data = new  \DateTime();
        if (!$ret['erro']) {
            if((float)$dados['ped_qtia'] > 0){
                $dados_ped = [
                    'ped_id'    => $dados['ped_id'],
                    'ped_data'  => $data->format('Y-m-d'),
                    'pro_id'    => $dados['pro_id'],
                    'emp_id'    => (isset($dados['emp_id'])) ? $dados['emp_id'] : $dados['empresa'],
                    'ped_qtia'  => $dados['ped_qtia'],
                    'und_id'    => $dados['und_id'],
                    'ped_justifica'    => $dados['ped_justifica'],
                    'ped_sugestao'    => $dados['ped_sugestao'],
                    'ped_datains'   =>  date('Y-m-d H:i:s'),
                ];
                if ($this->pedido->save($dados_ped)) {
                    if ($dados['ped_id'] == '') {
                        $ped_id = $this->pedido->getInsertID();
                    } else {
                        $ped_id = $dados_ped['ped_id'];
                    }
                    $ret['erro'] = false;
                    $ret['msg'] = 'Pedido gravado com Sucesso!!!';
                    // session()->setFlashdata('msg', $ret['msg']);
                    $ret['id'] = $ped_id;
                    $ret['ped_data'] = dataDbToBr($dados_ped['ped_data']);
                    $ret['url'] = site_url($this->data['controler']);
                } else {
                    $erros = $this->pedido->errors();
                    $ret['erro'] = true;
                    $ret['msg'] = 'Não foi possível gravar o Pedido, Verifique!';
                    foreach ($erros as $erro) {
                        $ret['msg'] .= $erro . '<br>';
                    }
                }
            } else {
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar o Pedido, Quantia Zerada!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
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
//     $pedido = false;
//     $produt = false;
//     if($dados_ped){
//         $pedido = $dados_ped['ped_id'];
//         $produt = $dados_ped['pro_id'];
//         $produtos =  $this->pedido->getPedidoProd($pedido, $produt);
//     } else {
//         $produtos =  $this->produto->getProdutoPedido(false, $empresas[0]);
//     }
    
//     $cabecalho = "<div class='col-12 bg-primary'>";
//     $cabecalho .= "<div class='col-3 text-center float-start bg-primary text-white'><h5>Grupo</h5></div>";
//     $cabecalho .= "<div class='col-5 text-center float-start bg-primary text-white'><h5>Produto</h5></div>";
//     $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Und</h5></div>";
//     $cabecalho .= "<div class='col-2 text-center float-start bg-primary text-white'><h5>Quantia</h5></div>";
//     $cabecalho .= "</div>";
//     $campospr[0]  = [];
//     $campospr[0][count($campospr[0])]  = $cabecalho;
//     $grupo = '';
//     for($p=0;$p<count($produtos);$p++){
//         $this->def_campos_prod($produtos[$p], $p);
//         // if($produtos[$p]['gru_nome'] != $grupo){
//             $grupo = $produtos[$p]['gru_nome'];
//             // if($p > 0){
//             //     $campospr[0][count($campospr[0])] = "</div>";
//             //     $campospr[0][count($campospr[0])] = "</div>";
//             // }
//         //     $campospr[0][count($campospr[0])] = "<div class='accordion-item col-lg-6 col-12 float-start p-2 border border-primary'>";
//         //     $campospr[0][count($campospr[0])] = "<div class='accordion-button bg-primary p-2 collapsed' data-bs-toggle='collapse' data-bs-target='#collapsePed$p' aria-expanded='true' aria-controls='collapsePed$p'>";
//         //     $campospr[0][count($campospr[0])] = "<h4 class='text-white'>$grupo</h4>";
//         //     $campospr[0][count($campospr[0])] = "</div>";
//         //     $campospr[0][count($campospr[0])] = $cabecalho;
//         //     $campospr[0][count($campospr[0])] = "<div id='collapsePed$p' class='accordion-collapse collapse col-12 overflow-y-auto' style='max-height:50vh'>";
//         // }
//         $campospr[0][count($campospr[0])] = "<div class='col-3 text-start float-start'>";
//         $campospr[0][count($campospr[0])] = $grupo;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-5 text-start float-start'>";
//         $campospr[0][count($campospr[0])] = $this->pro_nome;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-2 text-center float-start'>";
//         $campospr[0][count($campospr[0])] = $this->und_id;
//         $campospr[0][count($campospr[0])] = "</div>";
//         $campospr[0][count($campospr[0])] = "<div class='col-2 text-end float-start'>";
//         $campospr[0][count($campospr[0])] = $this->ped_id;
//         $campospr[0][count($campospr[0])] = $this->pro_id;
//         $campospr[0][count($campospr[0])] = $this->ped_qtia;
//         $campospr[0][count($campospr[0])] = "</div>";
//     }
//     $campospr[0][count($campospr[0])] = "</div>";
//     // $campospr[0][count($campospr[0])] = "</div>";

//     return $campospr;
// }
