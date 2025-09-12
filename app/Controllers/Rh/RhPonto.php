<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Entities\Cargo;
use App\Entities\Colaborador;
use App\Entities\Jornada;
use App\Entities\Ponto;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumCargoModel;
use App\Models\Rechum\RechumColaboradorModel;
use App\Models\Rechum\RechumPontoModel;
use App\Models\Rechum\RechumSetorModel;
use Ark4ne\XlReader\Factory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RhPonto extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $ponto;
    public $jornada;
    public $empresa;
    public $colaborador;
    public $cargo;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'];
        $this->common       = new CommonModel();
        $this->ponto       = new RechumPontoModel();
        $this->empresa       = new ConfigEmpresaModel();
        $this->colaborador   = new RechumColaboradorModel();
        $this->cargo         = new RechumCargoModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'pon_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');

        $import          = new MyCampo();
        $import->nome    = 'bt_import';
        $import->id      = 'bt_import';
        $import->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                              <i class="fa-solid fa-upload" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $import->i_cone  .= '<div class="align-items-start txt-bt-manut d-none">Importar</div>';
        $import->place    = 'Importar';
        $import->funcChan = 'redireciona(\'RhPonto/import/\')';
        $import->classep  = 'btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_import = $import->crBotao();

        $this->data['botao'] = $this->bt_import;
        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']         = 'ponto';
        $this->data['colunas'] = montaColunasLista($this->data, 'col_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');
        $this->data['campos']         = $campos;
        $this->data['script'] = "<script>carrega_lista('empresa', '" . $this->data['url_lista'] . "','" . $this->data['nome'] . "');</script>";
        echo view('vw_lista_filtrada', $this->data);

        // echo view('vw_lista', $this->data);
    }

    public function def_campos_lista()
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
        // $emp->funcChan              = 'carrega_saldos()';
        $emp->dispForm              = '2col';
        $emp->largura               = 50;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        $emp->funcChan              = "carrega_lista(this,'RhPonto/lista','ponto')";
        $this->dash_empresa         = $emp->crSelect();
    }

    public function edit($id)
    {
        $dados_ponto = $this->ponto->getPonto($id);
        $compete = $dados_ponto[0]['pon_mesanocompetencia'];
        $colabor = $dados_ponto[0]['col_id'];
        $dados_folha = $this->ponto->getListaPonto($colabor, $compete);
        $dados_resumo = $this->ponto->getResumoPonto($colabor, $compete)[0];
        $dados_colaborador = $this->colaborador->getColaborador($colabor)[0];
        $this->def_campos_colab($dados_colaborador, 0, true);
        $secao[0] = 'Dados Gerais';
        $campos[0][0] = $this->col_nome;
        $campos[0][count($campos[0])] = $this->col_cpf;
        $campos[0][count($campos[0])] = $this->col_matricula;
        $campos[0][count($campos[0])] = $this->cag_id;
        $campos[0][count($campos[0])] = $this->set_id;
        $campos[0][count($campos[0])] = $this->col_data_admissao;
        $campos[0][count($campos[0])] = $this->col_folgasemana;
        $campos[0][count($campos[0])] = $this->col_folgadomingo;

        $this->def_campos_resumo($dados_resumo, true);
        $campos[0][count($campos[0])] = $this->atestado;
        $campos[0][count($campos[0])] = $this->folga;
        $campos[0][count($campos[0])] = $this->banco;
        $campos[0][count($campos[0])] = $this->abono;
        $campos[0][count($campos[0])] = $this->dayoff;
        $campos[0][count($campos[0])] = $this->falta;
        $campos[0][count($campos[0])] = $this->ferias;
        $campos[0][count($campos[0])] = $this->inss;
        $campos[0][count($campos[0])] = $this->neutro;
        $campos[0][count($campos[0])] = $this->vazio;


        $ct = 0;
        for ($p = 0; $p < count($dados_folha); $p++) {
            if ($dados_folha[$p]['pon_data'] !=  '') {
                $this->def_campos_ponto($dados_folha[$p], $ct, false);
                $campos[0][count($campos[0])] = "<div class='col-12 col-12 float-start'>";
                $campos[0][count($campos[0])] = $this->pon_id;
                $campos[0][count($campos[0])] = $this->pon_data;
                $campos[0][count($campos[0])] = $this->pon_ent1;
                $campos[0][count($campos[0])] = $this->pon_sai1;
                $campos[0][count($campos[0])] = $this->pon_ent2;
                $campos[0][count($campos[0])] = $this->pon_sai2;
                $campos[0][count($campos[0])] = "</div>";
                $ct++;
            }
        }

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = '';

        // BUSCAR DADOS DO LOG
        $this->data['log'] = buscaLog('rh_colaborador', $id);

        echo view('vw_edicao', $this->data);
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_ponto($dados = false, $pos = 0, $show = false)
    {
        $id = new MyCampo('rh_ponto', 'pon_id');
        $id->valor = isset($dados['pon_id']) ? $dados['pon_id'] : '';
        $id->id = $id->nome       = "pon_id[$pos]";
        $this->pon_id = $id->crOculto();

        $dat                        = new MyCampo('rh_ponto', 'pon_data');
        $dat->valor                 = isset($dados['pon_data']) ? $dados['pon_data'] : '';
        $dat->id = $dat->nome       = "pon_data[$pos]";
        $dat->ordem                 = $pos;
        $dat->largura               = 20;
        $dat->leitura               = true;
        $dat->dispForm              = 'col-2';
        $this->pon_data             = $dat->crInput();

        $en1                        = new MyCampo('rh_ponto', 'pon_ent1');
        $en1->valor                 = isset($dados['pon_ent1']) ? $dados['pon_ent1'] : '';
        $en1->id = $en1->nome       = "pon_ent1[$pos]";
        $en1->ordem                 = $pos;
        $en1->largura               = 20;
        $en1->leitura               = $show;
        $en1->dispForm              = 'col-2';
        $en1->funcChan              = 'atualiza_ponto(this)';
        $this->pon_ent1             = $en1->crInput();

        $sa1                        = new MyCampo('rh_ponto', 'pon_sai1');
        $sa1->valor                 = isset($dados['pon_sai1']) ? $dados['pon_sai1'] : '';
        $sa1->id = $sa1->nome       = "pon_sai1[$pos]";
        $sa1->ordem                 = $pos;
        $sa1->largura               = 20;
        $sa1->leitura               = $show;
        $sa1->dispForm              = 'col-2';
        $sa1->funcChan              = 'atualiza_ponto(this)';
        $this->pon_sai1             = $sa1->crInput();

        $en2                        = new MyCampo('rh_ponto', 'pon_ent2');
        $en2->valor                 = isset($dados['pon_ent2']) ? $dados['pon_ent2'] : '';
        $en2->id = $en2->nome       = "pon_ent2[$pos]";
        $en2->ordem                 = $pos;
        $en2->largura               = 20;
        $en2->leitura               = $show;
        $en2->dispForm              = 'col-2';
        $en2->funcChan              = 'atualiza_ponto(this)';
        $this->pon_ent2             = $en2->crInput();

        $sa2                        = new MyCampo('rh_ponto', 'pon_sai2');
        $sa2->valor                 = isset($dados['pon_sai2']) ? $dados['pon_sai2'] : '';
        $sa2->id = $sa2->nome       = "pon_sai2[$pos]";
        $sa2->ordem                 = $pos;
        $sa2->largura               = 20;
        $sa2->leitura               = $show;
        $sa2->dispForm              = 'col-2';
        $sa2->funcChan              = 'atualiza_ponto(this)';
        $this->pon_sai2             = $sa2->crInput();
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_resumo($dados = false, $show = false)
    {
        $ate                        = new MyCampo('rh_ponto', 'pon_atestado');
        $ate->valor                 = isset($dados['pon_atestado']) ? $dados['pon_atestado'] : '';
        $ate->tipo                  = 'sonumero';
        $ate->largura               = 20;
        $ate->leitura               = $show;
        $ate->dispForm              = 'col-1';
        $this->atestado             = $ate->crInput();

        $fol                        = new MyCampo('rh_ponto', 'pon_folga');
        $fol->valor                 = isset($dados['pon_folga']) ? $dados['pon_folga'] : '';
        $fol->tipo                  = 'sonumero';
        $fol->largura               = 20;
        $fol->leitura               = $show;
        $fol->dispForm              = 'col-1';
        $this->folga                = $fol->crInput();

        $ban                        = new MyCampo('rh_ponto', 'pon_banco');
        $ban->valor                 = isset($dados['pon_banco']) ? $dados['pon_banco'] : '';
        $ban->tipo                  = 'sonumero';
        $ban->largura               = 20;
        $ban->leitura               = $show;
        $ban->dispForm              = 'col-1';
        $this->banco                = $ban->crInput();

        $abo                        = new MyCampo('rh_ponto', 'pon_abono');
        $abo->valor                 = isset($dados['pon_abono']) ? $dados['pon_abono'] : '';
        $abo->tipo                  = 'sonumero';
        $abo->largura               = 20;
        $abo->leitura               = $show;
        $abo->dispForm              = 'col-1';
        $this->abono                = $abo->crInput();

        $day                        = new MyCampo('rh_ponto', 'pon_dayoff');
        $day->valor                 = isset($dados['pon_dayoff']) ? $dados['pon_dayoff'] : '';
        $day->tipo                  = 'sonumero';
        $day->largura               = 20;
        $day->leitura               = $show;
        $day->dispForm              = 'col-1';
        $this->dayoff               = $day->crInput();

        $fal                        = new MyCampo('rh_ponto', 'pon_falta');
        $fal->valor                 = isset($dados['pon_falta']) ? $dados['pon_falta'] : '';
        $fal->tipo                  = 'sonumero';
        $fal->largura               = 20;
        $fal->leitura               = $show;
        $fal->dispForm              = 'col-1';
        $this->falta                = $fal->crInput();

        $fer                        = new MyCampo('rh_ponto', 'pon_ferias');
        $fer->valor                 = isset($dados['pon_ferias']) ? $dados['pon_ferias'] : '';
        $fer->tipo                  = 'sonumero';
        $fer->largura               = 20;
        $fer->leitura               = $show;
        $fer->dispForm              = 'col-1';
        $this->ferias               = $fer->crInput();

        $ins                        = new MyCampo('rh_ponto', 'pon_inss');
        $ins->valor                 = isset($dados['pon_inss']) ? $dados['pon_inss'] : '';
        $ins->tipo                  = 'sonumero';
        $ins->largura               = 20;
        $ins->leitura               = $show;
        $ins->dispForm              = 'col-1';
        $this->inss                 = $ins->crInput();

        $neu                        = new MyCampo('rh_ponto', 'pon_neutro');
        $neu->valor                 = isset($dados['pon_neutro']) ? $dados['pon_neutro'] : '';
        $neu->tipo                  = 'sonumero';
        $neu->largura               = 20;
        $neu->leitura               = $show;
        $neu->dispForm              = 'col-1';
        $this->neutro               = $neu->crInput();

        $vaz                        = new MyCampo('rh_ponto', 'pon_vazio');
        $vaz->valor                 = isset($dados['pon_vazio']) ? $dados['pon_vazio'] : '';
        $vaz->tipo                  = 'sonumero';
        $vaz->largura               = 20;
        $vaz->leitura               = $show;
        $vaz->dispForm              = 'col-1';
        $this->vazio                = $vaz->crInput();
    }

    /**
     * Definição de Campos
     * def_campos
     *
     * @param bool $dados 
     * @return void
     */
    public function def_campos_colab($dados = false, $pos = 0, $show = false)
    {

        $cargos            = new RechumCargoModel();
        $dados_cag          = $cargos->getCargo($dados['cag_id']);
        $opc_cag            = array_column($dados_cag, 'cag_nome', 'cag_id');

        $cag                        = new MyCampo('rh_colaborador', 'cag_id');
        $cag->valor = $cag->selecionado = isset($dados['cag_id']) ? $dados['cag_id'] : '';
        $cag->obrigatorio           = true;
        $cag->opcoes                = $opc_cag;
        $cag->largura               = 50;
        $cag->leitura               = $show;
        $cag->dispForm              = '2col';
        $this->cag_id               = $cag->crSelect();

        $setores            = new RechumSetorModel();
        $dados_set          = $setores->getSetor($dados['set_id']);
        $opc_set            = array_column($dados_set, 'set_nome', 'set_id');

        $set                        = new MyCampo('rh_colaborador', 'set_id');
        $set->valor = $set->selecionado = isset($dados['set_id']) ? $dados['set_id'] : '';
        $set->obrigatorio           = true;
        $set->opcoes                = $opc_set;
        $set->largura               = 30;
        $set->leitura               = $show;
        $set->dispForm              = '2col';
        $this->set_id               = $set->crSelect();

        $cpf                        = new MyCampo('rh_colaborador', 'col_cpf');
        $cpf->tipo                  = 'cpf';
        $cpf->valor = $cpf->selecionado = isset($dados['col_cpf']) ? $dados['col_cpf'] : '';
        $cpf->obrigatorio           = true;
        $cpf->leitura               = $show;
        $cpf->dispForm              = '3col';
        $this->col_cpf               = $cpf->crInput();

        $reg                        = new MyCampo('rh_colaborador', 'col_matricula');
        $reg->valor = $reg->selecionado = isset($dados['col_matricula']) ? $dados['col_matricula'] : '';
        $reg->obrigatorio           = true;
        $reg->leitura               = $show;
        $reg->dispForm              = '3col';
        $this->col_matricula               = $reg->crInput();

        $nom                        = new MyCampo('rh_colaborador', 'col_nome');
        $nom->valor = $nom->selecionado = isset($dados['col_nome']) ? $dados['col_nome'] : '';
        $nom->obrigatorio           = true;
        $nom->leitura               = $show;
        $nom->dispForm              = '3col';
        $this->col_nome               = $nom->crInput();

        $max = date("Y-m-d", strtotime(date("Y-m-d")));
        $dta                        = new MyCampo('rh_colaborador', 'col_data_admissao');
        $dta->valor = $dta->selecionado = isset($dados['col_data_admissao']) ? $dados['col_data_admissao'] : '';
        $dta->obrigatorio           = true;
        $dta->datamax               = $max;
        $dta->leitura               = $show;
        $dta->dispForm              = '3col';
        $this->col_data_admissao               = $dta->crInput();

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
        $fose->dispForm              = '3col';
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
        $fodo->dispForm              = '3col';
        $this->col_folgadomingo           = $fodo->crSelect();
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
        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = false;
        }

        $dados_pontos = $this->ponto->getResumoPonto(false, false, $param);

        $pontos = [
            'data' => montaListaColunas($this->data, 'pon_id', $dados_pontos, 'col_nome'),
        ];
        // }
        echo json_encode($pontos);
    }

    /**
     * Inclusão
     * importArquivo
     *
     * @return void
     */
    public function import()
    {
        $this->def_campos();

        $secao[0] = 'Selecionar Arquivo';
        $campos[0][0] = $this->arquivo;
        $campos[0][1] = $this->botao;

        $this->data['desc_metodo'] = 'Importação de ';
        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'uploadStore';

        echo view('vw_importar', $this->data);
    }

    /**
     * Faz Upload e Salva no Banco
     * uploadStore
     *
     * @return void
     */
    public function uploadStore()
    {
        $ret = [];
        $ret['erro'] = false;
        $file = $this->request->getFile('arquivo');
        $uploadPath = WRITEPATH . 'uploads/rh/ponto/';
        $fileName = $file->getName();
        $fullPath = $uploadPath . $fileName;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
        if ($file->isValid() && !$file->hasMoved()) {
            $file->move($uploadPath);
            $arquivo = $uploadPath . $file->getName();

            // Carrega a planilha
            $spreadsheet = IOFactory::load($arquivo);

            // Seleciona a primeira aba (sheet)
            $sheet = $spreadsheet->getActiveSheet();

            $ponto = new Ponto();
            $regcolab = new Colaborador();
            $regcargo = new Cargo();
            $vemitens = false;
            $competencia = '';
            $contador = 0;
            $cnpj = '';
            $proximo = '';
            $fieldit['Data'] = 'pon_data';
            $fieldit['Ent. 1'] = 'pon_ent1';
            $fieldit['Saí. 1'] = 'pon_sai1';
            $fieldit['Ent. 2'] = 'pon_ent2';
            $fieldit['Saí. 2'] = 'pon_sai2';
            $fieldit['Normais'] = 'pon_normais';
            $fieldit['Faltas'] = 'pon_faltas';
            $fieldit['Not.Tot.'] = 'pon_noturnas';
            $fieldit['Not.'] = 'pon_noturnas';
            $fieldit['BSaldo'] = 'pon_bancosaldo';
            $fieldit['BTotal'] = 'pon_bancototal';
            $fieldit['BCred.'] = 'pon_bancocre';
            $fieldit['BDeb.'] = 'pon_bancodeb';

            $ordem = [];
            $ctord = 0;
            $maxord = 0;
            $parar = false;
            foreach ($sheet->getRowIterator(2) as $row) { // Começa da linha 2
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Inclui células vazias

                if ($parar) {
                    break;
                }
                foreach ($cellIterator as $cell) {
                    $valor = $cell->getValue();
                    $coluna = $cell->getColumn(); // Obtém o identificador da coluna (ex: A, B, C)

                    // Verifica se a célula não está vazia
                    if (($valor !== null && $valor !== '') || $vemitens) {
                        // debug("Próximo ".$proximo);
                        // debug("Coluna: $coluna, Valor: $valor"); // Exibe a coluna e o valor
                        // debug(substr(trim($valor),0,8)); // Exibe a coluna e o valor
                        if (substr(trim($valor), 0, 3) == '(*)') {
                            // GRAVAR PONTO
                            $vemitens = false;
                            $proximo  = '';
                            continue;
                        }
                        if (substr(trim($valor), 0, 3) == 'De:' || substr(trim($valor),0,8) == 'Período') {
                            $competencia = substr(trim($valor), -10);
                            $ponto->pon_competencia = dataBrToDb($competencia);
                            $proximo = '';
                            continue;
                        }
                        if ((trim($valor) == 'CNPJ' || trim($valor) == 'CNPJ:') && $cnpj == '') {
                            $proximo = 'cnpj';
                            continue;
                        }
                        if ($proximo == 'cnpj') {
                            if ($cnpj == '') {
                                // debug($valor, false);
                                $cnpj = formatCNPJ(trim($valor));
                                // echo 'CNPJ: '.$cnpj."<br>";
                                // debug($cnpj, false);
                                $empresatrab = $this->empresa->getEmpresaCNPJ($cnpj);
                                // debug($empresatrab, true);
                                if (count($empresatrab) < 0) {
                                    $ret['erro'] = true;
                                    $ret['msg'] = 'CNPJ ' . $cnpj . ' Não cadastrado';
                                    $parar = true;
                                    break;
                                } else {
                                    $ponto->emp_id = $empresatrab[0]['emp_id'];
                                    $regcolab->emp_id = $empresatrab[0]['emp_id'];
                                }
                            }
                            $proximo = '';
                            continue;
                        }
                        if ((trim($valor) == 'Nome' || trim($valor) == 'Nome:')) {
                            $proximo = 'nome';
                            continue;
                        }
                        if ($proximo == 'nome') {
                            $regcolab->col_nome = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ((trim($valor) == 'CPF' || trim($valor) == 'CPF:')) {
                            $proximo = 'cpf';
                            // debug($proximo);
                            continue;
                        }
                        if ((trim($valor) == 'C.T.P.S.' || trim($valor) == 'C.T.P.S.:')) {
                            $proximo = 'cpf';
                            // debug($proximo);
                            continue;
                        }
                        if ($proximo == 'cpf') {
                            $regcolab->col_cpf = trim($valor);
                            $colab = $this->colaborador->getCPF($regcolab->col_cpf);
                            // debug($regcolab->toArray());
                            // debug($colab);
                            if (count($colab) > 0) {
                                $contador++;
                                $ponto->col_id = $colab[0]['col_id'];
                                $regcolab->col_id = $colab[0]['col_id'];
                            } else {
                                $regcolab->col_id = false;
                                $regcolab->col_cpf = formatarCPF(apenasNumeros(trim($valor)));
                            }
                            $proximo = '';
                            continue;
                        }
                        if ((trim($valor) == 'Admissão' || trim($valor) == 'Admissão:') && !$regcolab->col_id) {
                            $proximo = 'admissao';
                            continue;
                        }
                        if ($proximo == 'admissao') {
                            // debug($valor, true);
                            if (gettype($valor) == 'string') {
                                $regcolab->col_data_admissao = dataBrToDb($valor);
                            } else {
                                $dataconv =  Date::excelToDateTimeObject($valor);
                                $regcolab->col_data_admissao = $dataconv->format('Y-m-d');
                            }
                            $proximo = '';
                            continue;
                        }
                        // if ((trim($valor) == 'Função' || trim($valor) == 'Função:') && !$regcolab->col_id) {
                        if ((trim($valor) == 'Função' || trim($valor) == 'Função:')) {
                            $proximo = 'funcao';
                            continue;
                        }
                        // debug($proximo);
                        if ($proximo == 'funcao') {
                            $regcargo->cag_nome = $valor;
                            $cargo = $this->cargo->getCargoSearch($regcargo->cag_nome);
                            if (count($cargo) > 0) {
                                $regcolab->cag_id = $cargo[0]['cag_id'];
                            } else {
                            // debug($regcargo);
                            // debug($cargo, true);
                                $this->cargo->insert($regcargo);
                                $regcolab->cag_id = $this->cargo->getInsertID();
                            }
                            $regcolab->emp_id = $ponto->emp_id;
                            // debug($regcolab);
                            if(!$regcolab->col_id){
                                try {
                                    // Tenta inserir os dados
                                    // debug($regcolab->toArray());
                                    $salvacol = $this->colaborador->insert($regcolab->toArray());
                                    
                                    // Obtém o ID inserido
                                    $col_id = $this->colaborador->getInsertID();
                                    // debug($col_id);
                                } catch (\Throwable $e) {
                                    $ret['erro'] = true;
                                    $ret['msg'] = 'Erro ao inserir colaborador: ' . $e->getMessage();
                                    // debug($ret);
                                    // echo json_encode($ret);
                                    break;
                                }
                            } else {
                                $col_id = $regcolab->col_id;
                                // debug($col_id);
                                try {
                                    // Tenta inserir os dados
                                    // debug($regcolab->toArray());
                                    $salvacol = $this->colaborador->update($col_id, $regcolab->toArray());
                                    
                                } catch (\Throwable $e) {
                                    $ret['erro'] = true;
                                    $ret['msg'] = 'Erro ao atualizar colaborador: ' . $e->getMessage();
                                    // debug($ret);
                                    // echo json_encode($ret);
                                    break;
                                }
                            }
                            $ponto->col_id = $col_id;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Data' || $valor == 'Data:') {
                            $ordem[$ctord] = $valor;
                            // debug($ordem);
                            $ctord++;
                            $proximo = 'colunas';
                            continue;
                        }
                        if (
                            $proximo == 'colunas'
                            && Trim($valor) != 'Carga'
                            && Trim($valor) != 'Ut60%'
                            && Trim($valor) != 'Fe100%'
                            && Trim($valor) != 'Atras.'
                            && Trim($valor) != 'Totais'
                        ) {
                            $ordem[$ctord] = $valor;
                            $ctord++;
                            if ($ctord == 12) {
                                $maxord = $ctord;

                                // debug('Valor '.$valor);
                                // debug('Maxordem '.$maxord);
                                // debug($ordem);
                                $ctord = 0;
                                $proximo = $ordem[$ctord];
                                $vemitens = true;
                                break;
                            } else {
                                $proximo = 'colunas';
                                continue;
                            }
                        } else if ((Trim($valor) == 'Atras.' || Trim($valor) == 'Atras.:' || Trim($valor) == 'Totais')) {
                            $maxord = $ctord;
                            // debug('Maxordem '.$maxord);
                            // debug($ordem);
                            $ctord = 0;
                            $proximo = $ordem[$ctord];
                            $vemitens = true;
                            break;
                        }
                        if ($vemitens && $proximo == $ordem[$ctord]) {
                            if (isset($fieldit[$ordem[$ctord]])) {
                                $obj = (string)$fieldit[$ordem[$ctord]];
                                // debug($ponto->{$obj});
                                $hora = trim($valor);
                                // debug('Valor '.$valor);
                                if (is_numeric($hora)) {
                                    $totalMinutes = round($hora * 1440); // Converte dias para minutos e arredonda

                                    $hours = floor($totalMinutes / 60); // Total de horas
                                    $minutes = $totalMinutes % 60; // Total de minutos

                                    // Formata a string como HHH:MM
                                    if ($hours > 23) {
                                        $formattedTime = sprintf('%03d:%02d', $hours, $minutes);
                                    } else {
                                        $formattedTime = sprintf('%02d:%02d', $hours, $minutes);
                                    }
                                    $hora = $formattedTime;
                                }
                                $ponto->{$obj} = $hora;
                                // debug($obj. ' = '.$ponto->{$obj});
                                // debug($ponto);
                                $ctord++;
                                // debug($maxord);
                                // debug($ctord);
                                if ($ctord == $maxord) {
                                    if (
                                        strlen($ponto->pon_ent1) +
                                        strlen($ponto->pon_sai1) +
                                        strlen($ponto->pon_ent2) +
                                        strlen($ponto->pon_sai2) +
                                        strlen($ponto->pon_normais)  > 0
                                        ) {
                                            // debug('Entrei aqui');
                                            try {
                                                // Verifica se já existe ponto para os dados fornecidos
                                                $jatempon = $this->ponto->getPontoUnico(
                                                    $ponto->emp_id,
                                                    $ponto->col_id,
                                                    $ponto->pon_competencia,
                                                    $ponto->pon_data
                                                );
                                                // debqug($jatempon);
                                                if ($jatempon) {
                                                    // Se já existe, atualiza
                                                    $ponto->pon_id = $jatempon[0]['pon_id'];
                                                    $salvapon = $this->ponto->update($ponto->pon_id, $ponto);
                                                } else {
                                                    // Se não existe, insere novo
                                                    // debug($ponto->toArray());
                                                    $salvapon = $this->ponto->insert($ponto->toArray());
                                                }

                                                // Debug para verificar resultado da operação
                                                // debug(var_dump($salvapon));
                                            } catch (\Throwable $e) {
                                                // Tratamento de erro com mensagem
                                                log_message('error', 'Erro ao salvar ponto: ' . $e->getMessage());
                                                // debug($e->getMessage());
                                                // Você pode usar esse retorno em API ou view
                                                // return [
                                                //     'status' => 'error',
                                                //     'message' => 'Erro ao salvar ponto: ' . $e->getMessage()
                                                // ];
                                                $ret['erro'] = true;
                                                $ret['msg'] = 'Erro ao salvar ponto: ' . $e->getMessage();
                                                // debug($ret);
                                                break;
                                                // echo json_encode($ret);

                                                // exit;
                                            }
                                    }
                                    // debug($ponto->toArray());
                                    $ponto = new Ponto();
                                    $ponto->emp_id = $empresatrab[0]['emp_id'];
                                    $ponto->col_id = $regcolab->col_id;
                                    // debug($competencia);
                                    $ponto->pon_competencia = dataBrToDb($competencia);
                                    $ctord = 0;
                                    $proximo = $ordem[$ctord];
                                    break;
                                } else {
                                    $proximo = $ordem[$ctord];
                                    // $vemitens = true;
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
            if (!$ret['erro']) {
                $ret['erro'] = false;
                $ret['msg'] = 'Importados os Pontos de ' . $contador . ' Colaboradores, \nda Empresa ' . $empresatrab[0]['emp_apelido'];
                session()->setFlashdata('msg', $ret['msg']);
                $ret['url'] = site_url($this->data['controler']);
            }
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Arquivo Inválido';
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
        $arq                        = new MyCampo();
        $arq->tipo                  = 'file';
        $arq->id = $arq->nome       = 'arquivo';
        $arq->valor = $arq->selecionado = '';
        $arq->obrigatorio           = true;
        $arq->label                 = '.';
        $arq->pasta                 = 'assets/uploads/rh/pontos';
        $arq->size                  = 100;
        $arq->largura               = 0;
        $arq->dispForm              = 'col-12';
        $arq->tipoArq               = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $this->arquivo               = $arq->crArquivo();

        $bot = new MyCampo();
        $bot->nome = 'Processar';
        $bot->id    = 'Processar';
        $bot->label = 'Processar';
        $bot->place = 'Processar';
        $bot->i_cone = "<i class='fa-solid fa-play'></i> Processar";
        $bot->classep = 'btn btn-warning';
        $bot->tipo = 'submit';
        // $bot->funcChan = 'submit()';
        $bot->dispForm  = 'col-2';
        $this->botao = $bot->crBotao();
    }

    public function atualizaPonto()
    {
        $dados = $this->request->getPost();
        $id = $dados['id'];
        $sql_dados = [
            'pon_id'    => $dados['id'],
            'pon_ent1' => $dados['ent1'],
            'pon_sai1' => $dados['sai1'],
            'pon_ent2' => $dados['ent2'],
            'pon_sai2' => $dados['sai2'],
        ];
        $this->ponto->save($sql_dados);

        $dados_ponto = $this->ponto->getListaPonto(false, false, $id);
        $compete = $dados_ponto[0]['pon_mesanocompetencia'];
        $colabor = $dados_ponto[0]['col_id'];
        // debug('ponto');
        // debug($dados_ponto);
        $compete = $dados_ponto[0]['pon_mesanocompetencia'];
        $colabor = $dados_ponto[0]['col_id'];
        $dados_resumo = $this->ponto->getResumoPonto($colabor, $compete)[0];
        // debug($dados_resumo);

        $ret['atestado'] = $dados_resumo['pon_atestado'];
        $ret['folga']    = $dados_resumo['pon_folga'];
        $ret['banco']    = $dados_resumo['pon_banco'];
        $ret['abono']    = $dados_resumo['pon_abono'];
        $ret['dayoff']   = $dados_resumo['pon_dayoff'];
        $ret['falta']    = $dados_resumo['pon_falta'];
        $ret['ferias']   = $dados_resumo['pon_ferias'];
        $ret['inss']     = $dados_resumo['pon_inss'];
        $ret['neutro']   = $dados_resumo['pon_neutro'];
        $ret['vazio']    = $dados_resumo['pon_vazio'];

        // debug($ret);

        echo json_encode($ret);
        exit;
    }
}
