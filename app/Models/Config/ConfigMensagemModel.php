<?php

namespace App\Models\Config;

use App\Libraries\MyCampo;
use App\Models\LogMonModel;
use CodeIgniter\Model;

class ConfigMensagemModel extends Model
{
    protected $DBGroup          = 'dbConfig';
    protected $table            = 'cfg_mensagem';
    protected $view             = 'cfg_mensagem';
    protected $primaryKey       = 'msg_id';
    protected $useAutoIncremodt = true;

    protected $returnType       = 'array';
    // protected $useSoftDeletes   = true;

    protected $allowedFields    = ['msg_id',
                                    'msg_titulo',
                                    'msg_tipo',
                                    'msg_cor',
                                    'msg_mensagem',
                                    'msg_desc_tipo',
                                    'msg_ativo',                                    
                                ];

    protected $deletedField  = 'msg_excluido';

    protected $validationRules = [
        'msg_titulo' => 'required|is_unique[cfg_mensagem.msg_titulo, msg_id,{msg_id}]',
    ];

    protected $validationMessages = [
        'msg_titulo' => [
            'required' => 'O campo Título é Obrigatório',
            'is_unique' => 'Já existe uma Mensagem com o título informado!',
        ],
    ];


    // Callbacks
    protected $allowCallbacks = true;

    protected $afterInsert   = ['depoisInsert'];
    protected $afterUpdate   = ['depoisUpdate'];
    protected $afterDelete   = ['depoisDelete'];

    protected $logdb;

    /**
     * This method saves the session "usu_id" value to "created_by" and "updated_by" array
     * elements before the row is inserted into the database.
     *
     */
    protected function depoisInsert(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'];
        $log = $logdb->insertLog($this->table, 'Incluído', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "updated_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisUpdate(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Alteração', $registro, $data['data']);
        return $data;
    }

    /**
     * This method saves the session "usu_id" value to "deletede_by" array element before
     * the row is inserted into the database.
     *
     */
    protected function depoisDelete(array $data)
    {
        $logdb = new LogMonModel();
        $registro = $data['id'][0];
        $log = $logdb->insertLog($this->table, 'Excluído', $registro, $data['data']);
        return $data;
    }

    public function getMensagem($msg_id = false)
    {
        $this->builder()->select('*');
        if ($msg_id) {
            $this->builder()->where('msg_id', $msg_id);
        }
        $this->builder()->where('msg_excluido', null);
        $this->builder()->orderBy('msg_id');
        return $this->builder()->get()->getResultArray();
    }

    public function getMensagemSearch($termo)
    {
        $array = ['msg_titulo' => $termo . '%'];
        $this->builder()->select(['msg_id','msg_titulo']);
        $this->builder()->where('msg_excluido', null);
        $this->builder()->like($array);

        return $this->builder()->get()->getResultArray();
    }

    public function defCampos($dados = false)
    {
        $ret = [];
        $mid            = new MyCampo('cfg_mensagem', 'msg_id');
        $mid->valor     = (isset($dados['msg_id'])) ? $dados['msg_id'] : '';
        $ret['msg_id']   = $mid->crOculto();

        $titu           =  new MyCampo('cfg_mensagem', 'msg_titulo');
        $titu->valor    = (isset($dados['msg_titulo'])) ? $dados['msg_titulo'] : '';
        $titu->obrigatorio = true;
        $ret['msg_titulo'] = $titu->crInput();

        // $opctipo['texto']['P'] = 'Pergunta';
        // $opctipo['icone']['P'] = '<i class="fa fa-circle-question fa-2x"></i>';
        // $opctipo['texto']['A'] = 'Alerta';
        // $opctipo['icone']['A'] = '<i class="fa fa-circle-exclamation fa-2x"></i>';
        // $opctipo['texto']['E'] = 'Erro';
        // $opctipo['icone']['E'] = '<i class="fa fa-circle-xmark fa-2x"></i>';
        // $opctipo['texto']['I'] = 'Informação';
        // $opctipo['icone']['I'] = '<i class="fa fa-circle-info fa-2x"></i>';

        $opctipo['P'] = 'Pergunta';
        $opctipo['A'] = 'Alerta';
        $opctipo['E'] = 'Erro';
        $opctipo['I'] = 'Informação';

        $tipo           =  new MyCampo('cfg_mensagem', 'msg_tipo');
        $tipo->tipo     = 'tipo';
        $tipo->valor    = (isset($dados['msg_tipo'])) ? $dados['msg_tipo'] : '';
        $tipo->largura  =  30;
        $tipo->selecionado = $tipo->valor;
        $tipo->opcoes   = $opctipo;
        $ret['msg_tipo'] = $tipo->crSelect();

        $cor           =  new MyCampo('cfg_mensagem','msg_cor');
        $cor->valor    = (isset($dados['msg_cor'])) ? $dados['msg_cor'] : '';
        $cor->selecionado    = $cor->valor;
        $cor->largura  =  30;
        $ret['msg_cor'] = $cor->crCorbst();

        $mens           =  new MyCampo('cfg_mensagem', 'msg_mensagem');
        $mens->valor    = (isset($dados['msg_mensagem'])) ? $dados['msg_mensagem'] : '';
        $ret['msg_mensagem'] = $mens->crTexto();

        $opcat['A'] = 'Ativo';
        $opcat['I'] = 'Inativo';

        $ativ           = new MyCampo('cfg_mensagem', 'msg_ativo');
        $ativ->valor    = (isset($dados['msg_ativo'])) ? $dados['msg_ativo'] : 'A';
        $ativ->selecionado    = $ativ->valor;
        $ativ->opcoes   = $opcat;
        $ret['msg_ativo'] = $ativ->cr2opcoes();




        return $ret;
    }
    
}
