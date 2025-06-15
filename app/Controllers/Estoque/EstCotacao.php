<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCotacaoFornecModel;
use App\Models\Estoqu\EstoquCotacaoModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstCotacao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $cotacao;
    public $cotacao_fornec;
    public $produtos;
    public $common;
    public $empresa;
    public $pedido;

    public $dash_empresa;
    public $cot_prazo;
    public $bt_cota;
    public $cot_id;
    public $ctp_id;
    public $pro_id;
    public $ctp_quantia;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->cotacao = new EstoquCotacaoModel();
        $this->cotacao_fornec = new EstoquCotacaoFornecModel();
        $this->produtos = new EstoquProdutoModel();
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

        $this->data['nome']         = 'cotacao';
        $this->data['colunas']      = montaColunasLista($this->data, 'cot_id','d');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista_prod');
        // $this->data['script']       = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_details', $this->data);
    }

    public function def_campos_lista($tipo = 1, $dados = false)
    {

        if ($dados) {
            $cot                       = new MyCampo('est_cotacao', 'cot_id');
            $cot->valor                = $dados['cot_id'];
            $this->cot_id            = $cot->crOculto();
        }

        $empresas = explode(',', session()->get('usu_empresa'));
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
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        if ($tipo == 1) {
            $emp->funcChan              = "carrega_lista(this,'EstCotacao/lista','cotacao')";
        } else {
            $emp->funcChan              = "atualizaProdutosCotacao()";
        }
        $this->dash_empresa         = $emp->crSelect();

        if (!$dados) {
            $agora = new DateTime();
            $agora->modify('+24 hours');
            $termina = $agora->format('Y-m-d H:i:s');
        } else {
            $termina = $dados['cot_prazo'];
        }
        $praz                       = new MyCampo('est_cotacao', 'cot_prazo');
        $praz->valor                = $termina;
        $praz->dispForm             = 'col-6';
        $this->cot_prazo            = $praz->crInput();

        $cota          = new MyCampo();
        $cota->nome    = 'bt_cota';
        $cota->id      = 'bt_cota';
        $cota->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                              <i class="fa-solid fa-recycle" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $cota->i_cone  .= '<div class="align-items-start txt-bt-manut">Gerar Cotações</div>';
        $cota->place    = 'Gerar Cotações';
        $cota->funcChan = 'geraCotacao()';
        $cota->classep  = 'btn-primary bt-manut btn-sm mb-2 float-end';
        $this->bt_cota = $cota->crBotao();

        $opcmarc[1] = 'Marcar Todos';
        $opcmarc[0] = 'Desmarcar Todos';

        $marc        = new MyCampo();
        $marc->id = $marc->nome = "MarcDesmarc";
        $marc->label = 'Marcar/Desmarcar Todos';
        $marc->opcoes = $opcmarc;
        $marc->dispForm = 'col-6 justify-content-end';
        $marc->classediv  = 'text-end';
        $marc->funcChan = 'checkRadioButtons(this)';
        $marc->valor = $marc->selecionado = 0;
        $this->marctodos = $marc->crRadio();

    }


    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // $campos = montaColunasCampos($this->data, 'cot_id');
        // debug($campos, true);
        // $this->data['edicao'] = false;
        $dados_compr = $this->cotacao->getCotacao();
        $agora = date('Y-m-d H:i:s');
        for ($dc = 0; $dc < count($dados_compr); $dc++) {
            // $dados_compr[$dc]['d'] = '';
            $com = $dados_compr[$dc];
            $log = buscaLog('est_cotacao', $com['cot_id']);
            $dados_compr[$dc]['cot_usuario'] = $log['usua_alterou'];
            $stt = ($dados_compr[$dc]['cot_status'] == 'A') ? 'Aberta' : 'Fechada';
            $dados_compr[$dc]['cot_prazo']  = dataDbToBr($com['cot_prazo']);
            $dados_compr[$dc]['cot_status'] = $stt;
            if ($stt == 'Aberta') {
                $chave = (($com['cot_id'] + 9523) * 4 * 12);
                $url = base_url('EstCotForn/cotac/' . $chave);
                $dados_compr[$dc]['cot_link'] = "<a href='" . $url . "'  target='_blank'>" . $url . "</a>";
            }
        }
        $compr = [
            'data' => montaListaColunas($this->data, 'cot_id', $dados_compr, 'cot_nome'),
        ];
        // cache()->save('compr', $compr, 60000);
        // }

        echo json_encode($compr);
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
        $campos = montaColunasCampos($this->data, 'cot_id', 'd');
        // debug($campos, true);
        $dados_compr = $this->cotacao->getCotacao();
        // $this->data['edicao'] = false;
        $agora = date('Y-m-d H:i:s');
        for ($dc = 0; $dc < count($dados_compr); $dc++) {
            // $dados_compr[$dc]['d'] = '';
            $com = $dados_compr[$dc];
            $log = buscaLog('est_cotacao', $com['cot_id']);
            $dados_compr[$dc]['cot_usuario'] = $log['usua_alterou'];
            $stt = ($dados_compr[$dc]['cot_status'] == 'A') ? 'Aberta' : 'Fechada';
            $dados_compr[$dc]['cot_prazo']  = dataDbToBr($com['cot_prazo']);
            $dados_compr[$dc]['cot_status'] = $stt;
            if ($stt == 'Aberta') {
                $chave = (($com['cot_id'] + 9523) * 4 * 12);
                $url = base_url('EstCotForn/cotac/' . $chave);
                $dados_compr[$dc]['cot_link'] = "<a href='" . $url . "'  target='_blank'>" . $url . "</a>";
            }
            $dados_compr[$dc]['d'] = '';
        }

        $cotac = montaListaColunas($this->data, 'cot_id', $dados_compr, $campos[1], true);
        for ($cp = 0; $cp < count($cotac); $cp++) {
            $cont = $cotac[$cp];
            $cotac[$cp]['col_details'] = [
                'tit' => ['Produto', 'Qtia', 'Und'],
                'tam' => ['col-5', 'col-2', 'col-2'],
                'cam' => ['pro_nome', 'ctp_quantia', 'und_sigla'],
            ];
            $dados_prods = $this->cotacao->getCotacaoProd($cont[0]);
            $prod = '';
            $ct =0;
            for ($p = 0; $p < count($dados_prods); $p++) {
                if($prod != $dados_prods[$p]['pro_id']){
                    $qtia = formataQuantia(isset($dados_prods[$p]['ctp_quantia']) ? $dados_prods[$p]['ctp_quantia'] : 0);
                    $dados_prods[$p]['ctp_quantia'] = $qtia['qtia'];
                    $cotac[$cp]['details'][$ct] = $dados_prods[$p];
                    $prod = $dados_prods[$p]['pro_id'];
                    $ct++;
                }
            }
        }
        $cotacao['data'] = $cotac;
        // }
        // debug($compr, true);
        echo json_encode($cotacao);
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

        $secao[0] = 'Cotação';
        $campos[0] = $this->cot_prazo;
        $campos[1] = $this->marctodos;

        $empresas = $this->empresa->getEmpresa();

        $this->data['botao']        = $this->bt_cota;
        $this->data['nome']         = 'produtos';
        $this->data['colunas']      = ['Id', 'Grupo', 'Produto'];
        for ($e = 0; $e < count($empresas); $e++) {
            array_push($this->data['colunas'], $empresas[$e]['emp_abrev']);
        }
        array_push($this->data['colunas'], 'Total');
        array_push($this->data['colunas'], 'Und');
        array_push($this->data['colunas'], 'Cotar');
        // array_push($this->data['colunas'], 'Qt Cotar');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/listaadd');
        $this->data['campos']         = $campos;
        // $this->data['camposedit']   = $camposedit;
        $this->data['destino']         = 'store';
        $this->data['metodo']         = 'filtro';
        $this->data['desc_metodo']         = 'Cotação de Produtos Solicitados';
        $this->data['script']       = "<script>carrega_lista_edit('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function listaadd()
    {
        // Busca empresas e produtos pendentes
        $empresas = $this->empresa->getEmpresa();
        $pendentes = $this->cotacao->getPedidosPendentes($empresas);

        // Identifica produtos que já estão em cotações abertas (prazo > agora)
        $agora = date('Y-m-d H:i:s');
        $cotacoesAbertas = $this->cotacao
            ->where('cot_prazo >', $agora)
            ->findAll();

        $idsEmCotacao = [];
        foreach ($cotacoesAbertas as $cot) {
            // Busca produtos dessa cotação aberta
            $produtosCot = $this->cotacao->getCotacaoProd($cot['cot_id']);
            foreach ($produtosCot as $pc) {
                $idsEmCotacao[] = $pc['pro_id'];
            }
        }
        $idsEmCotacao = array_unique($idsEmCotacao);

        // Filtra pendentes removendo produtos em cotação aberta
        $pendentes = array_filter($pendentes, function ($prod) use ($idsEmCotacao) {
            return !in_array($prod['pro_id'], $idsEmCotacao);
        });

        // Monta colunas dinâmicas
        // $campos = ['pro_id', 'grc_id', 'grc_nome', 'pro_nome'];
        $campos = ['pro_id', 'grc_nome', 'pro_nome'];
        foreach ($empresas as $emp) {
            $campos[] = 'qt_' . strtolower($emp['emp_abrev']);
        }
        $campos[] = 'total';
        $campos[] = 'und';
        $campos[] = 'Cotar';

        // Prepara dados para a listagem
        $dados_cotac = [];
        foreach (array_values($pendentes) as $dc => $prod) {
            $dados_cotac[$dc] = [
                'pro_id'   => $prod['pro_id'],
                // 'grc_id'   => $prod['grc_id'],
                'grc_nome' => $prod['grc_nome'],
                'pro_nome' => $prod['pro_nome'],
            ];
            $total = 0;
            foreach ($empresas as $emp) {
                $sigla = strtolower($emp['emp_abrev']);
                $qt = $prod[$emp['emp_abrev']] ?? '';
                $dados_cotac[$dc]['qt_' . $sigla] = $qt;
                $total += is_numeric($qt) ? (float)$qt : 0;
            }
            $dados_cotac[$dc]['total'] = $total;
            $dados_cotac[$dc]['und']   = $prod['und_sigla_compra'];
            $simnao[1] = 'Sim';
            $simnao[0] = 'Não';
            $cot        = new MyCampo();
            $cot->id = $cot->nome = "Cot_{$prod['pro_id']}";
            $cot->label = '';
            $cot->valor = $cot->selecionado = 0;
            $cot->opcoes = $simnao;
            $cot->classep = 'mark';
            $this->cotar = $cot->cr2opcoes();
            $dados_cotac[$dc]['Cotar']   = $this->cotar;
        }

        // Monta JSON de retorno
        $cotac = [
            'data' => montaListaEditColunas(
                $campos,
                'pro_id',
                $dados_cotac,
                $campos[1]
            ),
        ];

        echo json_encode($cotac);
    }


    /**
     * edição
     * edit
     *
     * @return void
     */
    public function edit($id)
    {
        $dados_cot = $this->cotacao->getCotacao($id)[0];

        $this->def_campos_lista(2, $dados_cot);

        $secao[0] = 'Cotação';
        $campos[0][0] = $this->cot_id;
        $campos[0][1] = $this->cot_prazo;

        // debug($campos, true);
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'storeedit';
        $this->data['desc_edicao'] = $dados_cot['cot_nome'];

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('est_cotacao', $id);

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
            $dados_com = $this->cotacao->getCotacao($id)[0];
            $this->cotacao->delete($id);
            $com_exc = $this->common->deleteReg('dbEstoque', 'est_cotacao_produto', "cot_id = " . $id);

            $ret['erro'] = false;
            $ret['msg']  = 'Cotacao Excluída com Sucesso';
            session()->setFlashdata('msg', 'Cotacao Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Cotacao, Verifique!<br>';
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
        $compr = new MyCampo('est_cotacao', 'cot_id');
        $compr->ordem = $ord;
        $compr->valor = isset($dados['cot_id']) ? $dados['cot_id'] : '';
        $this->cot_id = $compr->crOculto();

        $proco = new MyCampo('est_cotacao_produto', 'ctp_id');
        $proco->ordem = $ord;
        $proco->valor = isset($dados['ctp_id']) ? $dados['ctp_id'] : '';
        $this->ctp_id = $proco->crOculto();

        $proid = new MyCampo('est_cotacao_produto', 'pro_id');
        $proid->ordem = $ord;
        $proid->valor = $dados['pro_id'];
        $this->pro_id = $proid->crOculto();

        $qtia = formataQuantia(isset($dados['ctp_quantia']) ? $dados['ctp_quantia'] : 0);

        $qti                        = new MyCampo('est_cotacao_produto', 'ctp_quantia');
        $qti->tipo                  = 'quantia';
        $qti->label                 = '';
        $qti->ordem                 = $ord;
        $qti->largura               = 10;
        $qti->valor                 = $qtia['qtiv'];
        $qti->decimal               = $qtia['dec'];
        $qti->dispForm              = 'intd';
        $qti->place                 = '';
        $this->ctp_quantia          = $qti->crInput();
    }

    // No mesmo controller onde está a função listaadd:
    public function geraCotacoesPorGrupo()
    {
        $ret = ['erro' => false];
        $dados = $this->request->getPost();
        // debug($dados, true);

        // Obtém a lista de empresas e produtos pendentes para cotação
        $empresas = $this->empresa->getEmpresa();
        $pendentes = $this->cotacao->getPedidosPendentes($empresas);
        // debug($pendentes);

        $produtosSelecionados = $dados['produtos'] ?? [];

        // Limpa os IDs para remover o sufixo [0]
        $produtosSelecionados = array_map(function ($id) {
            return (int) preg_replace('/\[\d+\]$/', '', $id);
        }, $produtosSelecionados);

        // debug($produtosSelecionados, );

        if (empty($produtosSelecionados)) {
            $ret['erro'] = true;
            $ret['msg'] = 'Nenhum produto foi selecionado para cotação.';
            echo json_encode($ret);
            return;
        }

        // Filtra os pendentes
        $pendentesFiltrados = array_filter($pendentes, function ($prod) use ($produtosSelecionados) {
            return in_array($prod['pro_id'], $produtosSelecionados);
        });

        // Soma as quantias e adiciona ao array
        $pendentes = array_map(function ($prod) {
            $campos = ['TSS', 'YAV', 'YCB', 'YSC', 'DAV', 'DJV', 'DSF', 'CP'];
            $quantia = 0;

            foreach ($campos as $campo) {
                $valor = isset($prod[$campo]) && is_numeric($prod[$campo]) ? (float)$prod[$campo] : 0;
                $quantia += $valor;
            }

            $prod['quantia'] = $quantia;
            return $prod;
        }, $pendentesFiltrados);

        // debug($pendentes, true);

        if (empty($pendentes)) {
            $ret['erro'] = true;
            $ret['msg'] = 'Os produtos selecionados não estão disponíveis para cotação.';
            echo json_encode($ret);
            return;
        }

        // Agrupa os produtos por grc_id
        $grupos = [];
        foreach ($pendentes as $prod) {
            $grc = $prod['grc_id'];
            if (!isset($grupos[$grc])) {
                $grupos[$grc] = [
                    'grc_nome' => $prod['grc_nome'],
                    'items'    => []
                ];
            }
            $grupos[$grc]['items'][] = $prod;
        }

        $cotacoesCriadas = [];
        foreach ($grupos as $grc_id => $grupo) {
            // Monta dados da cotação para o grupo
            $dados_cot = [
                'cot_data'   => date('Y-m-d'),
                'cot_prazo'  => $dados['cot_prazo'] ?? null,
                'cot_status' => 'A',
                'cot_nome'   => $grupo['grc_nome'] . ' - ' . date('d/m/Y'),
            ];

            // Salva a cotação do grupo
            if (!$this->cotacao->save($dados_cot)) {
                $erros = $this->cotacao->errors();
                $ret['erro'] = true;
                $ret['msg']  = "Erro ao salvar cotação do grupo {$grupo['grc_nome']}: " . implode('<br>', $erros);
                echo json_encode($ret);
                return;
            }

            $cot_id = $this->cotacao->getInsertID();

            // Insere cada produto do grupo na tabela est_cotacao_produto
            foreach ($grupo['items'] as $prod) {
                $dados_pro = [
                    'cot_id' => $cot_id,
                    'pro_id' => $prod['pro_id'],
                    'ctp_quantia' => $prod['quantia'],
                ];

                if (!$this->common->insertReg('dbEstoque', 'est_cotacao_produto', $dados_pro)) {
                    // Se falhar ao inserir produtos, remove a cotação recém-criada
                    $this->cotacao->delete(['cot_id' => $cot_id]);
                    $ret['erro'] = true;
                    $ret['msg']  = "Falha ao gravar produtos da cotação {$grupo['grc_nome']}.";
                    echo json_encode($ret);
                    return;
                }
            }

            $cotacoesCriadas[] = $cot_id;
        }

        $ret['erro']      = false;
        $ret['cotacoes']  = $cotacoesCriadas;
        $ret['msg']       = 'Cotações criadas com sucesso!';
        session()->setFlashdata('msg', $ret['msg']);
        $ret['url'] = site_url($this->data['controler']);
        echo json_encode($ret);
    }

    public function storeforn()
    {
        $ret = [];
        $dados = $this->request->getPost();
        // debug($dados);

        $cofDados = [
            'cot_id'       => $dados['cotacao'],
            'cop_id'       => $dados['cop_id'],
            'pro_id'       => $dados['produto'],
            'mar_id'       => $dados['marca'],
            'for_id'       => $dados['for_id'],
            'cof_preco'    => str_replace(',', '.', $dados['preco']),
            'cof_validade' => $dados['validade'],
        ];
        if ($dados['cof_id'] != '') {
            $ret['ok'] = $this->cotacao_fornec->update($dados['cof_id'], $cofDados);
        } else {
            $ret['ok'] = $this->cotacao_fornec->insert($cofDados);
        }

        if (!$ret['ok']) {
            $erros = $this->cotacao->errors();
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Cotacao, Verifique!';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['erro'] = false;
            $ret['msg'] = 'Cotacao gravada com Sucesso!!!';
            // session()->setFlashdata('msg', $ret['msg']);
        }

        return json_encode($ret);
    }

    /**
     * Gravação de alteração
     * storeedit
     *
     * @return void
     */
    public function storeedit()
    {
        $ret = [];
        $ret['erro'] = false;
        $dados = $this->request->getPost();

        $status = 'F';
        $hoje = new DateTime();
        $prazo = new DateTime($dados['cot_prazo']);

        if ($prazo > $hoje) {
            $status = 'A';
        }

        $dados_cot = [
            'cot_id'  => $dados['cot_id'],
            'cot_prazo'  => $dados['cot_prazo'],
            'cot_status' => $status
        ];
        if ($this->cotacao->save($dados_cot)) {
            $ret['erro'] = false;
            $ret['cot_id'] = $dados['cot_id'];
            $ret['msg'] = 'Cotação alterada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $erros = $this->cotacao->errors();
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível alterar a Cotação, Verifique!';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
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
        // debug($dados);
        $soma = array_sum($dados['ctp_quantia']);
        if ($soma == 0) {
            $ret['erro'] = true;
            $ret['msg'] = 'Informe pelo menos 1 produto para Cotação';
        }
        $erros = [];
        if (!$ret['erro']) {
            $dados_cot = [
                'cot_data'  => date('Y-m-d'),
                'cot_prazo'  => $dados['cot_prazo'],
                'cot_status' => 'A'
            ];
            if ($this->cotacao->save($dados_cot)) {
                $cot_id = $this->cotacao->getInsertID();
                for ($p = 0; $p < count($dados['ctp_quantia']); $p++) {
                    if ($dados['ctp_quantia'][$p] > 0) {
                        $dados_pro = [
                            'ctp_id'    => $dados['ctp_id'][$p],
                            'cot_id'    => $cot_id,
                            'pro_id'    => $dados['pro_id'][$p],
                            'ctp_quantia'   => $dados['ctp_quantia'][$p],
                        ];
                        // debug($dados_pro);
                        $cotaa = $this->common->insertReg('dbEstoque', 'est_cotacao_produto', $dados_pro);
                        if (!$cotaa) {
                            $this->cotacao->delete(['cot_id' => $cot_id]);
                            $ret['erro'] = true;
                            $ret['msg'] = 'Não foi possível gravar os produtos, Verifique!';
                        }
                    }
                }
            }
            if ($ret['erro']) {
                $erros = $this->cotacao->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar a Cotacao, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            } else {
                $ret['erro'] = false;
                $ret['cot_id'] = $cot_id;
                $ret['msg'] = 'Cotacao gravada com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            }
        }
        echo json_encode($ret);
    }
}
