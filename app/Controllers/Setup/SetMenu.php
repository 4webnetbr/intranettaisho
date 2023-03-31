<?php namespace App\Controllers\Setup;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Setup\SetupMenuModel;

class SetMenu extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $menu;

	public function __construct(){
		$this->data      = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
		$this->menu      = new SetupMenuModel();
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

	public function index()
	{
        $this->data['colunas'] = array('ID','Caminho','Classe','Hierarquia','Ações');
        $this->data['url_lista']  = base_url($this->data['controler'].'/lista');

		$order =  new Campos();
		$order->objeto  = 'botao';
        $order->tipo    = 'button';
        $order->nome    = 'bt_order';
        $order->id      = 'bt_order';
        $order->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style=""><i class="fa-solid fa-arrow-down-short-wide" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $order->label   = '<div class="align-items-start mx-4 txt-bt-manut d-none">Ordenar</div>';
        $order->hint    = 'Ordenar o Menu';
        $order->funcao_chan = 'redireciona(\'SetMenu/ordenar/\')';
        $order->classe  = 'btn btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_order = $order->create();

        $this->data['botao'] = $this->bt_order;
        
        echo view('vw_lista', $this->data);
	}

    public function lista(){
        // $this->menu->select('men_id', 'men_nome');
        $result = [];
		$dados_menu = $this->menu->getMenu();
        // debug($dados_menu);
        for($p=0;$p<sizeof($dados_menu);$p++){
            $dmenu = $dados_menu[$p];
            $edit   = '';
            $exclui = '';
            if (strpbrk($this->permissao, 'E')) {
                $edit   = anchor($this->data['controler'].'/edit/'.$dmenu['men_id'], '<i class="far fa-edit"></i>', ['class' =>'btn btn-outline-warning btn-sm mx-1','data-mdb-toggle'=>'tooltip','data-mdb-placement'=>'top','title'=>'Alterar este Registro' ]);
            }
            if (strpbrk($this->permissao, 'X')) {
                $url_del = $this->data['controler'].'/delete/'.$dmenu['men_id'];
                $exclui = "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"".$url_del."\",\"".$dmenu['men_etiqueta']."\")'><i class='far fa-trash-alt'></i></button>";
            }

            $dados_menu[$p]['acao'] = $edit.' '.$exclui;
            // se é menu pai e tem módulo
            $dados_menu[$p]['men_modulo'] = "<i class='".$dmenu['mod_icone']."'></i> ".$dmenu['mod_nome'];
            $dados_menu[$p]['men_classe'] = "<i class='".$dmenu['clas_icone']."'></i> ".$dmenu['clas_titulo'];
            $dados_menu[$p]['men_etiqueta'] = "<i class='".$dmenu['men_icone']."'></i> ".$dmenu['men_etiqueta'];
            $dados_menu[$p]['caminho'] = "<i class='".$dmenu['men_icone']."'></i> ".$dmenu['men_etiqueta'];
            if($dmenu['men_menu_id'] > 0){
                $dados_menu[$p]['caminho'] = "<i class='".$dmenu['pai_icone']."'></i> ".$dmenu['pai_etiqueta']." <i class='fas fa-level-up-alt fa-rotate-90'></i> ".$dados_menu[$p]['caminho'];
            }
            if($dmenu['men_submenu_id'] != null && $dmenu['men_submenu_id'] > 0){
                $dados_menu[$p]['caminho'] = "<i class='".$dmenu['pai_icone']."'></i> ".$dmenu['pai_etiqueta']." <i class='fas fa-level-up-alt fa-rotate-90'></i> <i class='".$dmenu['sub_icone']."'></i> ".$dmenu['sub_etiqueta']." <i class='fa fa-arrow-right-long'></i> <i class='".$dmenu['men_icone']."'></i> ".$dmenu['men_etiqueta'];;
            }
            $dados_menu[$p]['caminho'] = anchor($this->data['controler'].'/edit/'.$dmenu['men_id'],$dados_menu[$p]['caminho']);

            $dmenu = $dados_menu[$p];
            // debug($dmenu,true); 
            $result[] = [
                $dmenu['men_id'],
                $dmenu['caminho'],
                $dmenu['men_classe'], 
                $dmenu['desc_hierarquia'],
                $dmenu['acao'],
                // $dmenu['men_order'], 
            ];
        }
        $dadmmenus = [
            'data' => $result
        ];

        echo json_encode($dadmmenus);  
    }

    public function ordenar(){
        $it_menu     =  monta_menu(false, false, true);
        $this->data['desc_metodo'] = 'Ordenação de ';
        $this->data['it_menu'] = $it_menu;
		$this->data['destino']    = 'store_ord';

		echo view('vw_menu_ordenar', $this->data);
    }

    public function add(){
        $this->def_campos();

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $this->men_id; 
        $campos[0][1] = $this->men_hier;
        $campos[0][2] = $this->men_mpai;
        $campos[0][3] = $this->men_subm;
        $campos[0][4] = $this->men_modu;
        $campos[0][5] = $this->men_clas;
        $campos[0][6] = $this->men_etiq;
        $campos[0][7] = $this->men_icon;
        // $campos[0][0][8] = $this->men_orde;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

		echo view('vw_edicao', $this->data);
    }

    public function edit($id){
		$dados_menu = $this->menu->getMenu($id)[0];
		$this->def_campos($dados_menu);

        $secao[0] = 'Dados Gerais'; 
        $campos[0][0] = $this->men_id; 
        $campos[0][1] = $this->men_hier;
        $campos[0][2] = $this->men_mpai;
        $campos[0][3] = $this->men_subm;
        $campos[0][4] = $this->men_modu;
        $campos[0][5] = $this->men_clas;
        $campos[0][6] = $this->men_etiq;
        $campos[0][7] = $this->men_icon;
        // $campos[0][8] = $this->men_orde;

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';
		$this->data['script']    = "<script>acerta_campos_cad_menu(jQuery('#men_hierarquia')[0]);</script>";

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('setup_menu', $id);

		echo view('vw_edicao', $this->data);
    }

    public function delete($id){
        $this->menu->delete($id);
        session()->setFlashdata('msg', 'Registro Excluído com Sucesso');
        return redirect()->to(site_url($this->data['controler'])); 
    }

    public function def_campos($dados = false){
        // debug($dados, false);
		$id				= new Campos();
		$id->objeto		= 'oculto';
		$id->nome		= 'men_id';
		$id->valor		= (isset($dados['men_id']))?$dados['men_id']:'';
		$this->men_id	= $id->create();

        $hierarquia[1] = 'Raíz do Menu';
        $hierarquia[2] = 'Menu';
        $hierarquia[3] = 'Sub-menu';
        $hierarquia[4] = 'Opção do Menu';

		$hier =  new Campos();
		$hier->objeto  	= 'select';
        $hier->nome    	= 'men_hierarquia';
        $hier->id      	= 'men_hierarquia';
        $hier->label   	= 'Hierarquia';
        $hier->place   	= 'Hierarquia';
        $hier->obrigatorio = true;
        $hier->hint    	= 'Informe a Hierarquia';
        $hier->size   	= 30;
		$hier->tamanho  = 30;
		$hier->valor	    = (isset($dados['men_hierarquia']))?$dados['men_hierarquia']:0;
        $hier->selecionado  = (isset($dados['men_hierarquia']))?$dados['men_hierarquia']:'';
        $hier->busca        = base_url('buscas/busca_hierarquia');
        $hier->opcoes       = $hierarquia;
        $hier->funcao_chan   = "acerta_campos_cad_menu(this)";
        $this->men_hier = $hier->create();

        $menus = $this->menu->getMenuPai();
        $menu_pai = array_column($menus,'men_etiqueta','men_id');
        $sub_menu = [];
        if(isset($dados['men_menu_id'])){
            $submenus = $this->menu->getSubMenu($dados['men_menu_id']);
            $sub_menu = array_column($submenus,'men_etiqueta','men_id');
        }

		$mpai =  new Campos();
		$mpai->objeto  	= 'depende';
        $mpai->nome    	= 'men_menu_id';
        $mpai->id      	= 'men_menu_id';
        $mpai->label   	= 'Menu Pai';
        $mpai->place   	= 'Menu Pai';
        $mpai->obrigatorio = false;
        $mpai->hint    	= 'Informe o Menu Pai';
        $mpai->size   	= 30;
		$mpai->tamanho  = 30;
		$mpai->valor	    = (isset($dados['men_menu_id']))?$dados['men_menu_id']:0;
        $mpai->selecionado  = (isset($dados['men_menu_id']))?$dados['men_menu_id']:'';
        $mpai->busca        = base_url('buscas/busca_menu_pai');
        $mpai->pai          = 'men_hierarquia';
        $mpai->opcoes       = $menu_pai;
        $this->men_mpai = $mpai->create();

		$subm =  new Campos();
		$subm->objeto  	= 'depende';
        $subm->nome    	= 'men_submenu_id';
        $subm->id      	= 'men_submenu_id';
        $subm->label   	= 'Sub Menu';
        $subm->place   	= 'Sub Menu';
        $subm->obrigatorio = false;
        $subm->hint    	= 'Informe o Sub Menu';
        $subm->size   	= 30;
		$subm->tamanho  = 30;
		$subm->valor	    = (isset($dados['men_submenu_id']))?$dados['men_submenu_id']:0;
        $subm->selecionado  = (isset($dados['men_submenu_id']))?$dados['men_submenu_id']:'';
        $subm->busca       = base_url('buscas/busca_submenu');
        $subm->pai          = 'men_menu_id';
        $subm->opcoes       = $sub_menu;
        $this->men_subm = $subm->create();

		$etiq =  new Campos();
		$etiq->objeto  	= 'input';
        $etiq->tipo    	= 'text';
        $etiq->nome    	= 'men_etiqueta';
        $etiq->id      	= 'men_etiqueta';
        $etiq->label   	= 'Etiqueta';
        $etiq->place   	= 'Etiqueta';
        $etiq->obrigatorio = true;
        $etiq->hint    	= 'Informe a Etiqueta';
        $etiq->size   	= 50;
		$etiq->tamanho  = 50;
		$etiq->valor	= (isset($dados['men_etiqueta']))?$dados['men_etiqueta']:'';
        $this->men_etiq = $etiq->create();

		$icon =  new Campos(); 
		$icon->objeto  	= 'input';
        $icon->tipo    	= 'icone';
        $icon->nome    	= 'men_icone';
        $icon->id      	= 'men_icone';
        $icon->label   	= 'Ícone';
        $icon->place   	= 'Ícone';
        $icon->obrigatorio = true;
        $icon->hint    	= 'Informe o Ícone';
        $icon->size   	= 50;
		$icon->tamanho  = 50;
		$icon->max_size  = 50;
		$icon->valor	= (isset($dados['men_icone']))?$dados['men_icone']:'';
        $this->men_icon = $icon->create();

		$modu =  new Campos();
		$modu->objeto  	    = 'selbusca';
        $modu->nome    	    = 'men_modulo_id';
        $modu->id      	    = 'men_modulo_id';
        $modu->label   	    = 'Módulo';
        $modu->place   	    = 'Módulo';
        $modu->obrigatorio  = false;
        $modu->hint    	    = 'Escolha o Módulo';
		$modu->valor	    = (isset($dados['mod_id']))?$dados['mod_id']:'';
        $modu->selecionado  = (isset($dados['mod_nome']))?$dados['mod_nome']:'';
        $modu->busca        = base_url('buscas/busca_modulo');
        $modu->funcao_chan  = "busca_atributos('modulo','men_modulo_id', 'men_etiqueta','men_icone')";
		$modu->tamanho      = 35;
        $this->men_modu = $modu->create();

		$clas =  new Campos();
		$clas->objeto  	    = 'selbusca';
        $clas->nome    	    = 'men_classe_id';
        $clas->id      	    = 'men_classe_id';
        $clas->label   	    = 'Classe';
        $clas->place   	    = 'Classe'; 
        $clas->obrigatorio  = false;
        $clas->hint    	    = 'Escolha a Classe';
		$clas->valor	    = (isset($dados['clas_id']))?$dados['clas_id']:'';
        $clas->selecionado  = (isset($dados['clas_titulo']))?$dados['clas_titulo']:'';
        $clas->busca        = base_url('buscas/busca_classe');
        $clas->funcao_chan  = "busca_atributos('classe', 'men_classe_id', 'men_etiqueta','men_icone')";
		$clas->tamanho      = 35;
        $this->men_clas     = $clas->create();

		$meto =  new Campos();
		$meto->objeto  	    = 'input';
		$meto->tipo  	    = 'text';
        $meto->nome    	    = 'men_metodo';
        $meto->id      	    = 'men_metodo';
        $meto->label   	    = 'Método';
        $meto->place   	    = 'Método';
        $meto->obrigatorio  = false;
        $meto->hint    	    = 'Escolha o Método';
		$meto->valor	    = (isset($dados['men_metodo']))?$dados['men_metodo']:'index';
		$meto->tamanho      = 30;
		$meto->tamanho      = 35;
        $this->men_meto     = $meto->create();

        $cont =  new Campos();
		$cont->objeto  	= 'input';
        $cont->tipo    	= 'text';
        $cont->nome    	= 'men_controler';
        $cont->id      	= 'men_controler';
        $cont->label   	= 'Controler';
        $cont->place   	= 'Controler';
        $cont->obrigatorio = false;
        $cont->hint    	= 'Informe o Controler';
        $cont->size   	= 20;
		$cont->tamanho  = 25;
		$cont->valor	= (isset($dados['men_controler']))?$dados['men_controler']:'';
        $this->men_cont = $cont->create();

		$orde =  new Campos(); 
		$orde->objeto  	= 'input';
        $orde->tipo    	= 'number';
        $orde->nome    	= 'men_order';
        $orde->id      	= 'men_order';
        $orde->label   	= 'Ordem';
        $orde->place   	= 'Ordem';
        $orde->obrigatorio = false;
        $orde->hint    	= 'Informe a Ordem';
        $orde->size   	= 10;
		$orde->tamanho  = 15;
        $orde->minimo   = 1;
		$orde->maximo   = 50;
        $orde->step     = 1;
		$orde->valor	= (isset($dados['men_order']))?$dados['men_order']:'';
        $this->men_orde = $orde->create();

    }
	public function store_ord(){
        $req = $this->request->getPost();
        $ord = 1;
        foreach($req as $key => $value){
            $updt = [
                'men_order' => $ord
            ];
            $this->menu->update($value, $updt);
            $ord++;
        }
        $ret['erro'] = false;
        $ret['msg']  = 'Menu Reordenado com Sucesso!!!';
        session()->setFlashdata('msg', $ret['msg']);
        $ret['url']  = site_url($this->data['controler']);
        echo json_encode($ret);
    }

	public function store(){
        $ret = [];
		if($this->menu->save($this->request->getPost())){
            $ret['erro'] = false;
            $ret['msg']  = 'Menu gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url']  = site_url($this->data['controler']);
		} else {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível gravar o Menu, Verifique!';
        }
        echo json_encode($ret);
	}

}
