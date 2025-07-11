<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Entities\Cargo;
use App\Entities\Colaborador;
use App\Entities\Holerite;
use App\Entities\HoleriteItem;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Rechum\RechumCargoModel;
use App\Models\Rechum\RechumColaboradorModel;
use App\Models\Rechum\RechumHoleriteItemModel;
use App\Models\Rechum\RechumHoleriteModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RhHolerite extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;
    public $holerite;
    public $holeriteitem;
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
        $this->holerite       = new RechumHoleriteModel();
        $this->holeriteitem       = new RechumHoleriteItemModel();
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
        $this->data['colunas'] = montaColunasLista($this->data, 'col_id');
        $this->data['url_lista'] = base_url($this->data['controler'] . '/lista');

        $import          = new MyCampo();
        $import->nome    = 'bt_import';
        $import->id      = 'bt_import';
        $import->i_cone  = '<div class="align-items-center py-1 text-start float-start font-weight-bold" style="">
                              <i class="fa-solid fa-upload" style="font-size: 2rem;" aria-hidden="true"></i></div>';
        $import->i_cone  .= '<div class="align-items-start txt-bt-manut d-none">Importar</div>';
        $import->place    = 'Importar';
        $import->funcChan = 'redireciona(\'RhHolerite/import/\')';
        $import->classep  = 'btn-outline-info bt-manut btn-sm mb-2 float-end add';
        $this->bt_import = $import->crBotao();

        $this->data['botao'] = $this->bt_import;

        $this->def_campos_lista();
        $campos[0] = $this->dash_empresa;

        $this->data['nome']         = 'holerite';
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
        $emp->funcChan              = "carrega_lista(this,'RhHolerite/lista','holerite')";
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
        // $empresas = explode(',',session()->get('usu_empresa'));
        $param = $_REQUEST['param'];
        if ($param == 'undefined') {
            $param = false;
        }

        $dados_holerites = $this->holerite->getHolerite(false, $param);
        $holerites = [
            'data' => montaListaColunas($this->data, 'col_id', $dados_holerites, 'col_nome'),
        ];
        // }
        echo json_encode($holerites);
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
        $file = $this->request->getFile('arquivo');
        $uploadPath = WRITEPATH . 'uploads/rh/holerite/';
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

            $holerite = new Holerite();
            $regcolab = new Colaborador();
            $regcargo = new Cargo();
            $salvarhol = false;
            $contador = 0;
            $cnpj = '';
            $parar = false;
            foreach ($sheet->getRowIterator(2) as $row) { // Começa da linha 2
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                if ($parar) {
                    break;
                }
                foreach ($cellIterator as $cell) {
                    $valor = $cell->getValue();
                    $coluna = $cell->getColumn(); // Obtém o identificador da coluna (ex: A, B, C)

                    // Verifica se a célula não está vazia
                    if ($valor !== null && $valor !== '') {
                        // echo "Coluna: $coluna, Valor: $valor<br>"; // Exibe a coluna e o valor
                        if (substr(trim($valor), 0, 6) == 'Resumo') {
                            $parar = true;
                            break;
                        }

                        if ($valor == 'CNPJ:' && $cnpj == '') {
                            $proximo = 'cnpj';
                            continue;
                        }
                        if ($proximo == 'cnpj') {
                            $resumo = false;
                            // debug($row['A']);
                            if ($cnpj == '') {
                                $cnpj = formatCNPJ(trim($valor));
                                // debug('CNPJ: '.$cnpj);
                                $empresa = $this->empresa->getEmpresaCNPJ($cnpj);
                                // debug($empresa, true);
                                if (count($empresa) < 0) {
                                    // debug('EMPRESA NÃO CADASTRADA');
                                } else {
                                    $holerite->emp_id = $empresa[0]['emp_id'];
                                    $regcolab->emp_id = $empresa[0]['emp_id'];
                                }
                            }
                            $proximo = '';
                            continue;
                        }

                        if ($valor == 'Emissão:') {
                            $proximo = 'emissao';
                            continue;
                        }
                        if ($proximo == 'emissao') {
                            $dataconv =  Date::excelToDateTimeObject($valor);
                            $holerite->hol_dataemissao = $dataconv->format('Y-m-d');
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Cálculo:') {
                            $proximo = 'calculo';
                            continue;
                        }
                        if ($proximo == 'calculo') {
                            $holerite->hol_calculo = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Horas:') {
                            $proximo = 'horaemissao';
                            continue;
                        }
                        if ($proximo == 'horaemissao') {
                            $dataconv =  Date::excelToDateTimeObject($valor);
                            $holerite->hol_horaemissao = $dataconv->format('H:i');
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Competência:') {
                            $proximo = 'competencia';
                            continue;
                        }
                        if ($proximo == 'competencia') {
                            $dataconv =  Date::excelToDateTimeObject($valor);
                            $holerite->hol_competencia = $dataconv->format('Y-m-d');
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Empr.:') {
                            $proximo = 'empregado';
                            continue;
                        }
                        if ($proximo == 'empregado') {
                            $regcolab->col_matricula = $valor;
                            $proximo = 'nomeempregado';
                            continue;
                        }
                        if ($proximo == 'nomeempregado') {
                            $regcolab->col_nome = $valor;
                            $proximo = '';
                            continue;
                        }
                        // debug($valor);
                        if ($valor == 'Situação:') {
                            $proximo = 'situacao';
                            continue;
                        }
                        if ($proximo == 'situacao') {
                            $regcolab->col_situacao = $valor;
                            $holerite->hol_situacao = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'CPF:') {
                            $proximo = 'cpf';
                            continue;
                        }
                        if ($proximo == 'cpf') {
                            // debug('CPF '.$valor);
                            $regcolab->col_cpf = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Adm:') {
                            $proximo = 'adm';
                            continue;
                        }
                        if ($proximo == 'adm') {
                            $dataconv =  Date::excelToDateTimeObject($valor);
                            $regcolab->col_data_admissao = $dataconv->format('Y-m-d');
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Vínculo:') {
                            $proximo = 'vinculo';
                            continue;
                        }
                        if ($proximo == 'vinculo') {
                            $regcolab->col_vinculo = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Horas Mês:') {
                            $proximo = 'cargahoraria';
                            continue;
                        }
                        if ($proximo == 'cargahoraria') {
                            $regcolab->col_cargahoraria = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Cargo:') {
                            $proximo = 'cargo';
                            continue;
                        }
                        if ($proximo == 'cargo') {
                            $regcargo->cag_id = $valor;
                            $proximo = 'nomecargo';
                            continue;
                        }
                        if ($proximo == 'nomecargo') {
                            $regcargo->cag_nome = $valor;
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'C.B.O:') {
                            $proximo = 'cbo';
                            continue;
                        }
                        if ($proximo == 'cbo') {
                            $regcargo->cag_cbo = $valor;
                            $cargo = $this->cargo->getCargoSearch($regcargo->cag_nome);
                            // debug($cargo);
                            if (count($cargo) > 0) {
                                $regcolab->cag_id = $cargo[0]['cag_id'];
                            } else {
                                $regcargo->cag_id = null;
                                // debug($regcargo);
                                $this->cargo->insert($regcargo);
                                $regcolab->cag_id = $this->cargo->getInsertID();
                            }
                            $regcargo = new Cargo();
                            $proximo = '';
                            continue;
                        }
                        if ($valor == 'Salário:') {
                            $proximo = 'salario';
                            continue;
                        }
                        if ($proximo == 'salario') {
                            $regcolab->col_salario = $valor;
                            $colab = $this->colaborador->getCPF($regcolab->col_cpf);
                            // debug(count($colab));
                            // echo 'Colaborador';
                            // echo "<pre>";
                            // print_r($regcolab);
                            // echo "</pre>";
                            if (count($colab) > 0) {
                                $holerite->col_id = $colab[0]['col_id'];
                                $regcolab->col_id = $colab[0]['col_id'];
                                $salvacol = $this->colaborador->update($regcolab->col_id, $regcolab);
                                // echo "Alteração de Colaborador";
                                // var_dump($salvacol);
                            } else {
                                $regcolab->emp_id = $holerite->emp_id;
                                $salvacol = $this->colaborador->insert($regcolab);
                                $col_id = $this->colaborador->getInsertID();
                                // echo "Inclusão de Colaborador";
                                // var_dump($col_id);
                                $holerite->col_id = $col_id;
                            }
                            $regcolab = new Colaborador();
                            // echo 'Holerite';
                            // echo "<pre>";
                            // print_r($holerite);
                            // echo "</pre>";
                            // debug('Colaborador '.$regcolab->col_cpf);
                            $jatemhol = $this->holerite->getHoleriteUnico($holerite->emp_id, $holerite->col_id, $holerite->hol_competencia);
                            $this->holerite->transBegin();
                            if ($jatemhol) {
                                // echo 'Atualiza Holerite ' . $jatemhol[0]['hol_id'] . "<br>";
                                $salvahol = $this->holerite->update($jatemhol[0]['hol_id'], $holerite);
                                // echo $this->holerite->getLastQuery();

                                // var_dump($salvahol);
                                $holerite->hol_id = $jatemhol[0]['hol_id'];
                            } else {
                                // debug('Holerite '.$holerite->col_id);
                                // echo 'Insert Holerite';
                                $salvahol =  $this->holerite->insert($holerite);
                                // var_dump($salvahol);
                                $holerite->hol_id = $this->holerite->getInsertID();
                            }
                            $this->holerite->transCommit();
                            $proximo = '';
                            continue;
                        }
                        if ($coluna == 'B' && is_numeric(trim($valor))) {
                            $holitem = new HoleriteItem();
                            $holitem->hol_id = $holerite->hol_id;
                            $holitem->hoit_cod = $valor;
                            $proximo = 'item1desc';
                            continue;
                        }
                        if ($proximo == 'item1desc') {
                            $holitem->hoit_descricao = $valor;
                            $proximo = 'item1valo';
                            continue;
                        }
                        if ($proximo == 'item1valo') {
                            $holitem->hoit_valor = $valor;
                            $proximo = 'item1vlto';
                            continue;
                        }
                        if ($proximo == 'item1vlto') {
                            $holitem->hoit_valortotal = $valor;
                            $proximo = 'item1tipo';
                            continue;
                        }
                        if ($proximo == 'item1tipo') {
                            $holitem->hoit_tipo = $valor;
                            $jatemite = $this->holeriteitem->getHoleriteItemUnico($holitem->hol_id, $holitem->hoit_cod);
                            if ($jatemite) {
                                $holitem->hoit_id = $jatemite[0]['hoit_id'];
                                $this->holeriteitem->update($holitem->hoit_id, $holitem);
                            } else {
                                $this->holeriteitem->insert($holitem);
                            }
                            // debug($holitem);
                            $proximo = '';
                            continue;
                        }
                        if ($coluna == 'AN' && is_numeric(trim($valor))) {
                            $holitem = new HoleriteItem();
                            $holitem->hol_id = $holerite->hol_id;
                            $holitem->hoit_cod = $valor;
                            $proximo = 'item2desc';
                            continue;
                        }
                        if ($proximo == 'item2desc') {
                            $holitem->hoit_descricao = $valor;
                            $proximo = 'item2valo';
                            continue;
                        }
                        if ($proximo == 'item2valo') {
                            $holitem->hoit_valor = $valor;
                            $proximo = 'item2vlto';
                            continue;
                        }
                        if ($proximo == 'item2vlto') {
                            $holitem->hoit_valortotal = $valor;
                            $proximo = 'item2tipo';
                            continue;
                        }
                        if ($proximo == 'item2tipo') {
                            $holitem->hoit_tipo = $valor;
                            $jatemite = $this->holeriteitem->getHoleriteItemUnico($holitem->hol_id, $holitem->hoit_cod);
                            if ($jatemite) {
                                $holitem->hoit_id = $jatemite[0]['hoit_id'];
                                $this->holeriteitem->update($holitem->hoit_id, $holitem);
                            } else {
                                $this->holeriteitem->insert($holitem);
                            }
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Proventos:') {
                            $proximo = 'proventos';
                            continue;
                        }
                        if ($proximo == 'proventos') {
                            $holerite->hol_proventos = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Descontos:') {
                            $proximo = 'descontos';
                            continue;
                        }
                        if ($proximo == 'descontos') {
                            $holerite->hol_descontos = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Informativa:') {
                            $proximo = 'informativa';
                            continue;
                        }
                        if ($proximo == 'informativa') {
                            $holerite->hol_informativa = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Informativa Dedutora:') {
                            $proximo = 'dedutora';
                            continue;
                        }
                        if ($proximo == 'dedutora') {
                            $holerite->hol_informativa_dedutora = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Líquido:') {
                            $proximo = 'liquido';
                            continue;
                        }
                        if ($proximo == 'liquido') {
                            $holerite->hol_liquido = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Base INSS:') {
                            $proximo = 'baseinss';
                            continue;
                        }
                        if ($proximo == 'baseinss') {
                            $holerite->hol_baseinss = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Excedente INSS:') {
                            $proximo = 'excedeinss';
                            continue;
                        }
                        if ($proximo == 'excedeinss') {
                            $holerite->hol_excedente_inss = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Base FGTS:') {
                            $proximo = 'basefgts';
                            continue;
                        }
                        if ($proximo == 'basefgts') {
                            $holerite->hol_basefgts = $valor;
                            $proximo = '';
                            continue;
                        }
                        if (trim($valor) == 'Base IRRF:') {
                            $proximo = 'baseirrf';
                            continue;
                        }
                        if ($proximo == 'baseirrf') {
                            $holerite->hol_baseirrf = $valor;
                            $proximo = 'obs';
                            $salvarhol = true;
                            continue;
                        }
                        if ($proximo == 'obs') {
                            if (!is_numeric(substr($valor, 0, 3)) && strlen(trim($valor)) > 30) {
                                $holerite->hol_observacao = $valor;
                            }
                            $proximo = '';
                            continue;
                        }
                        if ($salvarhol) {
                            $this->holerite->update($holerite->hol_id, $holerite);
                            $holanter = $holerite;
                            $contador++;
                            $holerite = new Holerite();
                            $holerite->emp_id = $empresa[0]['emp_id'];
                            $holerite->hol_dataemissao = $holanter->hol_dataemissao;
                            $holerite->hol_horaemissao = $holanter->hol_horaemissao;
                            $holerite->hol_calculo     = $holanter->hol_calculo;
                            $holerite->hol_competencia = $holanter->hol_competencia;
                            $salvarhol = false;
                        }
                    }
                }
            }
            // debug('Fim');  
            $ret['erro'] = false;
            $ret['msg'] = 'Importados os Holerites de ' . $contador . ' Colaboradores, \nda Empresa ' . $empresa[0]['emp_apelido'];
            session()->setFlashdata('msg', $ret['msg']);
            $ret['url'] = site_url($this->data['controler']);
        } else {
            $ret['erro'] = true;
            $ret['msg'] = 'Arquivo Inválido';
        }

        // Obtém os dados da planilha
        // $data = $sheet->toArray(); // Converte a planilha em um array

        // // Exibe os dados
        // foreach ($data as $linha) {
        //     echo implode(', ', $linha) . "\n"; // Exibe cada linha
        // }

        //     debug('Fim', true);         
        //     $reader = Factory::createReader($arquivo);
        //     $reader->load();
        //     // debug($reader->, true);

        //     $holerite = new Holerite();
        //     $regcolab = new Colaborador();
        //     $regcargo = new Cargo();
        //     $novcolab = false;
        //     $novcargo = false;
        //     $salvarhol = false;
        //     $resumo = false;
        //     $contador = 0;
        //     $cnpj = '';
        //     foreach ($reader->read() as $row){
        //         // debug($row);                
        //         if(isset($row['A']) && substr(trim($row['A']),0,6) == 'Resumo'){
        //             $resumo = true;
        //         }                
        //         if(isset($row['A']) && trim($row['A']) == 'CNPJ:'){
        //             $resumo = false;
        //             // debug($row['A']);
        //             if($cnpj == ''){
        //                 $cnpj = formatCNPJ(trim($row['Q']));
        //                 // debug('CNPJ: '.$cnpj);
        //                 $empresa = $this->empresa->getEmpresaCNPJ($cnpj);
        //                 // debug($empresa);
        //                 if(count($empresa) < 0){
        //                     // debug('EMPRESA NÃO CADASTRADA');
        //                 } else{
        //                     $holerite->emp_id = $empresa[0]['emp_id'];
        //                     $regcolab->emp_id = $empresa[0]['emp_id']; 
        //                 }
        //                 $cnpj = '';
        //             }
        //         }
        //         if(!$resumo){
        //             if(isset($row['BT']) && trim($row['BT']) == 'Emissão:'){
        //                 $holerite->hol_dataemissao = substr($row['CA'],0,10);
        //             }
        //             if(isset($row['A']) && trim($row['A']) == 'Cálculo:'){
        //                 $holerite->hol_calculo = $row['Q'];
        //             }
        //             if(isset($row['BT']) && trim($row['BT']) == 'Horas:'){
        //                 $holerite->hol_horaemissao = substr($row['CA'],11,8);
        //             }
        //             if(isset($row['A']) && trim($row['A']) == 'Competência:'){
        //                 $compet = substr($row['Q'],0,10);
        //                 $datacompet = DateTime::createFromFormat('Y-m-d', $compet);
        //                 $datacompet->add(new DateInterval('P1D'));
        //                 $holerite->hol_competencia = $datacompet->format('Y-m-d');
        //                 // debug($holerite->hol_competencia);
        //             }
        //             if(isset($row['A']) && trim($row['A']) == 'Empr.:'){
        //                 $regcolab->col_cpf = $row['AZ'];
        //                 $colab = $this->colaborador->getCPF($regcolab->col_cpf);
        //                 // debug(count($colab));
        //                 // debug($colab);
        //                 if(count($colab) > 0){
        //                     $holerite->col_id = $colab[0]['col_id'];
        //                     $regcolab->col_id = $colab[0]['col_id'];
        //                 } else {
        //                     $novcolab = true;
        //                     $regcolab->emp_id = $holerite->emp_id; 
        //                 }
        //                 $regcolab->col_nome = $row['J'];
        //                 $regcolab->col_matricula = $row['F'];
        //             }
        //             if(isset($row['AB']) && trim($row['AB']) == 'Situação:'){
        //                 $holerite->hol_situacao = $row['AG'];
        //                 $regcolab->col_situacao = $row['AG'];
        //             }
        //             if(isset($row['BO']) && trim($row['BO']) == 'Adm:'){
        //                 $admis = substr($row['BZ'],0,10);
        //                 $dataadmis = DateTime::createFromFormat('Y-m-d', $admis);
        //                 $dataadmis->add(new DateInterval('P1D'));
        //                 $regcolab->col_data_admissao = $dataadmis->format('Y-m-d');
        //             }
        //             if(isset($row['A']) && trim($row['A']) == 'Cargo:'){
        //                 $regcargo->cag_nome =  $row['J'];
        //                 $cargo = $this->cargo->getCargoSearch($regcargo->cag_nome);
        //                 // debug($cargo);
        //                 if(count($cargo) > 0){
        //                     $regcolab->cag_id = $cargo[0]['cag_id'];
        //                 } else {
        //                     $novcargo = true;
        //                     $regcargo->cag_cbo = $row['AG'];
        //                 }
        //             }
        //             if(isset($row['A']) && trim($row['A']) == 'Vínculo:'){
        //                 $regcolab->col_vinculo = $row['J'];
        //             }
        //             if(isset($row['BL']) && trim($row['BL']) == 'Horas Mês:'){
        //                 $regcolab->col_cargahoraria = $row['BY'];
        //             }
        //             if(isset($row['BM']) && trim($row['BM']) == 'Salário:'){
        //                 $regcolab->col_salario = $row['BT'];
        //                 // Salário é o último campo antes da relação de proventos e descontos
        //                 // Aqui deve ser feito os cadastros
        //                 if($novcargo){
        //                     $this->cargo->save($regcargo);
        //                     $regcolab->cag_id = $this->cargo->getInsertID();
        //                 }
        //                 // debug($regcolab);
        //                 if($regcolab->col_id){
        //                     // debug($regcolab);
        //                     $salvacol = $this->colaborador->update($regcolab->col_id, $regcolab);
        //                     $holerite->col_id = $regcolab->col_id;
        //                 } else {
        //                     $salvacol = $this->colaborador->insert($regcolab);
        //                     $col_id = $this->colaborador->getInsertID();
        //                     $holerite->col_id = $col_id;
        //                 }
        //                 $jatemhol = $this->holerite->getHoleriteUnico($holerite->emp_id, $holerite->col_id, $holerite->hol_competencia);
        //                 if($jatemhol){
        //                     $this->holerite->update($jatemhol[0]['hol_id'],$holerite);
        //                     $holerite->hol_id = $jatemhol[0]['hol_id'];
        //                 } else {
        //                     $this->holerite->save($holerite);
        //                     $holerite->hol_id = $this->holerite->getInsertID();
        //                 }
        //             }
        //             if(isset($row['B']) && is_numeric(trim($row['B']))){
        //                 $holitem = new HoleriteItem();
        //                 $holitem->hol_id = $holerite->hol_id;
        //                 $holitem->hoit_cod = $row['B'];
        //                 $holitem->hoit_descricao = $row['I'];
        //                 $holitem->hoit_valor = $row['U'];
        //                 $holitem->hoit_valortotal = $row['AC'];
        //                 $holitem->hoit_tipo = $row['AK'];
        //                 $jatemite = $this->holeriteitem->getHoleriteItemUnico($holitem->hol_id, $holitem->hoit_cod);
        //                 if($jatemite){
        //                     $holitem->hoit_id = $jatemite[0]['hoit_id'];
        //                 }
        //                 $this->holeriteitem->save($holitem);
        //             }
        //             if(isset($row['AN']) && is_numeric(trim($row['AN']))){
        //                 $holitem = new HoleriteItem();
        //                 $holitem->hol_id = $holerite->hol_id;
        //                 $holitem->hoit_cod = $row['AN'];
        //                 $holitem->hoit_descricao = $row['AS'];
        //                 $holitem->hoit_valor = $row['BK'];
        //                 $holitem->hoit_valortotal = $row['BV'];
        //                 $holitem->hoit_tipo = $row['CD'];
        //                 $jatemite = $this->holeriteitem->getHoleriteItemUnico($holitem->hol_id, $holitem->hoit_cod);
        //                 if($jatemite){
        //                     $holitem->hoit_id = $jatemite[0]['hoit_id'];
        //                 }
        //                 $this->holeriteitem->save($holitem);
        //             }
        //             if(isset($row['H']) && trim($row['H']) == 'Proventos:'){
        //                 $holerite->hol_proventos = $row['L'];
        //             }
        //             if(isset($row['T']) && trim($row['T']) == 'Descontos:'){
        //                 $holerite->hol_descontos = $row['Y'];
        //             }
        //             if(isset($row['AG']) && trim($row['AG']) == 'Informativa:'){
        //                 $holerite->hol_informativa = $row['AM'];
        //             }
        //             if(isset($row['AX']) && trim($row['AX']) == 'Informativa Dedutora:'){
        //                 $holerite->hol_informativa_dedutora = $row['BG'];
        //             }
        //             if(isset($row['BP']) && trim($row['BP']) == 'Líquido:'){
        //                 $holerite->hol_liquido = $row['BY'];
        //             }
        //             if(isset($row['H']) && trim($row['H']) == 'Base INSS:'){
        //                 $holerite->hol_baseinss = $row['L'];
        //             }
        //             if(isset($row['T']) && trim($row['T']) == 'Excedente INSS:'){
        //                 $holerite->hol_excedente_inss = $row['Y'];
        //             }
        //             if(isset($row['AG']) && trim($row['AG']) == 'Base FGTS:'){
        //                 $holerite->hol_basefgts = $row['AM'];
        //             }
        //             if(isset($row['BP']) && trim($row['BP']) == 'Base IRRF:'){
        //                 $holerite->hol_baseirrf = $row['BY'];
        //                 $salvarhol = true;
        //             }
        //             if(isset($row['A']) && strlen(trim($row['A'])) > 30){
        //                 $holerite->hol_observacao = $row['A'];
        //             }
        //             if($salvarhol){
        //                 $this->holerite->save($holerite);
        //                 $holanter = $holerite;
        //                 // debug($regcolab->col_nome);
        //                 $contador++;
        //                 $holerite = new Holerite();
        //                 $holerite->emp_id = $empresa[0]['emp_id'];
        //                 $holerite->hol_dataemissao = $holanter->hol_dataemissao;
        //                 $holerite->hol_horaemissao = $holanter->hol_horaemissao;
        //                 $holerite->hol_calculo     = $holanter->hol_calculo;
        //                 $holerite->hol_competencia = $holanter->hol_competencia;
        //                 $novcolab = false;
        //                 $novcargo = false;
        //                 $salvarhol = false;                            
        //             }
        //         }
        //     }
        //     $ret['erro'] = false;
        //     $ret['msg'] = 'Importados os Holerites de '.$contador.' Colaboradores, \nda Empresa '.$empresa[0]['emp_apelido'];
        //     session()->setFlashdata('msg', $ret['msg']);
        //     $ret['url'] = site_url($this->data['controler']);
        // } else {
        //     $ret['erro'] = true;
        //     $ret['msg'] = 'Arquivo Inválido';
        // }
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
        $arq->pasta                 = 'assets/uploads/rh/holerites';
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
        // $bot->funcChan = 'monitora_importacao()';
        $bot->dispForm  = 'col-2';
        $this->botao = $bot->crBotao();
    }
}
