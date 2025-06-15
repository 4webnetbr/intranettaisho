<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquConsumoModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use DateTime;

class EstConsumo extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $consumo;
    public $common;
    public $empresa;
    public $produto;
    public $unidades;
    public $dash_empresa;
    public $con_id;
    public $pro_id;
    public $con_duracao;
    public $con_consumo;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->consumo = new EstoquConsumoModel();
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

        $this->data['nome']         = 'consumo';
        $this->data['colunas']      = montaColunasLista($this->data, 'con_id');
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
        $emp->funcChan              = "carrega_lista(this, 'EstConsumo/lista','consumo');";
        $this->dash_empresa         = $emp->crSelect();
    }

    public function lista($empresa = false)
    {
        $param = $_REQUEST['param'] ?? false;
        if ($param === 'undefined') {
            $param = false;
        }
        $empresas   = explode(',', $param);
        $empresaId  = $empresas[0]; // cache do índice

        // Busca tudo de uma vez com join via view/método otimizado
        $produtos = $this->consumo->getConsumoProd(false, false, $empresaId);
        // debug($produtos, true);
        // Se ainda precisar formatar os valores, faça aqui (preferencialmente, evite processamento pesado dentro do loop)
        foreach ($produtos as &$prod) {
            $prod['con_duracao'] = formataQuantia($prod['con_duracao'] ?? 0, 0)['qtiv'];
            $prod['con_consumo'] = formataQuantia($prod['con_consumo'] ?? 0, 0)['qtiv'];

            // Se def_campos_prod_consumos fizer ajustes necessários, chame-o uma vez
            $this->def_campos_prod_consumos($prod, $prod['pro_id']);

            // Se os valores abaixo são obrigatórios e devem sobrescrever, mantenha, mas revise se é realmente necessário
            $prod['con_duracao'] = $this->con_id . ' ' . $this->pro_id . ' ' . $this->con_duracao;
            $prod['con_consumo'] = $this->con_consumo;
        }
        unset($prod);

        $campos = [
            'pro_id',
            'gru_nome',
            'pro_nome',
            'und_completa',
            'con_duracao',
            'con_consumo',
            'acao'
        ];

        $consu = [
            'data' => montaListaEditColunas($campos, 'pro_id', $produtos, $campos[1])
        ];

        echo json_encode($consu);
    }


    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_prod_consumos($dados = false, $ord = 0,  $show = false)
    {
        if (!$dados) {
            return;
        }
        // debug($dados, true);
        $consu = new MyCampo('est_consumo', 'con_id');
        $consu->ordem = $ord;
        $consu->valor = isset($dados['con_id']) ? $dados['con_id'] : '';
        $this->con_id = $consu->crOculto();

        $produts = [];
        $dados_pro = $this->produto->getProduto();
        $produts = array_column($dados_pro, 'pro_nome', 'pro_id');

        $proid = new MyCampo('est_consumo', 'pro_id');
        $proid->valor = $proid->selecionado = $dados['pro_id'];
        $proid->ordem = $ord;
        $proid->opcoes = $produts;
        $proid->largura = 60;
        $proid->leitura = true;
        $this->pro_id = $proid->crOculto();

        $dur                        = new MyCampo('est_consumo', 'con_duracao');
        $dur->tipo                  = 'quantia';
        $dur->ordem = $ord;
        $dur->decimal               = 0;
        $dur->label                 = '';
        $dur->valor = $dur->selecionado  = isset($dados['con_duracao']) ? $dados['con_duracao'] : 0;
        $dur->largura               = 20;
        $dur->leitura               = false;
        $dur->size                  = 5;
        $dur->funcBlur              =  'gravaConsumo(this)';
        $dur->maxLength             = 5;
        $dur->minimo                = 0;
        $dur->maximo                = 1000;
        $dur->classediv             = 'mb-0 float-end';
        $this->con_duracao             = $dur->crInput();

        $con                        = new MyCampo('est_consumo', 'con_consumo');
        $con->tipo                  = 'quantia';
        $con->ordem = $ord;
        $con->decimal               = 0;
        $con->label                 = '';
        $con->valor = $con->selecionado  = isset($dados['con_consumo']) ? $dados['con_consumo'] : 0;
        $con->largura               = 20;
        $con->leitura               = false;
        $con->size                  = 5;
        $con->funcBlur              =  'gravaConsumo(this)';
        $con->maxLength             = 5;
        $con->minimo                = 0;
        $con->maximo                = 1000;
        $con->classediv             = 'mb-0 float-end';
        $this->con_consumo             = $con->crInput();
    }

    // public function atualizaProdutosConsumo()
    // {
    //     $dados = $this->request->getPost();
    //     $empresa = $dados['emp_id'];
    //     // $campos = $this->listaadd($empresa);
    //     $ret['campos'] = $campos;
    //     echo json_encode($ret);
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
            $this->consumo->delete($id);
            $con_exc = $this->common->deleteReg('dbEstoque', 'est_consumo', "con_id = " . $id);
            $ret['erro'] = false;
            $ret['msg']  = 'Consumo Excluído com Sucesso';
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Consumo, Verifique!<br>';
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
        $erros = [];
        $data = new  \DateTime();
        if (!$ret['erro']) {
            if ($dados['con_id'] == '') {
                $jatem = $this->consumo->getConsumoProd($dados['con_id'], $dados['pro_id'], $dados['emp_id']);
                if ($jatem) {
                    $dados['con_id'] = $jatem[0]['con_id'];
                }
            }
            $dados_con = [
                'con_id'    => $dados['con_id'],
                'emp_id'    => $dados['emp_id'],
                'pro_id'    => $dados['pro_id'],
                'con_consumo'  => $dados['con_consumo'],
                'con_duracao'  => $dados['con_duracao'],
            ];
            // debug($dados_con);
            if ($this->consumo->save($dados_con)) {
                if ($dados['con_id'] == '') {
                    $con_id = $this->consumo->getInsertID();
                } else {
                    $con_id = $dados['con_id'];
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Consumo gravado com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['id'] = $con_id;
            } else {
                $erros = $this->consumo->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar o Consumo, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }
        echo json_encode($ret);
    }
}
