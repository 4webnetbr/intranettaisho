<?php namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumColaboradorModel;
use App\Models\Rechum\RechumValeModel;
use DateTime;

class RhVale extends BaseController {	
    public $data = [];
    public $permissao = '';
    public $common;
    public $vale;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->vale       = new RechumValeModel(); 
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
        $this->data['colunas'] = montaColunasLista($this->data,'val_id');
        $this->data['url_lista'] = base_url($this->data['controler'].'/lista');
        echo view('vw_lista', $this->data);
    }

    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        // $empresas = explode(',',session()->get('usu_empresa'));

            $dados_vales = $this->vale->getVale();
            $vales = [             
                'data' => montaListaColunas($this->data,'val_id',$dados_vales,'col_nome'),
            ];
        // }
        echo json_encode($vales);
    }
    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add($modal = false)
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->val_data;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->col_id;
        $campos[0][3] = $this->val_valor;
        $campos[0][4] = $this->val_parcela;
        $campos[0][5] = $this->val_id;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            echo view('vw_edicao_modal', $this->data);
        }
    }

    /**
     * Consulta
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
        $dados_vale = $this->vale->getVale($id);
        // debug($dados_vale);
        $this->def_campos($dados_vale[0], 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->val_data;
        $campos[0][1] = $this->emp_id;
        $campos[0][2] = $this->col_id;
        $campos[0][3] = $this->val_valor;
        $campos[0][4] = $this->val_id;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_setor', $id);

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
            $this->vale->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Vale Excluída com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Vale, Verifique!';
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
    public function def_campos($dados = false, $pos = 0, $show = false)
    {
        $id = new MyCampo('rh_vale','val_id');
        $id->valor = isset($dados['val_id']) ? $dados['val_id'] : '';
        $this->val_id = $id->crOculto();

        $empresas           = explode(',',session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('rh_setor','emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id'])? $dados['emp_id']: $opc_emp[1];
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $this->emp_id               = $emp->crSelect();

        $empres = false;
        $opc_col = [];
        if(isset($dados['emp_id'])){
            $empres = $dados['emp_id'];
        }
        
        if($empres){
            $colaboradores            = new RechumColaboradorModel();
            $dados_col          = $colaboradores->getColaborador(false, $empres);
            $opc_col            = array_column($dados_col, 'col_nome', 'col_id');
        }
        $cola                        = new MyCampo('rh_vale','col_id');
        $cola->obrigatorio           = true;
        $cola->valor = $cola->selecionado = isset($dados['col_id'])? $dados['col_id']: '';
        $cola->opcoes                = $opc_col;
        $cola->largura               = 50;
        $cola->leitura               = $show;
        $cola->pai                  = 'emp_id';
        $cola->urlbusca             = base_url('buscas/buscaColaborador');
        $this->col_id               = $cola->crDepende();

        
        $data                       = new MyCampo('rh_vale','val_data');
        $data->obrigatorio          = true;
        $data->valor                = isset($dados['val_data'])? $dados['val_data']: date('Y-m-d');
        $data->largura               = 20;
        $data->leitura               = $show;
        $this->val_data             = $data->crInput();

        $valo                       = new MyCampo('rh_vale','val_valor');
        $valo->obrigatorio          = true;
        $valo->valor                = isset($dados['val_valor'])? $dados['val_valor']: '';
        $valo->largura               = 20;
        $valo->tamanho               = 20;
        $valo->leitura               = $show;
        $this->val_valor     = $valo->crInput();

        $parc                       = new MyCampo('rh_vale','val_valor');
        $parc->id = $parc->nome     = 'val_parcela';
        $parc->label                = 'Parcelas';
        $parc->tipo                 = 'quantia';
        $parc->place                = '';
        $parc->obrigatorio          = true;
        $parc->valor                = '1';
        $parc->decimal                = 0;
        $parc->minimo               = 1;
        $parc->largura               = 10;
        $parc->tamanho               = 10;
        $parc->leitura               = $show;
        $this->val_parcela             = $parc->crInput();

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
        if(!isset($dados['val_parcela'])){
            $dados['val_parcela'] = 1;
        }
        $dataInicial = new DateTime($dados['val_data']);
        for($p=0;$p<$dados['val_parcela'];$p++){
            $novaData = clone $dataInicial;
            $novaData->modify("+$p month");
            
            $valor = $dados['val_valor'] / $dados['val_parcela'];
            $dados_val = [
                'val_id'           => $dados['val_id'],
                'col_id'           => $dados['col_id'],
                'val_data'         => $novaData->format('Y-m-d'),
                'val_valor'         => $valor,
            ];
            // debug($dados_fun,true);
            $salvar = $this->vale->save($dados_val);
            if ($salvar) {
                $ret['erro'] = false;
                $ret['msg'] = 'Vale gravado com Sucesso!!!';
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            } else {
                $ret['erro'] = true;
                $erros = $this->vale->errors();
                $ret['msg'] = 'Não foi possível gravar o Vale, Verifique!<br><br>';
                foreach ($erros as $erro) {
                    $ret['msg'] .= $erro;
                }
                break;
            }
        } 
        echo json_encode($ret);
    }
}
