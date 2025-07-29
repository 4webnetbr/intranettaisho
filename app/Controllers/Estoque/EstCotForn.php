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
use Config\Database;
use DateTime;

class EstCotForn extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $cotacao;
    public $cotacao_fornec;
    public $produtos;
    public $fornecedor;
    public $common;
    public $empresa;
    public $pedido;

    public $cot_id;

    public $for_cnpj;
    public $for_id;
    public $for_minimo;
    public $for_nome;
    public $bt_salv;
    public $ctp_id;
    public $pro_id;
    public $mar_id;
    public $cof_preco;
    public $cof_validade;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        // $this->permissao = $this->data['permissao'];
        $this->cotacao = new EstoquCotacaoModel();
        $this->cotacao_fornec = new EstoquCotacaoFornecModel();
        $this->produtos = new EstoquProdutoModel();
        $this->fornecedor = new EstoquFornecedorModel();
        $this->common    = new CommonModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->pedido       = new EstoquPedidoModel();
    }

    public function index($id)
    {
        $this->cotac($id);
    }
    /**
     * Tela de Abertura
     * index
     */
    public function cotac($id)
    {
        // desfaz a criptografia do ID
        $id12 = (intval($id) / 12);
        $id4 = (intval($id12) / 4);
        $idreal = (intval($id4) - 9523);

        $cotacao = $this->cotacao->getCotacao($idreal);
        // debug($cotacao);
        // debug('passei aqui');
        $this->def_campos_lista($idreal);

        $secao[0]  = 'Cotação';
        $campos[0] = $this->cot_id;
        $campos[1] = "<h3>Cotação " . $cotacao[0]['cot_nome'] . " - <span class='text-danger'> Encerra em: ".dataDbToBr($cotacao[0]['cot_prazo'])."</span></h3>";
        if ($cotacao[0]['cot_status'] == 'A') {
            $campos[2] = "<h4>Informe o CNPJ, os preços e data de validade de cada produto</h4>";
            $campos[3] = "<div class='text-italic fs-7 text-danger'>** As quantias se referem ao Total que será comprado, podendo ser porcionadas nas diversas Unidades Taisho</div>";
            $campos[4] = $this->for_cnpj;
            $campos[5] = $this->for_nome;
            $campos[6] = $this->for_id;
            $campos[7] = $this->for_minimo;
            $campos[8] = $this->bt_salv;
        } else {
            $campos[2] = "<h4>Essa cotação fechou em " . dataDbToBr($cotacao[0]['cot_prazo']) . " </h4>";
            $campos[3] = '';
            $campos[4] = '';
        }

        $this->data['campos']         = $campos;
        $this->data['controler']    = '';
        $this->data['desc_metodo']  = 'Preços de Fornecedor para ';
        $this->data['destino']         = 'EstCotForn/store';
        // if ($cotacao[0]['cot_status'] == 'A') {
        //     $this->data['script']       = "<script>carregaCotacao();</script>";
        // }
        echo view('vw_cotacao_forn', $this->data);
    }

    public function def_campos_lista($cot_id)
    {

        $compr = new MyCampo('est_cotacao', 'cot_id');
        $compr->valor = $cot_id;
        $this->cot_id = $compr->crOculto();

        $cnpj                        = new MyCampo('est_fornecedor', 'for_cnpj');
        $cnpj->valor                 = '';
        $cnpj->dispForm              = 'col-12 col-lg-2';
        $cnpj->funcBlur            = 'buscaCNPJ(this);carregaCotacao();';
        $this->for_cnpj            = $cnpj->crInput();

        $forid                        = new MyCampo('est_fornecedor', 'for_id');
        $forid->valor                 = '';
        $this->for_id            = $forid->crOculto();

        $mini                        = new MyCampo('est_fornecedor', 'for_minimo');
        $mini->valor                 = 0;
        $mini->dispForm              = 'col-8 col-lg-2';
        $this->for_minimo            = $mini->crInput();

        $nome                        = new MyCampo('est_fornecedor', 'for_razao');
        $nome->valor                 = '';
        $nome->dispForm              = 'col-12 col-lg-4';
        $nome->leitura              = true;
        $this->for_nome            = $nome->crInput();

        $salv          = new MyCampo();
        $salv->nome    = 'bt_salv';
        $salv->id      = 'bt_salv';
        $salv->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                              <i class="fa-solid fa-save" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $salv->i_cone  .= '<div class="align-items-start txt-bt-manut">Salvar</div>';
        $salv->place    = 'Salvar';
        $salv->funcChan = 'gravaCotforn()';
        $salv->classep  = 'btn-primary bt-manut btn-sm mb-2 float-end';
        $this->bt_salv = $salv->crBotao();
    }

    public function listaforn()
    {
        $cotacao = $_REQUEST['cotacao'];
        $fornecedor = $_REQUEST['fornecedor'];
        if ($cotacao == 'undefined') {
            $cotacao = false;
        }
        if ($fornecedor == 'undefined') {
            $fornecedor = false;
        }
        $cotacao = $this->cotacao->getCotacaoProd($cotacao);

        $campos = [];
        $campos[count($campos)] = 'cot_id';
        $campos[count($campos)] = 'cop_id';
        $campos[count($campos)] = 'pro_id';
        $campos[count($campos)] = 'pro_nome';
        $campos[count($campos)] = 'mar_nome';
        // $campos[count($campos)] = 'mar_apresenta';
        $campos[count($campos)] = 'und_sigla';
        $campos[count($campos)] = 'und_sigla_compra';
        $campos[count($campos)] = 'ctp_quantia';
        $campos[count($campos)] = 'cof_preco';
        $campos[count($campos)] = 'cof_precoundcompra';
        $campos[count($campos)] = 'cof_validade';
        $campos[count($campos)] = 'cof_previsao';
        $campos[count($campos)] = 'cof_observacao';

        $dados_cotac = [];
        for ($dc = 0; $dc < count($cotacao); $dc++) {
            $prod = $cotacao[$dc];
            // debug($prod);
            $dados_cotac[$dc]['cot_id']      = $prod['cot_id'];
            $dados_cotac[$dc]['cop_id']      = $prod['ctp_id'];
            $dados_cotac[$dc]['pro_id']      = $prod['pro_id'];
            $dados_cotac[$dc]['pro_nome']    = $prod['pro_nome'];
            $dados_cotac[$dc]['mar_id']      = $prod['mar_id'];
            $dados_cotac[$dc]['mar_nome']    = $prod['mar_nome'] ?? 'SEM MARCA';
            // $dados_cotac[$dc]['mar_apresenta']    = $prod['mar_apresenta'] ?? '';
            $dados_cotac[$dc]['und_sigla']    = $prod['und_sigla'];
            $dados_cotac[$dc]['und_sigla_compra']    = $prod['und_sigla_compra'];
            $dados_cotac[$dc]['ctp_quantia']    = "<div class='text-start'>" .formataQuantia($prod['ctp_quantia'])['qtis']."</div>";

            $this->def_campos_prod($prod, $dc, $fornecedor);

            $dados_cotac[$dc]['cof_preco'] = $this->cof_preco;
            $dados_cotac[$dc]['cof_precoundcompra'] = $this->cof_precoundcompra;
            $dados_cotac[$dc]['cof_validade'] = $this->pro_id . ' ' . $this->mar_id . ' ' . $this->ctp_id . ' ' .$this->cof_validade;
            $dados_cotac[$dc]['cof_previsao'] = $this->cof_previsao;
            $dados_cotac[$dc]['cof_observacao'] = $this->cof_observacao;
        }
        // debug($dados_cotac);
        $cotac = [
            'data' => montaListaEditColunas($campos, 'pro_id', $dados_cotac, $campos[1]),
        ];

        echo json_encode($cotac);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_prod($dados = false, $ord = 0, $fornecedor = false)
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

        $marid = new MyCampo('est_cotacao_fornec', 'mar_id');
        $marid->ordem = $ord;
        $marid->valor = $dados['mar_id'] ?? '';
        $this->mar_id = $marid->crOculto();

        $preco = 0;
        $precocompra = 0;
        $validade = '';
        $previsao = 1;
        $observacao = '';
        if ($fornecedor) {
            $dfor = $this->cotacao->getCotacaoForn($dados['cot_id'], $fornecedor, $dados['pro_id'], $dados['mar_id']);
            // debug($dfor);
            if (count($dfor) > 0) {
                $preco = $dfor[0]['cof_preco'];
                $precocompra = $dfor[0]['cof_precoundcompra'];
                $validade = $dfor[0]['cof_validade'];
                $previsao = $dfor[0]['cof_previsao'];
                $observacao = $dfor[0]['cof_observacao'];
            }
        }

        $prec                        = new MyCampo('est_cotacao_fornec', 'cof_preco');
        $prec->label                 = '';
        $prec->ordem                 = $ord;
        $prec->largura               = 20;
        $prec->valor                 = $preco;
        $prec->dispForm              = 'inte';
        $prec->place                 = '';
        $this->cof_preco          = $prec->crInput();

        $pruc                        = new MyCampo('est_cotacao_fornec', 'cof_precoundcompra');
        $pruc->label                 = '';
        $pruc->ordem                 = $ord;
        $pruc->largura               = 20;
        $pruc->valor                 = $precocompra;
        $pruc->dispForm              = 'inte';
        $pruc->place                 = '';
        $this->cof_precoundcompra          = $pruc->crInput();

        $vali                        = new MyCampo('est_cotacao_fornec', 'cof_validade');
        $vali->label                 = '';
        $vali->ordem                 = $ord;
        $vali->largura               = 20;
        $vali->valor                 = $validade;
        $vali->dispForm              = 'inte';
        $vali->place                 = '';
        $this->cof_validade          = $vali->crInput();

        $prev                        = new MyCampo('est_cotacao_fornec', 'cof_previsao');
        $prev->label                 = '';
        $prev->ordem                 = $ord;
        $prev->largura               = 20;
        $prev->valor                 = $previsao;
        $prev->dispForm              = 'inte';
        $prev->place                 = '';
        $this->cof_previsao          = $prev->crInput();


        $obsv                        = new MyCampo('est_cotacao_fornec', 'cof_observacao');
        $obsv->label                 = '';
        $obsv->ordem                 = $ord;
        $obsv->largura               = 200;
        $obsv->colunas               = 200;
        $obsv->linhas                = 1;
        if(isMobile()){
            $obsv->largura               = 40;
            $obsv->colunas               = 80;
            $obsv->linhas                = 3;
        }
        $obsv->valor                 = $observacao;
        $obsv->dispForm              = 'col-9';
        $obsv->place                 = '';
        $this->cof_observacao          = $obsv->crTexto();
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
        $dados = $this->request->getPost();
        // debug($dados, true);

        // try {
        $for_id = $dados['for_id'] ?? null;
        $for_minimo = $dados['for_minimo'] ?? null;
        $cot_id = $dados['cot_id'] ?? null;

        if (!$for_id || $for_minimo === null || !$cot_id) {
            throw new \Exception('Campos obrigatórios ausentes');
        }

        $this->fornecedor->update($for_id, ['for_minimo' => $for_minimo]);

        $this->cotacao_fornec->excluirPorCotacaoEFornecedor($cot_id, $for_id);

        $cofDados = [];

        // debug($dados['pro_id'], true);
        foreach ($dados['pro_id'] as $key => $chave) {
            $cop_id = $dados['ctp_id'][$key];
            $pro_id = $dados['pro_id'][$key];
            $mar_id = $dados['mar_id'][$key];
            $preco  = str_replace(',', '.', $dados['cof_precoundcompra'][$key]);
            $precocompra  = str_replace(',', '.', $dados['cof_precoundcompra'][$key]);
            if(isset($dados['cof_preco'][$key])){
                $preco  = str_replace(',', '.', $dados['cof_preco'][$key]);
            }
            $validade = $dados['cof_validade'][$key];
            $previsao = $dados['cof_previsao'][$key];
            $observacao = $dados['cof_observacao'][$key];


            $cofDados[] = [
                'cot_id'       => $cot_id,
                'cop_id'       => $cop_id,
                'pro_id'       => $pro_id,
                'mar_id'       => $mar_id,
                'for_id'       => $for_id,
                'cof_preco'    => $preco,
                'cof_precoundcompra'    => $precocompra,
                'cof_validade' => $validade,
                'cof_previsao' => $previsao,
                'cof_observacao' => $observacao,
            ];
        }

        if (!empty($cofDados)) {
            $ok = $this->cotacao_fornec->insertBatch($cofDados);
            if (!$ok) {
                throw new \Exception('Falha ao gravar cotação');
                $ret['erro'] = true;
                $ret['msg'] = 'Erro ao salvar cotação: ' . $e->getMessage();
            } else {
                $ret['erro'] = false;
                $ret['msg'] = 'Cotação salva com sucesso!';
                // $ret['url'] = site_url($this->data['controler']);
                session()->setFlashdata('msg', $ret['msg']);
            }
        }

        echo json_encode($ret);
    }
}
