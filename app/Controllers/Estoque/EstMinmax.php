<?php

namespace App\Controllers\Estoque;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Estoqu\EstoquMinmaxModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class EstMinmax extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $minmax;
    public $common;
    public $empresa;
    public $produto;
    public $unidades;
    public $dash_empresa;

    public $mmi_id;
    public $mmi_minimo;
    public $mmi_maximo;
    public $pro_id;

    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->minmax = new EstoquMinmaxModel();
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

        $this->data['nome']         = 'minmax';
        $this->data['colunas']      = montaColunasLista($this->data, 'mmi_id');
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
        $emp->funcChan              = "carrega_lista(this, 'EstMinmax/lista','minmax');";
        $this->dash_empresa         = $emp->crSelect();
    }


    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista($empresa = false)
    {
        $param = $_REQUEST['param'] ?? false;
        if ($param === 'undefined') {
            $param = false;
        }
        $empresas   = explode(',', $param);
        $empresaId  = $empresas[0]; // cache do índice

        // Busca tudo de uma vez com join via view/método otimizado
        $produtos = $this->minmax->getMinmax($empresaId);
        // debug($produtos, true);
        // Se ainda precisar formatar os valores, faça aqui (preferencialmente, evite processamento pesado dentro do loop)
        foreach ($produtos as &$prod) {
            $prod['mmi_minimo'] = formataQuantia($prod['mmi_minimo'] ?? 0, 0)['qtiv'];
            $prod['mmi_maximo'] = formataQuantia($prod['mmi_maximo'] ?? 0, 0)['qtiv'];

            // Se def_campos_prod_consumos fizer ajustes necessários, chame-o uma vez
            $this->def_campos_prod_minmax($prod, $prod['pro_id']);

            // Se os valores abaixo são obrigatórios e devem sobrescrever, mantenha, mas revise se é realmente necessário
            $prod['mmi_minimo'] = $this->mmi_id . ' ' . $this->pro_id . ' ' . $this->mmi_minimo;
            $prod['mmi_maximo'] = $this->mmi_maximo;
        }
        unset($prod);

        $campos = [
            'pro_id',
            'gru_nome',
            'pro_nome',
            'und_completa',
            'mmi_minimo',
            'mmi_maximo',
            'acao'
        ];

        $consu = [
            'data' => montaListaEditColunas($campos, 'pro_id', $produtos, $campos[1])
        ];

        echo json_encode($consu);
    }

    public function def_campos_prod_minmax($dados = false, $ord = 0,  $show = false)
    {
        if (!$dados) {
            return;
        }
        // debug($dados, true);
        $mmi = new MyCampo('est_minmax', 'mmi_id');
        $mmi->ordem = $ord;
        $mmi->valor = isset($dados['mmi_id']) ? $dados['mmi_id'] : '';
        $this->mmi_id = $mmi->crOculto();

        $produts = [];
        $dados_pro = $this->produto->getProduto();
        $produts = array_column($dados_pro, 'pro_nome', 'pro_id');

        $proid = new MyCampo('est_minmax', 'pro_id');
        $proid->valor = $proid->selecionado = $dados['pro_id'];
        $proid->ordem = $ord;
        $proid->opcoes = $produts;
        $proid->largura = 60;
        $proid->leitura = true;
        $this->pro_id = $proid->crOculto();

        $minimo = formataQuantia($dados['mmi_minimo'] ?? 0);
        $min                        = new MyCampo('est_minmax', 'mmi_minimo');
        $min->tipo                  = 'quantia';
        $min->ordem = $ord;
        $min->decimal               = $minimo['dec'];
        $min->label                 = '';
        $min->valor = $min->selecionado  = $minimo['qtis'];
        $min->largura               = 20;
        $min->leitura               = false;
        $min->size                  = 5;
        $min->funcBlur              =  'gravaMinmax(this)';
        $min->maxLength             = 5;
        $min->minimo                = 0;
        $min->maximo                = 1000;
        $min->classediv             = 'mb-0 float-end';
        $this->mmi_minimo             = $min->crInput();

        $maximo = formataQuantia($dados['mmi_maximo'] ?? 0);
        $max                        = new MyCampo('est_minmax', 'mmi_maximo');
        $max->tipo                  = 'quantia';
        $max->ordem = $ord;
        $max->decimal               = $maximo['dec'];
        $max->label                 = '';
        $max->valor = $max->selecionado  = $maximo['qtis'];
        $max->largura               = 20;
        $max->leitura               = false;
        $max->size                  = 5;
        $max->funcBlur              =  'gravaMinmax(this)';
        $max->maxLength             = 5;
        $max->minimo                = 0;
        $max->maximo                = 1000;
        $max->classediv             = 'mb-0 float-end';
        $this->mmi_maximo             = $max->crInput();
    }

    //     return $campospr;
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
            $this->minmax->delete($id);
            $mmi_exc = $this->common->deleteReg('dbEstoque', 'est_minmax', "mmi_id = " . $id);
            $ret['erro'] = false;
            $ret['msg']  = 'Minmax Excluído com Sucesso';
            // session()->setFlashdata('msg', 'Minmax Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir a Minmax, Verifique!<br>';
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
            if ($dados['mmi_id'] == '') {
                $jatem = $this->minmax->getMinmax($dados['emp_id'], $dados['pro_id']);
                if ($jatem) {
                    $dados['mmi_id'] = $jatem[0]['mmi_id'];
                }
            }
            $dados_mmi = [
                'mmi_id'    => $dados['mmi_id'],
                'emp_id'    => $dados['emp_id'],
                'pro_id'    => $dados['pro_id'],
                'mmi_minimo'  => str_replace(',', '.', $dados['mmi_minimo']),
                'mmi_maximo'  => str_replace(',', '.', $dados['mmi_maximo']),
            ];
            if ($this->minmax->save($dados_mmi)) {
                if ($dados['mmi_id'] == '') {
                    $mmi_id = $this->minmax->getInsertID();
                } else {
                    $mmi_id = $dados_mmi['mmi_id'];
                }
                $ret['erro'] = false;
                $ret['msg'] = 'Mínimo e Máximo gravado com Sucesso!!!';
                // session()->setFlashdata('msg', $ret['msg']);
                $ret['id'] = $mmi_id;
            } else {
                $erros = $this->minmax->errors();
                $ret['erro'] = true;
                $ret['msg'] = 'Não foi possível gravar o Mínimo e Máximo, Verifique!';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro . '<br>';
                }
            }
        }
        echo json_encode($ret);
    }
}
