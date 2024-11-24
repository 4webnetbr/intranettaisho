<?php 

namespace App\Controllers\Config;

use App\Controllers\BaseController;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigStatusModel;
use Closure;
    
Class CfgStatus extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $status;

    public function __construct()
    {
		$this->data         = session()->getFlashdata('dados_tela');
        $this->permissao    = $this->data['permissao'];
		$this->status 		= new ConfigStatusModel();
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Tela de Abertura
     * index
     */
    public function index()
    {
<<<<<<< HEAD
        $order          = new MyCampo();
        $order->nome    = 'bt_order';
        $order->id      = 'bt_order';
        $order->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                            <i class="fa-solid fa-arrow-down-short-wide" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $order->i_cone  .= '<div class="align-items-start txt-bt-manut d-none">Ordenar</div>';
        $order->place    = 'Ordenar os Status';
        $order->funcChan = 'redireciona(\'CfgStatus/ordenar/\')';
        $order->classep  = 'btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_order = $order->crBotao();
        $this->data['botao'] = $this->bt_order;

=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        $this->data['colunas'] = montaColunasLista($this->data, 'stt_id,');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
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
        // if (!$status = cache('status')) {
            $campos = montaColunasCampos($this->data, 'stt_id');
<<<<<<< HEAD
            $dados_stat = $this->status->getStatus();
            for($st=0;$st<count($dados_stat);$st++){
                $dados_stat[$st]['stt_cor'] = fmtEtiquetaCorBst($dados_stat[$st]['stt_cor'],$dados_stat[$st]['stt_nome']);
            }
            $status = [
                'data' => montaListaColunas($this->data, 'stt_id', $dados_stat, $campos[1]),
=======
            $dados_tela = $this->status->getMensagem();
            $status = [
                'data' => montaListaColunas($this->data, 'stt_id', $dados_tela, $campos[1]),
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
            ];
            cache()->save('status', $status, 60000);
        // }
        echo json_encode($status);
    }
<<<<<<< HEAD
    public function ordenar()
    {
        $lst_status     =  $this->status->getStatusOrdem();
        // debug($lst_status, true);
        $this->data['desc_metodo'] = 'Ordenação de ';
        $this->data['lst_status']    = $lst_status;
        $this->data['destino']    = 'storeOrd';

        echo view('vw_status_ordenar', $this->data);
    }
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7

    public function add($modal = false)
    {
        $fields = $this->status->defCampos();

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['stt_id'];
<<<<<<< HEAD
        $campos[0][count($campos[0])] = $fields['stt_nome'];
        $campos[0][count($campos[0])] = "vazio2";
        $campos[0][count($campos[0])] = $fields['mod_id'];
        $campos[0][count($campos[0])] = $fields['tel_id'];
        $campos[0][count($campos[0])] = $fields['stt_cor'];
        $campos[0][count($campos[0])] = $fields['stt_exclusao'];
        // $campos[0][count($campos[0])] = "vazio2";
        $campos[0][count($campos[0])] = $fields['stt_edicao'];
=======
        $campos[0][1] = $fields['stt_nome'];
        $campos[0][2] = "quebralinha";
        $campos[0][3] = $fields['mod_id'];
        $campos[0][4] = $fields['tel_id'];
        $campos[0][5] = $fields['stt_exclusao'];
        $campos[0][6] = $fields['stt_edicao'];
        $campos[0][7] = $fields['stt_cor'];
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        
       
		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        if(!$modal){
            echo view('vw_edicao', $this->data);
        } else {
            // $this->data['destino']    = 'store/modal';
            echo view('vw_edicao_modal', $this->data);
        }
    }
<<<<<<< HEAD
    public function show($id){
        $this->edit($id, true);
    }

    public function edit($id, $show = false){
		$dados_status = $this->status->getStatus($id)[0];
        $fields = $this->status->defCampos($dados_status, $show);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['stt_id'];
        $campos[0][count($campos[0])] = $fields['stt_nome'];
        $campos[0][count($campos[0])] = "vazio2";
        $campos[0][count($campos[0])] = $fields['mod_id'];
        $campos[0][count($campos[0])] = $fields['tel_id'];
        $campos[0][count($campos[0])] = $fields['stt_cor'];
        $campos[0][count($campos[0])] = $fields['stt_exclusao'];
        // $campos[0][count($campos[0])] = "vazio2";
        $campos[0][count($campos[0])] = $fields['stt_edicao'];
=======

    public function edit($id){
		$dados_status = $this->status->find($id);
        $fields = $this->status->defCampos($dados_status);

        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $fields['stt_id'];
        $campos[0][1] = $fields['stt_nome'];
        $campos[0][2] = $fields['mod_id'];
        $campos[0][3] = $fields['stt_cor'];
        $campos[0][4] = $fields['stt_exclusao'];
        $campos[0][5] = $fields['stt_edicao'];
        $campos[0][6] = $fields['stt_ordem'];
        $campos[0][7] = $fields['stt_ativo'];
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7

		$this->data['secoes']     = $secao;
        $this->data['campos']     = $campos;
		$this->data['destino']    = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('cfg_status', $id);

        echo view('vw_edicao', $this->data);
    }

    public function delete($id)
    {
        $ret = [];
        try {
            $this->status->delete($id);
            $ret['erro'] = false;
<<<<<<< HEAD
            session()->setFlashdata('msg', 'Status Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Status, Verifique!<br><br>';
            $ret['msg'] .= 'Este Status possui relacionamentos em outros cadastros!';
=======
            session()->setFlashdata('stt', 'Status Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['stt']  = 'Não foi possível Excluir o Status, Verifique!<br><br>';
            $ret['stt'] .= 'Este Status possui relacionamentos em outros cadastros!';
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        }
        echo json_encode($ret);
    }

    public function ativinativ($id, $tipo)
    {
        if ($tipo == 1) {
            $dad_atin = [
                'stt_ativo' => 'A'
            ];
        } else {
            $dad_atin = [
                'stt_ativo' => 'I'
            ];
<<<<<<< HEAD
            //TODO CONSULTAR ESSE STATUS, NAS TABELAS DA TELA A QUAL ELE ESTA LIGADO, PARA VERIFICAR 
            //TODO SE EXISTE REGISTROS COM ESSE STATUS, CASO EXISTA, NÃO PERMITA INATIVAR E MOSTRA MENSAGEM
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
        }
        $ret = [];
        try {
            $this->status->update($id, $dad_atin);
            $ret['erro'] = false;
<<<<<<< HEAD
            session()->setFlashdata('msg', 'Status Alterado com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }

    public function storeOrd()
    {
        $req = $this->request->getPost();
        foreach ($req as $key => $value) {
            if(substr($key,0,3) == 'tel'){
                $ord = 1;
            } else {
                $updt = [
                    'stt_ordem' => $ord
                ];
                $this->status->update($value, $updt);
                $ord++;
            }
        }
        // debug($ord_, true);
        $ret['erro'] = false;
        $ret['msg']  = 'Status Reordenado com Sucesso!!!';
        session()->setFlashdata('msg', $ret['msg']);
        $ret['url']  = site_url($this->data['controler']);
        echo json_encode($ret);
    }
    
    public function store()
    {
        $ret = [];
        $ret['erro'] = false;
        $postado = $this->request->getPost();
        $proximo = $this->status->getStatusProximaOrdem($postado['tel_id']);
        if(count($proximo) == 0){
            $prxord = 1;
        } else {
            $prxord = $proximo[0]['stt_ordem'] +1;
        }
        $postado['stt_ordem'] = $prxord;
        $erros = [];
        if ($this->status->save($postado)) {
            $ret['erro'] = false;
        } else {
            $erros = $this->status->errors();
            $ret['erro'] = true;
        }
        if ($ret['erro']) { 
            $ret['msg']  = 'Não foi possível gravar o Status, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro . '<br>';
            }
        } else {
            $ret['msg']  = 'Status gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
=======
            session()->setFlashdata('stt', 'Status Alterado com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['stt']  = 'Não foi possível Alterar o Status, Verifique!<br><br>';
        }
        echo json_encode($ret);
    }
    
    public function defcampos($dados = false)

{        // $moduloss = $this->modulo->getModulo((isset($dados['mod_id'])) ? $dados['mod_id'] : false);

}
    public function store()
    {
        $ret = [];
        $postado = $this->request->getPost();
        $erros = [];
        if ($postado['stt_id'] == '') {
            if ($this->status->save($postado)) {
                $ret['erro'] = false;
            } else {
                $erros = $this->status->errors();
                $ret['erro'] = true;
            }
        } else {

        if (isset($ret['erro'])) { 
            $ret['stt']  = 'Não foi possível gravar seu Status, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['stt'] .= $erro . '<br>';
            }
        } else {
            $ret['stt']  = 'Status gravado com Sucesso!!!';
            session()->setFlashdata('stt', $ret['stt']);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
            $ret['url']  = site_url($this->data['controler']);
        }
        echo json_encode($ret);
    }
<<<<<<< HEAD
=======

}
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
}