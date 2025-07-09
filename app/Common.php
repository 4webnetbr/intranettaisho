<?php
// require('vendor/autoload.php');

use App\Libraries\Campos;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigTelaListaModel;
use App\Models\Config\ConfigTelaModel;
use WebSocket\Client;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter4.github.io/CodeIgniter4/
 */


/**
 * montaMenu
 * @param mixed $perfil_usu
 * @param mixed $tipo_usu
 * @param bool $ordenacao
 * @return array
 */
function montaMenu($perfil_usu, $tipo_usu, $ordenacao = false)
{
    // debug('Ordenação '.$ordenacao);
    // var_dump($ordenacao);
    $menu    = new ConfigMenuModel();
    $retorno = $menu->getMenuCompleto($perfil_usu, $tipo_usu);
    $ret_menu = [];
    $opc = -1;
    $niv1 = -1;
    $niv2 = -1;
    $niv_atual  = 0;
    // debug(count($retorno), true);
    for ($m = 0; $m < count($retorno); $m++) {
        $opc_menu = $retorno[$m];
        if ($opc_menu['men_hierarquia'] == '1') { //Raiz
            $opc++;
            if (strpbrk($opc_menu['pit_permissao'], 'C')) {
                $ret_menu[$opc] = $opc_menu;
            }
            $niv1 = -1;
            $niv2 = -1;
            $niv_atual = 0;
        }
        if ($opc_menu['men_hierarquia'] == '2') { //Menu
            $opc++;
            $niv1 = 0;
            $niv2 = -1;
            $niv_atual = 1;
            $ret_menu[$opc] = $opc_menu;
        }
        if ($opc_menu['men_hierarquia'] == '3') { //Submenu
            $niv2 = 0;
            $niv_atual = 2;
            $ret_menu[$opc]['niv1'][$niv1] = $opc_menu;
        }
        if ($opc_menu['men_hierarquia'] == '4') {
            if (strpbrk($opc_menu['pit_permissao'], 'C') || !$perfil_usu) {
                if ($niv_atual == 2) {
                    if ($retorno[$m]['men_submenu_id'] > 0) {
                        $ret_menu[$opc]['niv1'][$niv1]['niv2'][$niv2] = $opc_menu;
                        $niv2++;
                    } else {
                        $niv1++;
                        $ret_menu[$opc]['niv1'][$niv1] = $opc_menu;
                    }
                    if (
                        $m < count($retorno) - 1
                        && $retorno[$m + 1]['men_hierarquia'] != '4'
                    ) {
                        $niv1++;
                    }
                } elseif ($niv_atual == 1) {
                    $ret_menu[$opc]['niv1'][$niv1] = $opc_menu;
                    $niv1++;
                } else {
                    if ($ret_menu[$opc - 1] != $opc_menu) {
                        $ret_menu[$opc] = $opc_menu;
                        $opc++;
                    }
                }
            } else {
                if (isset($retorno[$m + 1]['men_hierarquia']) && $retorno[$m + 1]['men_hierarquia'] != '4') {
                    $niv1++;
                }
            }
        }
    }
    // debug($ret_menu, true);
    $opc_menu = '';
    for ($m = count($ret_menu) - 1; $m >= 0; $m--) {
        // debug($opc_menu);
        // debug($ret_menu[$m]);
        if ($opc_menu == $ret_menu[$m]) {
            unset($ret_menu[$m]);
        } else {
            $opc_menu = $ret_menu[$m];
            if ($opc_menu['men_hierarquia'] == '2') {
                if (!isset($opc_menu['niv1']) || count($opc_menu['niv1']) == 0) {
                    unset($ret_menu[$m]);
                } else {
                    for ($s = count($opc_menu['niv1']) - 1; $s >= 0; $s--) {
                        $opc_sub = $opc_menu['niv1'][$s];
                        if ($opc_sub['men_hierarquia'] == '3') {
                            if (
                                !array_key_exists('niv2', $opc_sub)
                                || !isset($opc_sub['niv2'])
                                || count($opc_sub['niv2']) == 0
                            ) {
                                unset($ret_menu[$m]['niv1'][$s]);
                            }
                        }
                    }
                }
            }
        }
    }
    for ($m = count($ret_menu) - 1; $m >= 0; $m--) {
        if (isset($ret_menu[$m])) {
            $opc_menu = $ret_menu[$m];
            if ($opc_menu['men_hierarquia'] == '2') {
                if (!isset($opc_menu['niv1']) || count($opc_menu['niv1']) == 0) {
                    unset($ret_menu[$m]);
                }
            }
        }
    }
    $arr = array_values($ret_menu);
    $ret_menu = $arr;
    return $ret_menu;
}


function montaListaTelas()
{
    $telas = new ConfigTelaModel();

    $lista_telas = $telas->getTelaPerfil();
    // debug($lista_telas, true);
    return $lista_telas;
}

function montaListaItensPerfil($perfil, $telas)
{
    $ret = [];
    $itperfil = new ConfigPerfilItemModel();
    foreach ($telas as $key => $value) {
        $permis = $itperfil->getItemPerfilClasse($perfil, $key);
        if (count($permis) == 0) {
            $ret[$key] = '';
        } else {
            $ret[$key] = $permis[0]['pit_permissao'];
        }
    }
    return $ret;
}

/**
 * montaListagem
 * @param array $data_lis
 * @param string $chave
 * @return array
 */
function montaListagem($data_lis, $chave)
{
    $dicionario = new ConfigDicDadosModel();

    $lista = $chave . $data_lis['listagem'];
    $arr_lista = explode(',', $lista);
    $tabela = $data_lis['tabela'];
    $arr_campos = [];
    array_push($arr_campos, $dicionario->getDetalhesCampo($tabela, $arr_lista));
    $cols = [];
    for ($l = 0; $l < count($arr_lista); $l++) {
        for ($c = 0; $c < count($arr_campos[0]); $c++) {
            $campo = $arr_campos[0][$c];
            if ($campo['COLUMN_NAME'] == $arr_lista[$l]) {
                array_push($cols, $arr_campos[0][$c]['COLUMN_COMMENT']);
            }
        }
    }
    array_push($cols, "Ação");

    return $cols;
}

/**
 * montaLista
 * @param array $data_lis
 * @param string $chave
 * @param array $dados
 * @param string $nome
 * @return array
 */
function montaLista($data_lis, $chave, $dados, $nome)
{
    $fields = explode(',', $data_lis['listagem']);
    array_unshift($fields, $chave);
    array_push($fields, 'acao');
    $result = [];
    for ($p = 0; $p < sizeof($dados); $p++) {
        $dat_i = $dados[$p];
        $edit = '';
        $exclui = '';
        if (strpbrk($data_lis['permissao'], 'E')) {
            $edit = anchor(
                $data_lis['controler'] . '/edit/' . $dat_i[$chave],
                '<i class="far fa-edit"></i>',
                [
                    'class' => 'btn btn-outline-warning btn-sm mx-1',
                    'data-mdb-toggle' => 'tooltip',
                    'data-mdb-placement' => 'top',
                    'title' => 'Alterar este Registro',
                ]
            );
        }
        if (strpbrk($data_lis['permissao'], 'X')) {
            $url_del =
                $data_lis['controler'] . '/delete/' . $dat_i[$chave];
            $exclui =
                "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' 
                title='Excluir este Registro' onclick='excluir(\"" .
                $url_del .
                "\",\"" .
                $dat_i[$nome] .
                "\")'><i class='far fa-trash-alt'></i></button>";
        }

        // se for a tela de telas
        if ($chave == 'tel_id') {
            $dados[$p]['tel_nome'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " .
                $dat_i['mod_nome'] . "  <i class='fa fa-arrow-right-long'></i>
            <i class='" . $dat_i['tel_icone'] . "'></i> " . $dat_i['tel_nome'];
        }

        // se for a tela de menus
        if ($chave == 'men_id') {
            $dados[$p]['men_modulo'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " . $dat_i['mod_nome'];
            $dados[$p]['men_tela'] = "<i class='" . $dat_i['tel_tela_icone'] . "'></i> " . $dat_i['tel_nome'];
            $dados[$p]['men_etiqueta'] = "<i class='" . $dat_i['men_icone'] . "'></i> " . $dat_i['men_etiqueta'];
            $dados[$p]['men_caminho'] = "<i class='" . $dat_i['men_icone'] . "'></i> " . $dat_i['men_etiqueta'];
            if ($dat_i['men_menupai_id'] > 0) {
                $dados[$p]['men_caminho'] = "<i class='" . $dat_i['pai_icone'] . "'></i> " .
                    $dat_i['pai_etiqueta'] . " <i class='fas fa-level-up-alt fa-rotate-90'></i> " .
                    $dados[$p]['men_caminho'];
            }
            if ($dat_i['men_submenu_id'] != null && $dat_i['men_submenu_id'] > 0) {
                $dados[$p]['men_caminho'] = "<i class='" . $dat_i['pai_icone'] . "'></i> " .
                    $dat_i['pai_etiqueta'] . " <i class='fas fa-level-up-alt fa-rotate-90'></i> <i class='" .
                    $dat_i['sub_icone'] . "'></i> " . $dat_i['sub_etiqueta'] .
                    " <i class='fa fa-arrow-right-long'></i> <i class='" . $dat_i['men_icone'] . "'></i> " .
                    $dat_i['men_etiqueta'];
            }
        }
        $dados[$p]['acao'] = $edit . ' ' . $exclui;
        $retor = $dados[$p];
        $res = [];
        for ($f = 0; $f < count($fields); $f++) {
            // testa se o campo é uma data
            if (strlen($fields[$f]) > 100) {
                $fields[$f] = strip_tags($fields[$f]);
            }

            $data = DateTime::createFromFormat('Y-m-d H:i:s', $retor[$fields[$f]]);
            if ($data && $data->format('Y-m-d H:i:s') === $retor[$fields[$f]]) {
                $retor[$fields[$f]] = "<div class='text-center'>" . dataDbToBr($retor[$fields[$f]]) . "</div>";
            } else {
                $data = DateTime::createFromFormat('Y-m-d', $retor[$fields[$f]]);
                if ($data && $data->format('Y-m-d') === $retor[$fields[$f]]) {
                    $retor[$fields[$f]] = "<div class='text-center'>" . dataDbToBr($retor[$fields[$f]]) . "</div>";
                } else {
                    // testa se o campo é numérico, se for alinha a direita
                    if (floatval($retor[$fields[$f]]) > 0) {
                        $partes = explode('.', $retor[$fields[$f]]);
                        // debug($partes,false);
                        if (isset($partes[1]) && trim($partes[1]) != '') {
                            if (strlen($partes[1]) > 2) {
                                $retor[$fields[$f]] = "<div class='text-end'>" . $retor[$fields[$f]] . "</div>";
                            } else {
                                $retor[$fields[$f]] = "<div class='text-end'>" .
                                    number_format($retor[$fields[$f]], 2, ',', '.') . "</div>";
                            }
                        }
                    }
                }
            }
            array_push($res, $retor[$fields[$f]]);
        }
        array_push($result, $res);
    }
    return $result;
}

function montaColunasLista($data_lis, $chave, $detalhe = false)
{
    $telaLista    = new ConfigTelaListaModel();

    $lista = $telaLista->getListagem($data_lis['tel_id']);
    $arr_campos = array_column($lista, 'lis_rotulo');
    if ($detalhe) {
        array_unshift($arr_campos, ' ');
    }
    array_unshift($arr_campos, $chave);
    array_push($arr_campos, "Ação");

    // debug($arr_campos);
    return $arr_campos;
}

function montaColunasCampos($data_lis, $chave, $detalhe = false)
{
    $telaLista    = new ConfigTelaListaModel();

    $lista = $telaLista->getListagem($data_lis['tel_id']);
    $arr_campos = array_column($lista, 'lis_campo');
    if ($detalhe) {
        array_unshift($arr_campos, $detalhe);
    }
    array_unshift($arr_campos, $chave);
    // debug($arr_campos);
    return $arr_campos;
}


function montaListaColunas($data_lis, $chave, $dados, $nome, $detalhe = false)
{
    $telaLista = new ConfigTelaListaModel();

    $lista = $telaLista->getListagem($data_lis['tel_id']);
    $fields = array_column($lista, 'lis_campo');
    // Mostra o botão de exclusão por padrão
    $prevbotoes = isset($data_lis['botoes']) ? $data_lis['botoes'] : [];

    $edicao   = isset($data_lis['edicao']) ? $data_lis['edicao'] : true;
    $exclusao = isset($data_lis['exclusao']) ? $data_lis['exclusao'] : true;
    if ($detalhe) {
        array_unshift($fields, 'd');
    }
    array_unshift($fields, $chave);
    array_push($fields, 'acao');
    // debug($fields);
    $result = [];
    for ($p = 0; $p < sizeof($dados); $p++) {
        $dat_i = $dados[$p];
        $temativo = false;
        $ativo = false;
        $inativa = '';
        $podeinativar = true;
        if (isset($dat_i['stt_exclusao'])) {
            if (trim($dat_i['stt_exclusao']) == 'N') {
                $podeinativar = false;
            }
        }
        // VERIFICA SE TEM UM CAMPO DE ATIVO INATIVO
        foreach ($dat_i as $key => $value) {
            if (substr($key, -5) == 'ativo') {
                $temativo = true;
                if ($value == 'A') {
                    $ativo = true;
                }
            }
        }
        $botoes = [];
        $edit = '';
        $exclui = '';
        if (strpos($data_lis['permissao'], 'C')) { // mas tem acesso de consulta
            $edit = anchor(
                $data_lis['controler'] . '/show/' . $dat_i[$chave],
                '<i class="far fa-eye"></i>',
                [
                    'class' => 'btn btn-outline-info btn-sm mx-1',
                    'data-mdb-toggle' => 'tooltip',
                    'data-mdb-placement' => 'top',
                    'title' => 'Detalhes',
                ]
            );
            array_push($botoes, $edit);
        }
        if (strpos($data_lis['permissao'], 'E') && $edicao) {
            if (isset($dat_i['cta_data']) && $dat_i['cta_data'] != date('Y-m-d')) {
            } else if (isset($dat_i['ent_data']) && $dat_i['ent_data'] != date('Y-m-d')) {
            } else if (isset($dat_i['sai_data']) && $dat_i['sai_data'] != date('Y-m-d')) {
            } else {
                $edit = anchor(
                    $data_lis['controler'] . '/edit/' . $dat_i[$chave],
                    '<i class="far fa-edit"></i>',
                    [
                        'class' => 'btn btn-outline-warning btn-sm border-0 mx-0 fs-0',
                        'data-mdb-toggle' => 'tooltip',
                        'data-mdb-placement' => 'top',
                        'title' => 'Alterar',
                    ]
                );
                array_push($botoes, $edit);
            }
        }
        if ($temativo) {
            if ($podeinativar && strpos($data_lis['permissao'], 'X')) {
                $url_ati = $data_lis['controler'] . '/ativinativ/' . $dat_i[$chave] . '/1';
                $url_ina = $data_lis['controler'] . '/ativinativ/' . $dat_i[$chave] . '/0';
                $inativa =
                    "<button class='btn btn-outline-secondary btn-sm border-0 mx-0 fs-0' data-mdb-toggle='tooltip' 
                    data-mdb-placement='top' title='Ativar' onclick='ativInativ(\"" .
                    $url_ati .
                    "\",\"" .
                    $dat_i[$nome] .
                    "\",false)'><i class='fa-solid fa-toggle-off fa-rotate-270'></i></button>";
                if ($ativo) {
                    $inativa =
                        "<button class='btn btn-outline-success btn-sm border-0 mx-0 fs-0' data-mdb-toggle='tooltip' 
                    data-mdb-placement='top' title='Inativar' onclick='ativInativ(\"" .
                        $url_ina .
                        "\",\"" .
                        $dat_i[$nome] .
                        "\",true)'><i class='fa-solid fa-toggle-off fa-rotate-90'></i></button>";
                }
                array_push($botoes, $inativa);
            }
        }
        if (strpos($data_lis['permissao'], 'X')) {
            if ($podeinativar && $exclusao) {
                if (isset($dat_i['cta_data']) && $dat_i['cta_data'] != date('Y-m-d')) {
                    $exclui = '';
                } else if (isset($dat_i['ent_data']) && $dat_i['ent_data'] != date('Y-m-d')) {
                    $exclui = '';
                } else if (isset($dat_i['sai_data']) && $dat_i['sai_data'] != date('Y-m-d')) {
                    $exclui = '';
                } else {
                    $url_del =
                        $data_lis['controler'] . '/delete/' . $dat_i[$chave];
                    $exclui =
                        "<button class='btn btn-outline-danger btn-sm border-0 mx-0 fs-0' data-mdb-toggle='tooltip' 
                            data-mdb-placement='top' title='Excluir' onclick='excluir(\"" .
                        $url_del .
                        "\",\"" .
                        $dat_i[$nome] .
                        "\")'><i class='far fa-trash-alt'></i></button>";
                    array_push($botoes, $exclui);
                }
            }
        }
        if (count($prevbotoes) > 0) {
            for ($b = 0; $b < count($prevbotoes); $b++) {
                $botao = $prevbotoes[$b];
                if (isset($botao['condicao'])) {
                    $condic = $botao['condicao'];
                    $valido = mostra_botao($dat_i[$condic['campo']], $condic);
                } else {
                    $valido = true;
                }
                if ($valido) {
                    $url_bot = $botao['url'];
                    if (str_contains($botao['url'], 'chavesec')) {
                        $url_bot = str_replace('chavesec', $dat_i[$chave_sec], $botao['url']);
                    }
                    if (str_contains($botao['url'], 'chave')) {
                        $url_bot = str_replace('chave', $dat_i[$chave], $url_bot);
                    }
                    $funcao  = str_replace('url', $url_bot, $botao['funcao']);
                    // debug($funcao);
                    if (isset($botao['tipo']) && $botao['tipo'] == 'link') {
                        $bot = "<a class='" . $botao['classe'] .
                            "' data-mdb-toggle='tooltip' data-mdb-placement='top' 
                                title='" . $botao['title'] . "' href='" . $url_bot . "'><i class='" . $botao['icone'] . "'></i></a>";
                    } else {
                        $bot = "<button class='" . $botao['classe'] .
                            "' data-mdb-toggle='tooltip' data-mdb-placement='top' 
                                title='" . $botao['title'] . "' onclick='" . $funcao . "'><i class='" . $botao['icone'] . "'></i></button>";
                    }
                    array_push($botoes, $bot);
                }
            }
        }

        // se for a tela de menus
        if ($chave == 'men_id') {
            $dados[$p]['men_modulo'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " . $dat_i['mod_nome'];
            $dados[$p]['men_tela'] = $dat_i['tel_nome'];
            $dados[$p]['men_etiqueta'] = "<i class='" . $dat_i['men_icone'] . "'></i> " . $dat_i['men_etiqueta'];
            $dados[$p]['men_caminho'] = "<i class='" . $dat_i['men_icone'] . "'></i> " . $dat_i['men_etiqueta'];
            if ($dat_i['men_menupai_id'] > 0) {
                $dados[$p]['men_caminho'] = "<i class='" . $dat_i['pai_icone'] . "'></i> " .
                    $dat_i['pai_etiqueta'] . " <i class='fas fa-level-up-alt fa-rotate-90'></i> " .
                    $dados[$p]['men_caminho'];
            }
            if ($dat_i['men_submenu_id'] != null && $dat_i['men_submenu_id'] > 0) {
                $dados[$p]['men_caminho'] = "<i class='" . $dat_i['pai_icone'] . "'></i> " .
                    $dat_i['pai_etiqueta'] . " <i class='fas fa-level-up-alt fa-rotate-90'></i> <i class='" .
                    $dat_i['sub_icone'] . "'></i> " . $dat_i['sub_etiqueta'] .
                    " <i class='fa fa-arrow-right-long'></i> <i class='" .
                    $dat_i['men_icone'] . "'></i> " . $dat_i['men_etiqueta'];
            }
        }
        $dados[$p]['acao'] = "<div class='col-12 text-nowrap'>";
        for ($bt = 0; $bt < count($botoes); $bt++) {
            $dados[$p]['acao'] .= $botoes[$bt] . ' ';
        }
        $dados[$p]['acao'] = rtrim($dados[$p]['acao']);
        $dados[$p]['acao'] .= "</div>";

        // $dados[$p]['acao'] = $edit . ' ' . $exclui . ' ' . $inativa;
        if (isset($dados[$p]['cor'])) {
            array_push($fields, 'cor');
        }
        $retor = $dados[$p];
        $res = [];
        for ($f = 0; $f < count($fields); $f++) {
            // testa se o campo é uma data
            if (strlen($fields[$f]) > 100) {
                $fields[$f] = strip_tags($fields[$f]);
            }
            // debug($fields[$f], false);
            if ($fields[$f] === 'stt_nome') {
                $retor[$fields[$f]] = fmtEtiquetaCorBst($retor['stt_cor'], $retor[$fields[$f]]);
            }
            // var_dump($retor[$fields[$f]]);
            if (strlen($retor[$fields[$f]]) < 15) {
                $data = DateTime::createFromFormat('Y-m-d H:i:s', $retor[$fields[$f]]);
                if ($data && $data->format('Y-m-d H:i:s') === $retor[$fields[$f]]) {
                    $retor[$fields[$f]] = "<div class='text-center'>" .
                        dataDbToBr($retor[$fields[$f]]) . "</div>";
                } else {
                    if($retor[$fields[$f]] != null){
                        $data = DateTime::createFromFormat('Y-m-d', $retor[$fields[$f]]);
                    }
                    if ($data && $data->format('Y-m-d') === $retor[$fields[$f]]) {
                        $retor[$fields[$f]] = "<div class='text-center'>" . dataDbToBr($retor[$fields[$f]]) .
                            "</div>";
                    } else {
                        // testa se o campo é numérico, se for alinha a direita
                        if (is_numeric($retor[$fields[$f]]) > 0) {
                            $partes = explode('.', $retor[$fields[$f]]);
                            if (isset($partes[1]) && trim($partes[1]) != '') {
                                if (strlen($partes[1]) > 2) {
                                    $retor[$fields[$f]] = "<div class='text-end'>" .
                                        floatToQuantia($retor[$fields[$f]], 3) . "</div>";
                                } else {
                                    $retor[$fields[$f]] = "<div class='text-end'>" .
                                        floatToMoeda($retor[$fields[$f]]) . "</div>";
                                }
                            }
                        }
                    }
                }
            }
            if (str_contains($fields[$f], 'msg_cor')) {
                if (strlen($retor[$fields[$f]]) > 1) {
                    $retor[$fields[$f]] = fmtEtiquetaCorBst($retor[$fields[$f]]);
                }
            }

            if ($fields[$f] === 'cor') {
                $res['cor'] = $retor['cor'];
            }
            array_push($res, $retor[$fields[$f]]);
        }
        // debug($res);
        array_push($result, $res);
    }
    // debug($result);
    return $result;
}

function montaListaEditColunas($colunas, $chave, $dados, $nome, $detalhe = false)
{
    $fields = $colunas;
    // debug($fields);
    $result = [];
    for ($p = 0; $p < sizeof($dados); $p++) {
        $dat_i = $dados[$p];
        $temativo = false;
        $ativo = false;
        $inativa = '';
        $podeinativar = true;
        if (isset($dat_i['stt_exclusao'])) {
            if (trim($dat_i['stt_exclusao']) == 'N') {
                $podeinativar = false;
            }
        }
        // VERIFICA SE TEM UM CAMPO DE ATIVO INATIVO
        foreach ($dat_i as $key => $value) {
            if (substr($key, -5) == 'ativo') {
                $temativo = true;
                if ($value == 'A') {
                    $ativo = true;
                }
            }
        }
        $edit = '';
        $exclui = '';
        $dados[$p]['acao'] = $edit . ' ' . $exclui . ' ' . $inativa;
        $retor = $dados[$p];
        $res = [];
        for ($f = 0; $f < count($fields); $f++) {
            // testa se o campo é uma data
            if (strlen($fields[$f]) > 100) {
                $fields[$f] = strip_tags($fields[$f]);
            }
            // debug($fields[$f], false);
            if ($fields[$f] === 'stt_nome') {
                $retor[$fields[$f]] = fmtEtiquetaCorBst($retor['stt_cor'], $retor[$fields[$f]]);
            }
            // debug($retor[$fields[$f]]);
            if (strlen($retor[$fields[$f]]) < 20) {
                $data = DateTime::createFromFormat('Y-m-d H:i:s', $retor[$fields[$f]]);
                if ($data && $data->format('Y-m-d H:i:s') === $retor[$fields[$f]]) {
                    $retor[$fields[$f]] = "<div class='text-center'>" .
                        dataDbToBr($retor[$fields[$f]]) . "</div>";
                } else {
                    $data = DateTime::createFromFormat('Y-m-d', $retor[$fields[$f]]);
                    if ($data && $data->format('Y-m-d') === $retor[$fields[$f]]) {
                        $retor[$fields[$f]] = "<div class='text-center'>" . dataDbToBr($retor[$fields[$f]]) .
                            "</div>";
                    } else {
                        // testa se o campo é numérico, se for alinha a direita
                        if (is_numeric($retor[$fields[$f]]) > 0) {
                            $partes = explode('.', $retor[$fields[$f]]);
                            if (isset($partes[1]) && trim($partes[1]) != '') {
                                if (strlen($partes[1]) > 2) {
                                    $retor[$fields[$f]] = "<div class='text-end'>" .
                                        floatToQuantia($retor[$fields[$f]], 3) . "</div>";
                                } else {
                                    $retor[$fields[$f]] = "<div class='text-end'>" .
                                        floatToMoeda($retor[$fields[$f]]) . "</div>";
                                }
                            }
                        }
                    }
                }
            }
            if (str_contains($fields[$f], 'msg_cor')) {
                if (strlen($retor[$fields[$f]]) > 1) {
                    $retor[$fields[$f]] = fmtEtiquetaCorBst($retor[$fields[$f]]);
                }
            }

            array_push($res, $retor[$fields[$f]]);
        }
        // debug($res);
        array_push($result, $res);
    }
    // debug($result);
    return $result;
}


/**
 * ordenaSelecionados
 * @param array $lista
 * @param string $selecionados
 * @return array
 */
function ordenaSelecionados($lista, $selecionados)
{
    // debug($lista,false);
    $campos_lis = [];
    $selec = explode(",", $selecionados);
    // debug($selecionados, true);
    foreach ($selec as $skey => $svalue) {
        foreach ($lista as $lkey => $lvalue) {
            // debug($svalue, false);
            // debug($lkey,false);
            if ($svalue == $lkey) {
                $campos_lis[$lkey] = $lvalue;
                unset($lista[$lkey]);
            }
        }
    }
    foreach ($lista as $lkey => $lvalue) {
        $campos_lis[$lkey] = $lvalue;
    }
    // debug($campos_lis,true);
    return $campos_lis;
}

function monta_filtro($data_lis, $dados_base)
{
    $dicionario = new ConfigDicDadosModel();

    $lista = $data_lis['filtros'];
    $arr_lista = explode(',', $lista);
    $tabela = $data_lis['tabela'];
    $arr_campos = [];
    // array_push($arr_campos, $this->dicionario->getCampoChave($tabela)[0]);
    for ($l = 0; $l < count($arr_lista); $l++) {
        array_push($arr_campos, $dicionario->getDetalhesCampo($tabela, $arr_lista[$l])[0]);
    }
    $filtros = [];
    for ($c = 0; $c < count($arr_campos); $c++) {
        $opcoes = array_column($dados_base, $arr_campos[$c]['COLUMN_NAME'], $arr_campos[$c]['COLUMN_NAME']);

        $filt = new Campos();
        $filt->objeto   = 'select';
        $filt->nome     = $arr_campos[$c]['COLUMN_NAME'];
        $filt->id       = $arr_campos[$c]['COLUMN_NAME'];
        $filt->label    = $arr_campos[$c]['COLUMN_COMMENT'];
        $filt->obrigatorio = false;
        $filt->size     = $arr_campos[$c]['COLUMN_SIZE'];
        $filt->opcoes   = $opcoes;
        $filt->tipo_form = 'inline';
        $filtro = $filt->create();

        array_push($filtros, $filtro);
    }
    // debug($filtros);
    return $filtros;
}
