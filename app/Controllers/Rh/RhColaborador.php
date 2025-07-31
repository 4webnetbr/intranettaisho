<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumCargoModel;
use App\Models\Rechum\RechumColaboradorModel;
use App\Models\Rechum\RechumSetorModel;
use App\Models\Rechum\RechumTurnoModel;

class RhColaborador extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $colaborador;
    public $empresa;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->colaborador       = new RechumColaboradorModel();
        $this->empresa       = new ConfigEmpresaModel();
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

        $this->data['nome']         = 'colaborador';
        $this->data['colunas']      = montaColunasLista($this->data, 'col_id');
        $this->data['url_lista']    = base_url($this->data['controler'] . '/lista');
        $this->data['campos']         = $campos;
        $this->data['script']       = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);
    }

    public function def_campos_lista()
    {

        $empresas = explode(',', session()->get('usu_empresa'));
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');
        if (session()->has('empresa_atual')) {
            $empresas[0] = session()->get('empresa_atual');
        }

        $emp                        = new MyCampo();
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label = $emp->place   = 'Empresa(s)';
        $emp->selecionado           = $empresas[0];
        $emp->opcoes                = $empres;
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = '2col';
        $emp->largura               = 50;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        $emp->funcChan              = "carrega_lista(this,'RhColaborador/lista','colaborador')";
        $this->dash_empresa         = $emp->crSelect();
    }
    /**
     * Listagem
     * lista
     *
     * @return void
     */
    public function lista()
    {
        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = false;
        } else {
            session()->set('empresa_atual', $param);
        }
        // $empresas = explode(',',session()->get('usu_empresa'));

        $dados_colaboradors = $this->colaborador->getColaborador(false, $param);
        foreach ($dados_colaboradors as $key => $value) {
            $situac = $value['col_situacao']; // Atribui o valor quando a chave for 'col_situacao'
            $dados_colaboradors[$key]['cor'] = (trim($situac) == 'Férias') ? 'bg-success' : (($situac == 'Demitido') ? 'bg-warning' : (($situac == 'Doença') ? 'bg-info' : ''));
        }
        $colaboradors = [
            'data' => montaListaColunas($this->data, 'col_id', $dados_colaboradors, 'col_nome'),
        ];
        // debug($colaboradors['data']);
        // }
        echo json_encode($colaboradors);
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
        $campos[0][0] = $this->col_id;
        $campos[0][count($campos[0])] = $this->emp_id;
        $campos[0][count($campos[0])] = $this->emp_id_registro;
        $campos[0][count($campos[0])] = $this->cag_id;
        $campos[0][count($campos[0])] = $this->set_id;
        $campos[0][count($campos[0])] = $this->col_cpf;
        $campos[0][count($campos[0])] = $this->col_matricula;
        $campos[0][count($campos[0])] = $this->col_nome;
        $campos[0][count($campos[0])] = $this->col_nome_social;
        $campos[0][count($campos[0])] = $this->col_data_nascimento;
        $campos[0][count($campos[0])] = $this->col_genero;
        $campos[0][count($campos[0])] = $this->col_estado_civil;

        $secao[1] = 'Endereço';
        $campos[1][0] = '';
        $campos[1][count($campos[1])] = $this->col_cep;
        $campos[1][count($campos[1])] = $this->col_cidade;
        $campos[1][count($campos[1])] = $this->col_estado;
        $campos[1][count($campos[1])] = $this->col_bairro;
        $campos[1][count($campos[1])] = $this->col_endereco;
        $campos[1][count($campos[1])] = $this->col_numero;
        $campos[1][count($campos[1])] = $this->col_complemento;
        $campos[1][count($campos[1])] = $this->col_telefone_celular;
        $campos[1][count($campos[1])] = $this->col_email;

        $secao[2] = 'Contrato';
        $campos[2][0] = '';
        $campos[2][count($campos[2])] = $this->col_vinculo;
        $campos[2][count($campos[2])] = $this->col_cargahoraria;
        $campos[2][count($campos[2])] = $this->col_data_admissao;
        $campos[2][count($campos[2])] = $this->col_data_demissao;
        $campos[2][count($campos[2])] = $this->col_tipo;
        $campos[2][count($campos[2])] = $this->col_salario;
        $campos[2][count($campos[2])] = $this->col_pctparticipacao;
        $campos[2][count($campos[2])] = $this->col_situacao;
        $campos[2][count($campos[2])] = $this->col_vale;
        $campos[2][count($campos[2])] = $this->col_vt;
        $campos[2][count($campos[2])] = $this->col_folgasemana;
        $campos[2][count($campos[2])] = $this->col_folgadomingo;
        $campos[2][count($campos[2])] = $this->col_metropolitana;


        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    public function add_campo($ind)
    {
        $this->def_campos_cargo(false, $ind);

        $campo = [];
        $campo[count($campo)] = $this->quf_id;
        $campo[count($campo)] = $this->cag_id;
        $campo[count($campo)] = $this->quf_vagas;
        $campo[count($campo)] = $this->quf_salario;
        $campo[count($campo)] = $this->quf_participacao;
        $campo[count($campo)] = $this->bt_add;
        $campo[count($campo)] = $this->bt_del;

        echo json_encode($campo);
        exit;
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
        $dados_colaborador = $this->colaborador->getColaborador($id)[0];
        // debug($dados_colaborador);
        $this->def_campos($dados_colaborador, 0, $show);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->col_id;
        $campos[0][count($campos[0])] = $this->emp_id;
        $campos[0][count($campos[0])] = $this->emp_id_registro;
        $campos[0][count($campos[0])] = $this->cag_id;
        $campos[0][count($campos[0])] = $this->set_id;
        $campos[0][count($campos[0])] = $this->col_cpf;
        $campos[0][count($campos[0])] = $this->col_matricula;
        $campos[0][count($campos[0])] = $this->col_nome;
        $campos[0][count($campos[0])] = $this->col_nome_social;
        $campos[0][count($campos[0])] = $this->col_data_nascimento;
        $campos[0][count($campos[0])] = $this->col_genero;
        $campos[0][count($campos[0])] = $this->col_estado_civil;

        $secao[1] = 'Endereço';
        $campos[1][0] = '';
        $campos[1][count($campos[1])] = $this->col_cep;
        $campos[1][count($campos[1])] = $this->col_cidade;
        $campos[1][count($campos[1])] = $this->col_estado;
        $campos[1][count($campos[1])] = $this->col_bairro;
        $campos[1][count($campos[1])] = $this->col_endereco;
        $campos[1][count($campos[1])] = $this->col_numero;
        $campos[1][count($campos[1])] = $this->col_complemento;
        $campos[1][count($campos[1])] = $this->col_telefone_celular;
        $campos[1][count($campos[1])] = $this->col_email;

        $secao[2] = 'Contrato';
        $campos[2][0] = '';
        $campos[2][count($campos[2])] = $this->col_vinculo;
        $campos[2][count($campos[2])] = $this->col_cargahoraria;
        $campos[2][count($campos[2])] = $this->col_data_admissao;
        $campos[2][count($campos[2])] = $this->col_data_demissao;
        $campos[2][count($campos[2])] = $this->col_tipo;
        $campos[2][count($campos[2])] = $this->col_salario;
        $campos[2][count($campos[2])] = $this->col_pctparticipacao;
        $campos[2][count($campos[2])] = $this->col_situacao;
        $campos[2][count($campos[2])] = $this->col_vale;
        $campos[2][count($campos[2])] = $this->col_vt;
        $campos[2][count($campos[2])] = $this->col_folgasemana;
        $campos[2][count($campos[2])] = $this->col_folgadomingo;
        $campos[2][count($campos[2])] = $this->col_metropolitana;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_colaborador', $id);

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
            $this->colaborador->delete($id);
            $ret['erro'] = false;
            session()->setFlashdata('msg', 'Colaborador Excluído com Sucesso');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $ret['erro'] = true;
            $ret['msg']  = 'Não foi possível Excluir o Colaborador, Verifique!';
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
        $id = new MyCampo('rh_colaborador', 'col_id');
        $id->valor = isset($dados['col_id']) ? $dados['col_id'] : '';
        $this->col_id = $id->crOculto();

        $empresas           = explode(',', session()->get('usu_empresa'));
        $empresa            = new ConfigEmpresaModel();
        $dados_emp          = $empresa->getEmpresa($empresas);
        $opc_emp            = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo('rh_colaborador', 'emp_id');
        $emp->valor = $emp->selecionado = isset($dados['emp_id']) ? $dados['emp_id'] : '';
        $emp->obrigatorio           = true;
        $emp->opcoes                = $opc_emp;
        $emp->largura               = 50;
        $emp->leitura               = $show;
        $this->emp_id               = $emp->crSelect();

        $reg                        = new MyCampo('rh_colaborador', 'emp_id_registro');
        $reg->valor = $emp->selecionado = isset($dados['emp_id_registro']) ? $dados['emp_id_registro'] : '';
        $reg->obrigatorio           = true;
        $reg->opcoes                = $opc_emp;
        $reg->largura               = 50;
        $reg->leitura               = $show;
        $this->emp_id_registro               = $reg->crSelect();

        $cargos                     = new RechumCargoModel();
        $dados_cag                  = $cargos->getCargo();
        $opc_cag                    = array_column($dados_cag, 'cag_nome', 'cag_id');

        $cag                        = new MyCampo('rh_colaborador', 'cag_id');
        $cag->valor = $cag->selecionado = isset($dados['cag_id']) ? $dados['cag_id'] : '';
        $cag->obrigatorio           = true;
        $cag->opcoes                = $opc_cag;
        $cag->largura               = 30;
        $cag->leitura               = $show;
        $this->cag_id               = $cag->crSelect();

        $setores            = new RechumSetorModel();
        $dados_set          = $setores->getSetor();
        $opc_set            = array_column($dados_set, 'set_nome', 'set_id');

        $set                        = new MyCampo('rh_colaborador', 'set_id');
        $set->valor = $set->selecionado = isset($dados['set_id']) ? $dados['set_id'] : '';
        $set->obrigatorio           = true;
        $set->opcoes                = $opc_set;
        $set->largura               = 30;
        $set->leitura               = $show;
        $this->set_id               = $set->crSelect();

        $cpf                        = new MyCampo('rh_colaborador', 'col_cpf');
        $cpf->tipo                  = 'cpf';
        $cpf->valor = $cpf->selecionado = isset($dados['col_cpf']) ? $dados['col_cpf'] : '';
        $cpf->obrigatorio           = true;
        $cpf->leitura               = $show;
        $cpf->funcBlur              = 'pesquisaCPF(this.value)';
        $this->col_cpf               = $cpf->crInput();

        $reg                        = new MyCampo('rh_colaborador', 'col_matricula');
        $reg->valor = $reg->selecionado = isset($dados['col_matricula']) ? $dados['col_matricula'] : '';
        $reg->obrigatorio           = true;
        $reg->leitura               = $show;
        $this->col_matricula               = $reg->crInput();

        $nom                        = new MyCampo('rh_colaborador', 'col_nome');
        $nom->valor = $nom->selecionado = isset($dados['col_nome']) ? $dados['col_nome'] : '';
        $nom->obrigatorio           = true;
        $nom->leitura               = $show;
        $this->col_nome               = $nom->crInput();

        $nos                        = new MyCampo('rh_colaborador', 'col_nome_social');
        $nos->valor = $nos->selecionado = isset($dados['col_nome_social']) ? $dados['col_nome_social'] : '';
        $nos->obrigatorio           = false;
        $nos->leitura               = $show;
        $this->col_nome_social      = $nos->crInput();

        $vin                        = new MyCampo('rh_colaborador', 'col_vinculo');
        $vin->valor = $vin->selecionado = isset($dados['col_vinculo']) ? $dados['col_vinculo'] : '';
        $vin->obrigatorio           = false;
        $vin->leitura               = $show;
        $this->col_vinculo          = $vin->crInput();

        $max = date("Y-m-d", strtotime(date("Y-m-d") . "-15 year"));
        $dtn                        = new MyCampo('rh_colaborador', 'col_data_nascimento');
        $dtn->valor = $dtn->selecionado = isset($dados['col_data_nascimento']) ? $dados['col_data_nascimento'] : '';
        $dtn->obrigatorio           = false;
        $dtn->datamax               = $max;
        $dtn->leitura               = $show;
        $this->col_data_nascimento               = $dtn->crInput();

        $cho                        = new MyCampo('rh_colaborador', 'col_cargahoraria');
        $cho->valor = $cho->selecionado = isset($dados['col_cargahoraria']) ? $dados['col_cargahoraria'] : '';
        $cho->obrigatorio           = false;
        $cho->largura               = 30;
        $cho->leitura               = $show;
        $cho->maximo                = 300;
        $this->col_cargahoraria               = $cho->crInput();

        $gene['M'] = 'Masculino';
        $gene['F'] = 'Feminino';
        $gene['O'] = 'Outro';

        $gen                        = new MyCampo('rh_colaborador', 'col_genero');
        $gen->valor = $gen->selecionado = isset($dados['col_genero']) ? $dados['col_genero'] : '';
        $gen->obrigatorio           = false;
        $gen->opcoes                = $gene;
        $gen->largura               = 30;
        $gen->leitura               = $show;
        $this->col_genero           = $gen->crSelect();

        $esci['S'] = 'Solteiro(a)';
        $esci['C'] = 'Casado(a)';
        $esci['E'] = 'Separado(a)';
        $esci['D'] = 'Divorciado(a)';
        $esci['U'] = 'União Estável';

        $esc                        = new MyCampo('rh_colaborador', 'col_estado_civil');
        $esc->valor = $esc->selecionado = isset($dados['col_estado_civil']) ? $dados['col_estado_civil'] : '';
        $esc->obrigatorio           = false;
        $esc->opcoes                = $esci;
        $esc->largura               = 30;
        $esc->leitura               = $show;
        $this->col_estado_civil           = $esc->crSelect();


        $max = date("Y-m-d", strtotime(date("Y-m-d")));
        $dta                        = new MyCampo('rh_colaborador', 'col_data_admissao');
        $dta->valor = $dta->selecionado = isset($dados['col_data_admissao']) ? $dados['col_data_admissao'] : '';
        $dta->obrigatorio           = true;
        $dta->datamax               = $max;
        $dta->leitura               = $show;
        $this->col_data_admissao               = $dta->crInput();

        $max = date("Y-m-d", strtotime(date("Y-m-d")));
        $dtd                        = new MyCampo('rh_colaborador', 'col_data_demissao');
        $dtd->valor = $dtd->selecionado = isset($dados['col_data_demissao']) ? $dados['col_data_demissao'] : '';
        $dtd->obrigatorio           = false;
        $dtd->datamax               = $max;
        $dtd->leitura               = $show;
        $this->col_data_demissao               = $dtd->crInput();

        $tipo[0] = 'Normal';
        $tipo[1] = 'Tipo 1';
        $tipo[2] = 'Tipo 2';
        $tipo[9] = 'Tipo 9';
        $tipo[10] = 'Tipo 10';
        $tipo[11] = 'Tipo 11';
        $tipo[12] = 'Tipo 12';
        $tip                        = new MyCampo('rh_colaborador', 'col_tipo');
        $tip->valor = $tip->selecionado = isset($dados['col_tipo']) ? $dados['col_tipo'] : 0;
        $tip->obrigatorio           = true;
        $tip->opcoes                = $tipo;
        $tip->largura               = 30;
        $tip->leitura               = $show;
        $this->col_tipo             = $tip->crSelect();

        $sal                        = new MyCampo('rh_colaborador', 'col_salario');
        $sal->valor = $sal->selecionado = isset($dados['col_salario']) ? $dados['col_salario'] : '';
        $sal->obrigatorio           = false;
        $sal->leitura               = $show;
        $sal->largura               = 30;
        $this->col_salario               = $sal->crInput();

        $par                        = new MyCampo('rh_colaborador', 'col_pctparticipacao');
        $par->valor = $par->selecionado = isset($dados['col_pctparticipacao']) ? $dados['col_pctparticipacao'] : '';
        $par->obrigatorio           = false;
        $par->leitura               = $show;
        $par->largura               = 30;
        $this->col_pctparticipacao               = $par->crInput();

        $situ['Trabalhando'] = 'Trabalhando';
        $situ['Demitido'] = 'Demitido';
        $sit                        = new MyCampo('rh_colaborador', 'col_situacao');
        $sit->valor = $sit->selecionado = isset($dados['col_situacao']) ? $dados['col_situacao'] : 'Trabalhando';
        $sit->obrigatorio           = true;
        $sit->opcoes                = $situ;
        $sit->largura               = 30;
        $sit->leitura               = $show;
        $this->col_situacao         = $sit->crSelect();

        $val                        = new MyCampo('rh_colaborador', 'col_vale');
        $val->valor = $val->selecionado = isset($dados['col_vale']) ? $dados['col_vale'] : 40;
        $val->minimo                = 0;
        $val->maximo                = 100;
        $val->obrigatorio           = true;
        $val->leitura               = $show;
        $val->largura               = 30;
        $this->col_vale               = $val->crInput();

        $simnao['S'] = 'Sim';
        $simnao['N'] = 'Não';

        $vat                        = new MyCampo('rh_colaborador', 'col_vt');
        $vat->valor = $vat->selecionado = isset($dados['col_vt']) ? $dados['col_vt'] : '';
        $vat->obrigatorio           = true;
        $vat->leitura               = $show;
        $vat->opcoes                = $simnao;
        $this->col_vt               = $vat->crRadio();

        $semana[0] = 'Domingo';
        $semana[1] = 'Segunda-feira';
        $semana[2] = 'Terça-feira';
        $semana[3] = 'Quarta-feira';
        $semana[4] = 'Quinta-feira';
        $semana[5] = 'Sexta-feira';
        $semana[6] = 'Sábado';

        $fose                        = new MyCampo('rh_colaborador', 'col_folgasemana');
        $fose->valor = $fose->selecionado = isset($dados['col_folgasemana']) ? $dados['col_folgasemana'] : '';
        $fose->obrigatorio           = true;
        $fose->opcoes                = $semana;
        $fose->largura               = 30;
        $fose->leitura               = $show;
        $this->col_folgasemana           = $fose->crSelect();

        $dom[1] = 'Primeiro';
        $dom[2] = 'Segundo';
        $dom[3] = 'Terceiro';
        $dom[4] = 'Quarto';
        $dom[0] = 'Todos';

        $fodo                        = new MyCampo('rh_colaborador', 'col_folgadomingo');
        $fodo->valor = $fodo->selecionado = isset($dados['col_folgadomingo']) ? $dados['col_folgadomingo'] : '';
        $fodo->obrigatorio           = true;
        $fodo->opcoes                = $dom;
        $fodo->largura               = 30;
        $fodo->leitura               = $show;
        $this->col_folgadomingo           = $fodo->crSelect();

        $metr                        = new MyCampo('rh_colaborador', 'col_metropolitana');
        $metr->valor = $metr->selecionado = isset($dados['col_metropolitana']) ? $dados['col_metropolitana'] : 'N';
        $metr->obrigatorio           = true;
        $metr->leitura               = $show;
        $metr->opcoes                = $simnao;
        $this->col_metropolitana               = $metr->crRadio();

        $cep                        = new MyCampo('rh_colaborador', 'col_cep');
        $cep->tipo                  = 'cep';
        $cep->valor = $cep->selecionado = isset($dados['col_cep']) ? $dados['col_cep'] : '';
        $cep->funcBlur              = "pesquisacep(this, this.value, 'col_')";
        // $cep->obrigatorio           = true;
        $cep->leitura               = $show;
        $this->col_cep               = $cep->crInput();

        $end                        = new MyCampo('rh_colaborador', 'col_endereco');
        $end->valor = $end->selecionado = isset($dados['col_endereco']) ? $dados['col_endereco'] : '';
        // $end->obrigatorio           = true;
        $end->leitura               = $show;
        $this->col_endereco               = $end->crInput();

        $num                        = new MyCampo('rh_colaborador', 'col_numero');
        $num->valor = $num->selecionado = isset($dados['col_numero']) ? $dados['col_numero'] : '';
        // $num->obrigatorio           = true;
        $num->minimo                = 0;
        $num->maximo                = 99999;
        $num->largura               = '20';
        $num->leitura               = $show;
        $this->col_numero               = $num->crInput();

        $cpl                        = new MyCampo('rh_colaborador', 'col_complemento');
        $cpl->valor = $cpl->selecionado = isset($dados['col_complemento']) ? $dados['col_complemento'] : '';
        // $cpl->obrigatorio           = true;
        $cpl->leitura               = $show;
        $this->col_complemento               = $cpl->crInput();

        $bai                        = new MyCampo('rh_colaborador', 'col_bairro');
        $bai->valor = $bai->selecionado = isset($dados['col_bairro']) ? $dados['col_bairro'] : '';
        // $bai->obrigatorio           = true;
        $bai->leitura               = $show;
        $this->col_bairro               = $bai->crInput();

        $cid                        = new MyCampo('rh_colaborador', 'col_cidade');
        $cid->valor = $cid->selecionado = isset($dados['col_cidade']) ? $dados['col_cidade'] : '';
        // $cid->obrigatorio           = true;
        $cid->leitura               = $show;
        $this->col_cidade               = $cid->crInput();

        $est                        = new MyCampo('rh_colaborador', 'col_estado');
        $est->valor = $est->selecionado = isset($dados['col_estado']) ? $dados['col_estado'] : '';
        // $est->obrigatorio           = true;
        $est->leitura               = $show;
        $this->col_estado               = $est->crInput();

        $cel                        = new MyCampo('rh_colaborador', 'col_telefone_celular');
        $cel->tipo                  = 'celular';
        $cel->valor = $cel->selecionado = isset($dados['col_telefone_celular']) ? $dados['col_telefone_celular'] : '';
        $cel->obrigatorio           = false;
        $cel->leitura               = $show;
        $this->col_telefone_celular               = $cel->crInput();

        $ema                        = new MyCampo('rh_colaborador', 'col_email');
        $ema->tipo                  = 'email';
        $ema->valor = $ema->selecionado = isset($dados['col_email']) ? $dados['col_email'] : '';
        // $ema->obrigatorio           = true;
        $ema->leitura               = $show;
        $this->col_email               = $ema->crInput();
    }



    /**
     * Definição de Campos
     * def_campos_cargo
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_cargo($dados = false, $pos = 0, $show = false)
    {
        $id = new MyCampo('rh_colaborador_cargo', 'quf_id');
        $id->id = $id->nome = "quf_id[$pos]";
        $id->valor = isset($dados['quf_id']) ? $dados['quf_id'] : '';
        $id->ordem = $pos;
        $this->quf_id = $id->crOculto();

        $cargos            = new RechumCargoModel();
        $dados_cag          = $cargos->getCargo();
        $opc_cag            = array_column($dados_cag, 'cag_nome', 'cag_id');

        $cag                        = new MyCampo('rh_colaborador_cargo', 'cag_id');
        $cag->id = $cag->nome       = "cag_id[$pos]";
        $cag->valor = $cag->selecionado = isset($dados['cag_id']) ? $dados['cag_id'] : '';
        $cag->ordem                 = $pos;
        $cag->obrigatorio           = true;
        $cag->opcoes                = $opc_cag;
        $cag->largura               = 30;
        $cag->leitura               = $show;
        $cag->dispForm              = '4col';
        $this->cag_id               = $cag->crSelect();

        $vag                        = new MyCampo('rh_colaborador_cargo', 'quf_vagas');
        $vag->id = $vag->nome       = "quf_vagas[$pos]";
        $vag->valor = $vag->selecionado = isset($dados['quf_vagas']) ? $dados['quf_vagas'] : '';
        $vag->ordem                 = $pos;
        $vag->obrigatorio           = true;
        $vag->largura               = 20;
        $vag->leitura               = $show;
        $vag->dispForm              = '4col';
        $this->quf_vagas               = $vag->crInput();

        $sal                        = new MyCampo('rh_colaborador_cargo', 'quf_salario');
        $sal->id = $sal->nome       = "quf_salario[$pos]";
        $sal->valor = $sal->selecionado = isset($dados['quf_salario']) ? $dados['quf_salario'] : '';
        $sal->ordem                 = $pos;
        $sal->obrigatorio           = true;
        $sal->largura               = 25;
        $sal->leitura               = $show;
        $sal->dispForm              = '4col';
        $this->quf_salario               = $sal->crInput();

        $par                        = new MyCampo('rh_colaborador_cargo', 'quf_participacao');
        $par->id = $par->nome       = "quf_participacao[$pos]";
        $par->valor = $par->selecionado = isset($dados['quf_participacao']) ? $dados['quf_participacao'] : '';
        $par->ordem                 = $pos;
        $par->obrigatorio           = true;
        $par->largura               = 20;
        $par->leitura               = $show;
        $par->dispForm              = '4col';
        $this->quf_participacao               = $par->crInput();

        $atrib['data-index'] = $pos;
        $add            = new MyCampo();
        $add->attrdata  = $atrib;
        $add->nome      = "bt_add[$pos]";
        $add->id        = "bt_add[$pos]";
        $add->i_cone    = "<i class='fas fa-plus'></i>";
        $add->place     = "Adicionar Função";
        $add->classep    = "btn-outline-success btn-sm bt-repete mt-4";
        $add->funcChan = "addCampo('" . base_url("RhColaborador/add_campo") . "','cargos',this)";
        $this->bt_add   = $add->crBotao();

        $del            = new MyCampo();
        $del->attrdata  = $atrib;
        $del->nome      = "bt_del[$pos]";
        $del->id        = "bt_del[$pos]";
        $del->i_cone     = "<i class='fas fa-trash'></i>";
        $del->place     = "Excluir Função";
        $del->classep    = "btn-outline-danger btn-sm bt-exclui mt-4";
        $del->funcChan = "exclui_campo('cargos',this)";
        $this->bt_del   = $del->crBotao();
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
        if ($this->colaborador->save($dados)) {
            $ret['erro'] = false;
            $ret['msg'] = 'Colaborador gravado com Sucesso!!!';
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $erros = $this->colaborador->errors();
            $ret['msg'] = 'Não foi possível gravar o Colaborador, Verifique!<br><br>';
            foreach ($erros as $erro) {
                $ret['msg'] .= $erro;
            }
        }
        echo json_encode($ret);
    }
}
