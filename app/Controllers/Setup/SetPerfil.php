<?php namespace App\Controllers\Setup;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Setup\SetupPerfilItemModel;
use App\Models\Setup\SetupPerfilModel;

class SetPerfil extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $perfil;
    public $perfilitem;

	public function __construct(){
		$this->data = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
		$this->perfil 		= new SetupPerfilModel();
		$this->perfilitem 	= new SetupPerfilItemModel();
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }
	
    public function index()
	{
        $this->data['desc_metodo'] = 'Listagem de ';
        $this->data['colunas'] = array('ID','Nome','Descrição','Ações');
        $this->data['url_lista']  = base_url($this->data['controler'].'/lista');

        echo view('vw_lista', $this->data);
	}

    public function lista(){
        $result = [];
		$dados_perfil = $this->perfil->getPerfil();
        for($p=0;$p<sizeof($dados_perfil);$p++){
            $perfils = $dados_perfil[$p];
            if(session()->usu_perfil_id <= $perfils['per_id']){
                $edit   = '';
                $exclui = '';
                if (strpbrk($this->permissao, 'E')) {
                    $edit   = anchor($this->data['controler'].'/edit/'.$perfils['per_id'], '<i class="far fa-edit"></i>', ['class' =>'btn btn-outline-warning btn-sm mx-1','data-mdb-toggle'=>'tooltip','data-mdb-placement'=>'top','title'=>'Alterar este Registro' ]);
                }
                if (strpbrk($this->permissao, 'X')) {
                    $url_del = $this->data['controler'].'/delete/'.$perfils['per_id'];
                    $exclui = "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"".$url_del."\",\"".$perfils['per_nome']."\")'><i class='far fa-trash-alt'></i></button>";
                }
                $dados_perfil[$p]['per_nome'] = anchor($this->data['controler'].'/edit/'.$perfils['per_id'],$dados_perfil[$p]['per_nome']);
                $dados_perfil[$p]['acao'] = $edit.' '.$exclui;
                $perfils = $dados_perfil[$p];
                $result[] = [
                    $perfils['per_id'],
                    $perfils['per_nome'],
                    $perfils['per_descricao'],
                    $perfils['acao']
                ];
            }
        }
        $perfils_all = [
            'data' => $result
        ];

        echo json_encode($perfils_all); 
    }

    public function add(){
        $this->def_campos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0][0] = $this->per_id;
        $campos[0][0][1] = $this->per_nome;
        $campos[0][0][2] = $this->per_descricao;

        $secao[1] = 'Permissões'; 
		$itens = monta_lista_itens_perfil(false);
        // debug($itens);
        $modu = '';
        $ctm  = -1;
        $campos[1][0][0] = 'ite_perfil';
        for ($mn=0;$mn<sizeof($itens);$mn++) {
            // debug($classes[$mn], false);
            if($modu != $itens[$mn]['mod_id']){
                $cti = 0;
                $ctm++;
                $modu = $itens[$mn]['mod_id'];
                $this->def_campos_itens(0, $modu, $itens[$mn]);
                $campos2[1][$modu][$cti][0] = $this->pit_modu;
                $campos2[1][$modu][$cti][1] = $this->pit_classe;
                $campos2[1][$modu][$cti][2] = $this->pit_all;
                $campos2[1][$modu][$cti][3] = $this->pit_consulta;
                $campos2[1][$modu][$cti][4] = $this->pit_adicao;
                $campos2[1][$modu][$cti][5] = $this->pit_edicao;
                $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
            }
            $cti++;
            $item = $itens[$mn]['clas_id'];
            $this->def_campos_itens($item, $modu, $itens[$mn]);
            $campos2[1][$modu][$cti][0] = $this->pit_modu;
            $campos2[1][$modu][$cti][1] = $this->pit_classe;
            $campos2[1][$modu][$cti][2] = $this->pit_all;
            $campos2[1][$modu][$cti][3] = $this->pit_consulta;
            $campos2[1][$modu][$cti][4] = $this->pit_adicao;
            $campos2[1][$modu][$cti][5] = $this->pit_edicao;
            $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
        // debug($campos2[1][$ctm][$cti], false);
            // $ctm++; 
        }

		$this->data['secoes']   = $secao;
        $this->data['campos']   = $campos;
        $this->data['campos2']  = $campos2; 
		$this->data['destino']  = 'store';

		echo view('vw_edicao_perfil', $this->data);        
    }

    public function edit($id){
		$dados_perfil = $this->perfil->find($id);
		$this->def_campos($dados_perfil);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0][0] = $this->per_id;
        $campos[0][0][1] = $this->per_nome;
        $campos[0][0][2] = $this->per_descricao;

        $secao[1] = 'Permissões'; 
		$itens = monta_lista_itens_perfil($id);
        // debug($itens);
        $modu = '';
        $ctm  = -1;
        $campos[1][0][0] = 'ite_perfil';
        for ($mn=0;$mn<sizeof($itens);$mn++) {
            if($modu != $itens[$mn]['mod_id']){
                $cti = 0;
                $ctm++;
                $modu = $itens[$mn]['mod_id'];
                $clas = $itens[$mn]['clas_id'];
                // $perfil = $this->perfilitem->getItemPerfil($dados_perfil['per_id'], $classe);
                // debug($perfil, false);
                $this->def_campos_itens( 0, $modu, $itens[$mn]);

                $campos2[1][$modu][$cti][0] = $this->pit_modu;
                $campos2[1][$modu][$cti][1] = $this->pit_classe;
                $campos2[1][$modu][$cti][2] = $this->pit_all;
                $campos2[1][$modu][$cti][3] = $this->pit_consulta;
                $campos2[1][$modu][$cti][4] = $this->pit_adicao;
                $campos2[1][$modu][$cti][5] = $this->pit_edicao;
                $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
            }
            // debug('Módulo', false);
            // debug($this->pit_modu, false);
            $cti++;
            $item = $itens[$mn]['clas_id'];
            $this->def_campos_itens($item, $modu, $itens[$mn]);
            $campos2[1][$modu][$cti][0] = $this->pit_modu;
            $campos2[1][$modu][$cti][1] = $this->pit_classe;
            $campos2[1][$modu][$cti][2] = $this->pit_all;
            $campos2[1][$modu][$cti][3] = $this->pit_consulta;
            $campos2[1][$modu][$cti][4] = $this->pit_adicao;
            $campos2[1][$modu][$cti][5] = $this->pit_edicao;
            $campos2[1][$modu][$cti][6] = $this->pit_exclusao;
        // $ctm++; 
        }

		$this->data['secoes']   = $secao;
        $this->data['campos']   = $campos;
        $this->data['campos2']  = $campos2; 
		$this->data['destino']  = 'store';

		echo view('vw_edicao_perfil', $this->data);        
	}

    public function delete($id){
        $this->perfil->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler'])); 
    }

    public function def_campos($dados = false){
		$id				= new Campos();
		$id->objeto		= 'oculto';
		$id->nome		= 'per_id';
		$id->valor		= (isset($dados['per_id']))?$dados['per_id']:'';
		$this->per_id	= $id->create();

		$nome =  new Campos();
		$nome->objeto  	= 'input';
        $nome->tipo    	= 'text';
        $nome->nome    	= 'per_nome';
        $nome->id      	= 'per_nome';
        $nome->label   	= 'Nome';
        $nome->place   	= 'Nome';
        $nome->obrigatorio = true;
        $nome->hint    	= 'Informe o Nome do Perfil';
        $nome->size   	= 30;
		$nome->tamanho  = 30;
		$nome->valor	= (isset($dados['per_nome']))?$dados['per_nome']:'';
        $this->per_nome = $nome->create();

		$desc =  new Campos();
		$desc->objeto  	= 'texto';
        $desc->nome    	= 'per_descricao';
        $desc->id      	= 'per_descricao';
        $desc->label   	= 'Descrição';
        $desc->place   	= 'Descrição';
        $desc->obrigatorio = false;
        $desc->hint    	= 'Informe a Descrição';
        $desc->size     = 70;
        $desc->max_size = 3;
        $desc->tamanho  = 80;
		$desc->valor	= (isset($dados['per_descricao']))?$dados['per_descricao']:'';
        $this->per_descricao = $desc->create();
	}

    public function def_campos_itens($pos, $mod, $items = false){
        // ITENS
		$modu				= new Campos();
		$modu->objeto		= 'text_show';
		$modu->valor		= (isset($items['mod_nome']))?$items['mod_nome']:'';
		$this->pit_modu	    = $modu->create();

        $classe				= new Campos();
		$classe->objeto		= 'text_show';
		$classe->valor		= (isset($items['clas_titulo']))?$items['clas_titulo']:'';
		$this->pit_classe	    = $classe->create();

        $all =  new Campos();
        $all->objeto       = 'checkbox';
        $all->nome         = "pit_all[$mod][$pos]";
        $all->id           = "pit_all[$mod][$pos]";
        $all->label        = '';
        $all->obrigatorio  = false;
        $all->valor        = '';
        $all->selecionado  = 'X';
        $all->size         = 20;
        $all->tamanho      = 1;
        $all->classe       = "pit_all[$mod]";
        $this->pit_all     = $all->create();

        $consulta =  new Campos();
        $consulta->objeto       = 'checkbox';
        $consulta->nome         = "pit_consulta[$mod][$pos]";
        $consulta->id           = "pit_consulta[$mod][$pos]";
        $consulta->label        = '';
        $consulta->obrigatorio  = false;
        $consulta->valor        = 'C';
        $consulta->selecionado  = '';
        if($pos > 0 && isset($items['pit_permissao'])){
            if(strpos($items['pit_permissao'], 'C') !== false){
                $consulta->selecionado  = 'C';
            }
        }
        $consulta->size         = 20;
        $consulta->tamanho      = 1;
        $consulta->classe       = "pit_consulta[$mod] pit_all[$mod]";
        $this->pit_consulta     = $consulta->create();

        $adicao =  new Campos();
        $adicao->objeto      = 'checkbox';
        $adicao->nome        = "pit_adicao[$mod][$pos]";
        $adicao->id          = "pit_adicao[$mod][$pos]";
        $adicao->label       = '';
        $adicao->obrigatorio = false;
        $adicao->valor          = 'A';
        $adicao->selecionado    = '';
        if($pos > 0 && isset($items['pit_permissao'])){
            if(strpos($items['pit_permissao'], 'A') !== false){
                $adicao->selecionado  = 'A';
            }
        }
        $adicao->size       = 20;
        $adicao->tamanho   = 1;
        $adicao->classe    = "pit_adicao[$mod]  pit_all[$mod]";
        $this->pit_adicao  = $adicao->create();

        $edicao =  new Campos();
        $edicao->objeto         = 'checkbox';
        $edicao->nome           = "pit_edicao[$mod][$pos]";
        $edicao->id             = "pit_edicao[$mod][$pos]";
        $edicao->label          = '';
        $edicao->obrigatorio    = false;
        $edicao->valor          = 'E';
        $edicao->selecionado    = '';
        if($pos > 0 && isset($items['pit_permissao'])){
            if(strpos($items['pit_permissao'], 'E') !== false){
                $edicao->selecionado  = 'E';
            }
        }
        $edicao->size           = 20;
        $edicao->tamanho        = 1;
        $edicao->classe         = "pit_edicao[$mod] pit_all[$mod]";
        $this->pit_edicao       = $edicao->create();

        $exclusao =  new Campos();
        $exclusao->objeto      = 'checkbox';
        $exclusao->nome        = "pit_exclusao[$mod][$pos]";
        $exclusao->id          = "pit_exclusao[$mod][$pos]";
        $exclusao->label       = '';
        $exclusao->obrigatorio = false;
        $exclusao->valor       = 'X';
        $exclusao->selecionado = '';
        if($pos > 0 && isset($items['pit_permissao'])){
            if(strpos($items['pit_permissao'], 'X') !== false){
                $exclusao->selecionado  = 'X';
            }
        }
        $exclusao->size        = 20;
        $exclusao->tamanho     = 1;
        $exclusao->classe      = "pit_exclusao[$mod] pit_all[$mod]";
        $this->pit_exclusao    = $exclusao->create();
    }

	public function store(){
        $dados = $this->request->getPost();
        // debug($dados,true);
        $retorno = [];
        $tempermis = false;
        if (isset($dados['pit_consulta']) || isset($dados['pit_adicao']) || isset($dados['pit_edicao']) || isset($dados['pit_exclusao'])) {
            $tempermis = true;
        }
        if (!$tempermis) {
            $retorno['erro'] = true;
            $retorno['msg'] = 'É necessário informar pelo menos uma Permissão';
        } else {
            $dados_per = [
                'per_id'          => $dados['per_id'],
                'per_nome'        => $dados['per_nome'],
                'per_descricao'   => $dados['per_descricao']

            ];
            $this->perfil->transBegin();
            $per_save = $this->perfil->save($dados_per);
            // debug($this->perfil->errors());
            if ($per_save) {
                if ($dados['per_id'] == '') {
                    $per_id=$this->perfil->getInsertID();
                } else {
                    $per_id = $dados['per_id'];
                }
                $dados_pit = [];
                $d_ite = [];
                $cont = 0;
                // debug($dados['pit_consulta']);
                if (isset($dados['pit_consulta'])) {
                    $pit_consulta = $dados['pit_consulta'];
                    foreach ($pit_consulta as $chave => $valor) {
                        foreach ($valor as $classe => $opcao) {
                            if ($classe > 0) {
                                $d_ite[$chave][$classe]['permissao'] = isset($d_ite[$chave][$classe]['permissao'])?$d_ite[$chave][$classe]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_adicao'])) {
                    $pit_adicao = $dados['pit_adicao'];
                    foreach ($pit_adicao as $chave => $valor) {
                        foreach ($valor as $classe => $opcao) {
                            if ($classe > 0) {
                                $d_ite[$chave][$classe]['permissao'] = isset($d_ite[$chave][$classe]['permissao'])?$d_ite[$chave][$classe]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_edicao'])) {
                    $pit_edicao = $dados['pit_edicao'];
                    foreach ($pit_edicao as $chave => $valor) {
                        foreach ($valor as $classe => $opcao) {
                            if ($classe > 0) {
                                $d_ite[$chave][$classe]['permissao'] = isset($d_ite[$chave][$classe]['permissao'])?$d_ite[$chave][$classe]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                if (isset($dados['pit_exclusao'])) {
                    $pit_exclusao = $dados['pit_exclusao'];
                    foreach ($pit_exclusao as $chave => $valor) {
                        foreach ($valor as $classe => $opcao) {
                            if ($classe > 0) {
                                $d_ite[$chave][$classe]['permissao'] = isset($d_ite[$chave][$classe]['permissao'])?$d_ite[$chave][$classe]['permissao'].$opcao:$opcao;
                            }
                        }
                    }
                }
                foreach ($d_ite as $chave => $valor) {
                    foreach ($valor as $classe => $opcao) {
                        $dados_pit[$cont]['pit_perfil_id'] = $per_id;
                        $dados_pit[$cont]['pit_modulo_id'] = $chave;
                        $dados_pit[$cont]['pit_classe_id'] = $classe;
                        $dados_pit[$cont]['pit_permissao'] = $opcao['permissao'];
                        $cont++;
                    }
                }
                // debug($dados_pit, false);
                // exclui as permissões
                $this->perfilitem->excluiItemPerfil('pit_perfil_id', $per_id);

                $this->perfilitem->transBegin();
                for ($ct = 0;$ct<sizeof($dados_pit);$ct++) {
                    $pit_dados['pit_perfil_id']     =  $dados_pit[$ct]['pit_perfil_id'];
                    $pit_dados['pit_modulo_id']     =  $dados_pit[$ct]['pit_modulo_id'];
                    $pit_dados['pit_classe_id']     =  $dados_pit[$ct]['pit_classe_id'];
                    $pit_dados['pit_permissao']     =  $dados_pit[$ct]['pit_permissao'];
                    $pit_save = $this->perfilitem->insert($pit_dados);
                }
                if ($pit_save) {
                    $retorno['erro'] = false;
                    $retorno['msg'] = 'Perfil Gravado com Sucesso';
                    $retorno['url'] = base_url($this->data['controler']);
                } else {
                    $retorno['erro'] = true;
                    $retorno['msg'] = 'Não foi possível gravar as Permissões';
                    debug($this->perfilitem->errors());

                    $this->perfilitem->transRollback();
                }
            } else {
                $retorno['erro'] = true;
                $retorno['msg'] = 'Não foi possível gravar o Perfil<br>';
                $this->perfil->transRollback();
            }

            if (!$retorno['erro']) {
                session()->setFlashdata('msg', $retorno['msg']);
                $this->perfil->transCommit();
                $this->perfilitem->transCommit();
            }
            echo json_encode($retorno);
        }
    }
}
