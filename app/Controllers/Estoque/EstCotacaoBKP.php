<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquCotacaoModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquGrupoCompraModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstCotacao extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $cotacao;
    public $produtos;
    public $grupocompra;
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
        $this->cotacao = new EstoquCotacaoModel();
        $this->produtos = new EstoquProdutoModel();
        $this->grupocompra  = new EstoquGrupoCompraModel();
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

        $this->data['nome']         = 'cotacao';
        $this->data['colunas']      = montaColunasLista($this->data, 'cot_id');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista');
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

        $agora = new DateTime();
        $agora->modify('+24 hours');
        $termina = $agora->format('Y-m-d H:i:s');
        $praz                       = new MyCampo('est_cotacao', 'cot_prazo');
        $praz->valor                = $termina;
        $this->cot_prazo            = $praz->crInput();

        $dados_grc = $this->grupocompra->getGrupocompra();
        $grucs = array_column($dados_grc, 'grc_nome', 'grc_id');
        $todos = ['0' => 'Todos'];
        $grucs = $todos + $grucs;

        $grc                    = new MyCampo('est_produto', 'grc_id');
        $grc->selecionado       = isset($dados['grc_id']) ? $dados['grc_id'] : '0';
        $grc->opcoes            = $grucs;
        $grc->largura           = 50;
        $grc->funcChan          = "carrega_lista_edit(this, '" . base_url($this->data['controler'] . '/listaadd') . "','produtos');";
        $this->grc_id           = $grc->crSelect();
    }

    public function atualizaProdutosCotacao()
    {
        $dados = $this->request->getPost();
        $empresa = $dados['emp_id'];
        $grupocompra = $dados['grc_id'];
        $campos = $this->listaadd("param=$grupocompra");
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
        // $campos = montaColunasCampos($this->data, 'cot_id');
        // debug($campos, true);
        $this->data['edicao'] = false;
        $dados_compr = $this->cotacao->getCotacao();
        for ($dc = 0; $dc < count($dados_compr); $dc++) {
            // $dados_compr[$dc]['d'] = '';
            $com = $dados_compr[$dc];
            $log = buscaLog('est_cotacao', $com['cot_id']);
            $dados_compr[$dc]['cot_usuario'] = $log['usua_alterou'];
            $stt = ($dados_compr[$dc]['cot_status'] == 'A') ? 'Aberta' : 'Fechada';
            $dados_compr[$dc]['cot_status'] = $stt;
            $dados_compr[$dc]['cot_link'] = "<a href='" . base_url('EstCotForn/cotac/' . (($com['cot_id'] + 9523) * 4 * 12)) . "' target='_blank'>" . base_url('EstCotacao/forn/' . (($com['cot_id'] + 9523) * 4 * 12)) . "</a>";
        }
        $compr = [
            'data' => montaListaColunas($this->data, 'cot_id', $dados_compr, 'cot_data'),
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

        $secao[0] = 'Cotação';
        $campos[0] = $this->cot_prazo;
        $campos[1] = $this->grc_id;

        $empresas = $this->empresa->getEmpresa();

        $this->data['nome']         = 'produtos';
        $this->data['colunas']      = ['Id', 'Grupo', 'Produto'];
        for ($e = 0; $e < count($empresas); $e++) {
            array_push($this->data['colunas'], $empresas[$e]['emp_abrev']);
        }
        array_push($this->data['colunas'], 'Total');
        array_push($this->data['colunas'], 'Und');
        array_push($this->data['colunas'], 'Cotar?');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/listaadd');
        $this->data['campos']         = $campos;
        // $this->data['camposedit']   = $camposedit;  
        $this->data['destino']         = 'store';
        $this->data['script']       = "<script>carrega_lista_edit('grc_id', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function listaadd()
    {
        $dados = $this->request->getGet();
        $empresas = $this->empresa->getEmpresa();
        $grupo = $dados['param'];
        $pendentes = $this->cotacao->getPedidosPendentes($empresas, $grupo);
        // debug($produtos);

        $campos[0] = 'pro_id';
        $campos[1] = 'grc_nome';
        $campos[2] = 'pro_nome';
        for ($e = 0; $e < count($empresas); $e++) {
            array_push($campos, 'qt_' . strtolower($empresas[$e]['emp_abrev']));
        }
        array_push($campos, 'total');
        array_push($campos, 'und');
        array_push($campos, 'cotar');

        $dados_cotac = [];
        for ($dc = 0; $dc < count($pendentes); $dc++) {
            $prod = $pendentes[$dc];
            $dados_cotac[$dc]['pro_id']      = $prod['pro_id'];
            $dados_cotac[$dc]['grc_nome']    = $prod['grc_nome'];
            $dados_cotac[$dc]['pro_nome']    = $prod['pro_nome'];
            $total = 0;
            for ($e = 0; $e < count($empresas); $e++) {
                $emp = $empresas[$e]['emp_abrev'];
                $dados_cotac[$dc]['qt_' . strtolower($emp)]    = $prod[$emp];
                $total = $total + $prod[$emp];
            }
            $dados_cotac[$dc]['total']        = $total;
            $dados_cotac[$dc]['und']          = $prod['und_sigla_compra'];

            $this->def_campos_prod($prod, $dc);

            $dados_cotac[$dc]['cotar'] = $this->cot_id . ' ' . $this->pro_id . ' ' . $this->ctp_id . ' ' . $this->ctp_quantia;
        }
        $cotac = [
            'data' => montaListaEditColunas($campos, 'pro_id', $dados_cotac, $campos[1]),
        ];
        // debug($pedid, true);
        // cache()->save('pedid', $pedid, 60000);
        // }

        echo json_encode($cotac);
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
            $pedido = $dados_com['ped_id'];

            $this->cotacao->delete($id);
            $cot_exc = $this->common->deleteReg('dbEstoque', 'est_cotacao_produto', "cot_id = " . $id);
            $dados_ped = [
                'ped_id' => $pedido,
                'ped_status' => 'P',
            ];
            $this->pedido->save($dados_ped);

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
                        $salva = $this->common->insertReg('dbEstoque', 'est_cotacao_produto', $dados_pro);
                        if (!$salva) {
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
                $ret['url'] = site_url($this->data['controler']);
            }
        }
        echo json_encode($ret);
    }
}
