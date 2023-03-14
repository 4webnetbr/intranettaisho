<?php namespace App\Controllers\Setup;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Setup\SetupClasseModel;
use App\Models\Setup\SetupDicDadosModel;
use App\Models\Setup\SetupUsuarioModel;

class SetClasse extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $classe;
    public $dicionario;
    public $logs;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
        $this->classe    = new SetupClasseModel();
        $this->dicionario= new SetupDicDadosModel();
        $this->usuario   = new SetupUsuarioModel();
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
        $this->data['desc_metodo'] = 'Listagem de ';
        $this->data['colunas'] = ['ID', 'Titulo', 'Controler','Descrição', 'Ações'];
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
        $result = [];
        $dados_classe = $this->classe->findAll();
        for ($p = 0; $p < sizeof($dados_classe); $p++) {
            $class = $dados_classe[$p];
            $edit = '';
            $exclui = '';
            if (strpbrk($this->permissao, 'E')) {
                $edit = anchor(
                    $this->data['controler'] . '/edit/' . $class['clas_id'],
                    '<i class="far fa-edit"></i>',
                    [
                        'class' => 'btn btn-outline-warning btn-sm mx-1',
                        'data-mdb-toggle' => 'tooltip',
                        'data-mdb-placement' => 'top',
                        'title' => 'Alterar este Registro', 
                    ]
                );
            }
            if (strpbrk($this->permissao, 'X')) {
                $url_del =
                    $this->data['controler'] . '/delete/' . $class['clas_id'];
                $exclui =
                    "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"" .
                    $url_del .
                    "\",\"" .
                    $class['clas_titulo'] .
                    "\")'><i class='far fa-trash-alt'></i></button>";
            }

            $dados_classe[$p]['clas_titulo'] = "<i class='".$class['clas_icone']."'></i> ".$class['clas_titulo'];
            $dados_classe[$p]['acao'] = $edit . ' ' . $exclui;
            $class = $dados_classe[$p];
            $result[] = [
                $class['clas_id'],
                $class['clas_titulo'],
                $class['clas_controler'],
                $class['clas_descricao'],
                $class['acao'],
            ];
        }
        $classs = [
            'data' => $result,
        ];

        echo json_encode($classs);
    }

    /**
     * Inclusão
     * add
     *
     * @return void
     */
    public function add()
    {
        $this->def_campos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0][0] = $this->clas_id;
        $campos[0][0][1] = $this->clas_titulo;
        $campos[0][0][2] = $this->clas_icon;
        $campos[0][0][3] = $this->clas_cont;
        $campos[0][0][4] = $this->clas_txtb;
        $campos[0][0][5] = $this->clas_tabela;
        $campos[0][0][6] = $this->clas_desc;

        $secao[1] = 'Regras Gerais';
        $campos[1][0][0] = $this->clas_regg;

        $secao[2] = 'Regras do Cadastro';
        $campos[2][0][0] = $this->clas_regc;


        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    /**
     * Edição
     * edit
     *
     * @param mixed $id 
     * @return void
     */
    public function edit($id)
    {
        $dados_classe = $this->classe->getClasseId($id)[0];
        $this->def_campos($dados_classe);

        $secao[0] = 'Dados Gerais';
        $campos[0][0][0] = $this->clas_id;
        $campos[0][0][1] = $this->clas_titulo;
        $campos[0][0][2] = $this->clas_icon;
        $campos[0][0][3] = $this->clas_cont;
        $campos[0][0][4] = $this->clas_txtb;
        $campos[0][0][5] = $this->clas_desc;

        $secao[1] = 'Regras Gerais';
        $campos[1][0][0] = $this->clas_regg;

        $secao[2] = 'Filtros';
        $campos[2][0][0] = 'tabela';
        $campos[2][0][1] = $this->clas_filtros;

        $secao[3] = 'Cadastro';
        $campos[3][0][0] = $this->clas_regc;

        $secao[4] = 'Base de Dados';
        $campos[4][0][0] = $this->clas_tabela;
        $campos[4][0][1] = $this->clas_camp;
        $campos[4][0][2] = $this->clas_trel;

        $secao[5] = 'Funções';
        $campos[5][0][0] = $this->clas_meto;

        $secao[6] = 'Código Fonte';
        $campos[6][0][0] = $this->clas_codi;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('setup_classe', $id);
        // debug('fim');

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
        $this->classe->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler']));
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos($dados = false, $pos = 0)
    {
        $id = new Campos();
        $id->objeto = 'oculto';
        $id->nome = 'clas_id';
        $id->id = 'clas_id';
        $id->valor = isset($dados['clas_id']) ? $dados['clas_id'] : '';
        $this->clas_id = $id->create();

        $titulo = new Campos();
        $titulo->objeto = 'input';
        $titulo->tipo = 'text';
        $titulo->nome = 'clas_titulo';
        $titulo->id = 'clas_titulo';
        $titulo->label = 'Titulo';
        $titulo->place = 'Titulo';
        $titulo->obrigatorio = true;
        $titulo->hint = 'Informe o Titulo';
        $titulo->size = 30;
        $titulo->tamanho = 40;
        $titulo->valor = isset($dados['clas_titulo'])? $dados['clas_titulo']: '';
        $this->clas_titulo = $titulo->create();

        $icon = new Campos();
        $icon->objeto = 'input';
        $icon->tipo = 'icone';
        $icon->nome = 'clas_icone';
        $icon->id = 'clas_icone';
        $icon->label = 'Ícone';
        $icon->place = 'Ícone';
        $icon->obrigatorio = true;
        $icon->hint = 'Informe o Ícone';
        $icon->size = 100;
        $icon->tamanho = 50;
        $icon->max_size = 50;
        $icon->valor = isset($dados['clas_icone']) ? $dados['clas_icone'] : '';
        $this->clas_icon = $icon->create();

        $cont = new Campos();
        $cont->objeto = 'input';
        $cont->tipo = 'text';
        $cont->nome = 'clas_controler';
        $cont->id = 'clas_controler';
        $cont->label = 'Controler';
        $cont->place = 'Controler';
        $cont->obrigatorio = true;
        $cont->hint = 'Informe o Controler';
        $cont->size = 20;
        $cont->tamanho = 25;
        $cont->valor = isset($dados['clas_controler'])
            ? $dados['clas_controler']
            : '';
        $this->clas_cont = $cont->create();

        $meto = new Campos();
        $meto->objeto = 'show';
        $meto->id = 'clas_metodos';
        $meto->label = 'Métodos';
        $meto->size = 40;
        $meto->tamanho = 'auto';
        $meto->valor = '';
        if (isset($dados['clas_controler'])) {
            $metoddos = get_class_methods($this);
            $meto->valor = metodos_classe($metoddos);
        }
        $this->clas_meto = $meto->create();

        $txtb = new Campos();
        $txtb->objeto = 'input';
        $txtb->tipo = 'text';
        $txtb->nome = 'clas_texto_botao';
        $txtb->id = 'clas_texto_botao';
        $txtb->label = 'Texto do Botão Add';
        $txtb->place = 'Texto do Botão Add';
        $txtb->obrigatorio = false;
        $txtb->hint = 'Informe o Texto do Botão Add';
        $txtb->size = 20;
        $txtb->tamanho = 25;
        $txtb->valor = isset($dados['clas_texto_botao'])
            ? $dados['clas_texto_botao']
            : '';
        $this->clas_txtb = $txtb->create();

        $tabe = new Campos();
		$tabe->objeto  	        = 'selbusca';
        $tabe->nome    	        = 'clas_tabela';
        $tabe->id      	        = 'clas_tabela';
        $tabe->label   	        = 'Tabela Principal';
        $tabe->place   	        = 'Escolha a Tabela Principal';
        $tabe->obrigatorio      = false;
        $tabe->hint    	        = 'Escolha a Tabela Principal';
        $tabe->valor            = (isset($dados['clas_tabela']))?$dados['clas_tabela']:'';
        $tabe->selecionado      = (isset($dados['clas_tabela']))?$dados['clas_tabela']:'';
        $tabe->busca            = base_url('buscas/busca_tabela');
		$tabe->tamanho          = 35;
        $this->clas_tabela      = $tabe->create();

        if(isset($dados['clas_tabela'])){
            $campos = $this->dicionario->getCampos($dados['clas_tabela']);
            $relac = $this->dicionario->getRelacionamentos($dados['clas_tabela']);
            // $campos .= $this->dicionario->getCampos($relac['REFERENCED_TABLE_NAME']);
        }

        $camp = new Campos();
        $camp->objeto = 'show';
        $camp->id = 'clas_campos';
        $camp->label = 'Campos';
        $camp->size = 'auto';
        $camp->tamanho = 'auto';
        $camp->valor = '';
        if (isset($dados['clas_tabela'])) {
            $camp->valor = campos_tabela($campos);
        }
        $this->clas_camp = $camp->create();


        $trel = new Campos();
        $trel->objeto = 'show';
        $trel->id = 'clas_relac';
        $trel->label = 'Tabelas Relacionadas';
        $trel->size = 'auto';
        $trel->tamanho = 'auto';
        $trel->valor = '';
        if (isset($dados['clas_tabela'])) {
            $trel->valor = relacion_tabela($relac);
        }
        $this->clas_trel = $trel->create();

        $filcamp = new Campos();
        $filcamp->objeto            = 'select';
        $filcamp->id                = "clas_filtros__$pos";
        $filcamp->label             = 'Filtrar por:';
        $filcamp->size              = 40;
        $filcamp->tamanho           = 50;
        $filcamp->max_size          = 10;
        $filcamp->repete            = true;
        if (isset($dados['clas_tabela'])) {
            $filcamp->opcoes            = array_column($campos,'COLUMN_COMMENT','COLUMN_NAME');
        }
        $filcamp->valor             = (isset($dados['clas_filtros']))?$dados['clas_filtros']:'';
        $filcamp->selecionado       = (isset($dados['clas_filtros']))?$dados['clas_filtros']:'';
        $this->clas_filtros = $filcamp->create();

        $desc = new Campos();
        $desc->objeto   = 'texto';
        $desc->nome     = 'clas_descricao';
        $desc->id       = 'clas_descricao';
        $desc->label    = 'Descrição';
        $desc->place    = 'Descrição';
        $desc->obrigatorio = false;
        $desc->hint     = 'Informe a Descrição';
        $desc->size     = 70;
        $desc->max_size = 3;
        $desc->tamanho  = 80;
        $desc->valor    = isset($dados['clas_descricao'])
            ? $dados['clas_descricao']
            : '';
        $this->clas_desc = $desc->create();

        $regg = new Campos();
        $regg->objeto   = 'texto';
        $regg->tipo     = 'textarea';
        $regg->nome     = 'clas_regras_gerais';
        $regg->id       = 'clas_regras_gerais';
        $regg->label    = 'Regras Gerais';
        $regg->place    = 'Regras Gerais';
        $regg->obrigatorio = false;
        $regg->hint     = 'Informe as Regras Gerais';
        $regg->classe   = 'editor';
        $regg->size     = 80;
        $regg->max_size = 5;
        $regg->tamanho  = 100;
        $regg->valor    = isset($dados['clas_regras_gerais'])
            ? $dados['clas_regras_gerais']
            : '';
        $this->clas_regg = $regg->create();

        $regc = new Campos();
        $regc->objeto = 'texto';
        $regc->tipo = 'textarea';
        $regc->nome = 'clas_regras_cadastro';
        $regc->id = 'clas_regras_cadastro';
        $regc->label = 'Regras do Cadastro';
        $regc->place = 'Regras do Cadastro';
        $regc->obrigatorio = false;
        $regc->hint = 'Informe as Regras  do Cadastro';
        $regc->classe   = 'editor';
        $regc->size     = 80;
        $regc->max_size = 5;
        $regc->tamanho  = 100;
        $regc->valor = isset($dados['clas_regras_cadastro'])
            ? $dados['clas_regras_cadastro']
            : '';
        $this->clas_regc = $regc->create();

        if($dados){
            $fonte = ver_codigo('Controllers/Setup/'.$dados['clas_controler']);
            $codi = new Campos();
            $codi->objeto = 'show';
            $codi->nome = 'codigo';
            $codi->id = 'codigo';
            $codi->label = 'Código Fonte';
            $codi->size = '100%';
            $codi->tamanho = 'auto';
            $codi->valor = '<pre>'.$fonte.'</pre>';
            // $codi->valor = print_r($fonte);
            $this->clas_codi = $codi->create();
        }
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
        // debug($dados);
        $dados_clas = [
            'clas_id'           => $dados['clas_id'],
            'clas_titulo'       => $dados['clas_titulo'],
            'clas_icone'        => $dados['clas_icone'],
            'clas_controler'    => $dados['clas_controler'],
            'clas_texto_botao'  => $dados['clas_texto_botao'],
            'clas_tabela'    => $dados['clas_tabela'],
            'clas_descricao'    => $dados['clas_descricao'],
            'clas_regras_gerais'       => $dados['clas_regras_gerais'],
        ];
        if ($this->classe->save($dados_clas)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Classe gravada com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Não foi possível gravar a Classe, Verifique!';
        }
        echo json_encode($ret);
    }
}
?>