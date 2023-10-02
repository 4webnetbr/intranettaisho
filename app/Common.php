<?php
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

use App\Libraries\Campos;
use App\Models\Config\ConfigClasseListaModel;
use App\Models\Config\ConfigClasseModel;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\EtapaModel;

/**
 * montaMenu
 * @param mixed $perfil_usu
 * @param mixed $ordenacao
 * @return array
 */
function montaMenu($perfil_usu, $tipo_usu, $ordenacao = false)
{
    $menu    = new ConfigMenuModel();
    $retorno = $menu->getMenuCompleto($perfil_usu, $tipo_usu);
    $ret_menu = [];
    $opc = -1;
    $niv1 = -1;
    $niv2 = -1;
    $niv_atual  = 0;
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
            if (strpbrk($opc_menu['pit_permissao'], 'C')) {
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
                    $ret_menu[$opc] = $opc_menu;
                    $opc++;
                }
            } else {
                if ($retorno[$m + 1]['men_hierarquia'] != '4') {
                    $niv1++;
                }
            }
        }
    }
    if (!$ordenacao) {
        for ($m = count($ret_menu) - 1; $m >= 0; $m--) {
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
    }
    return $ret_menu;
}

function monta_menu($perfil_usu, $ordenacao = false)
{
    $menu    = new ConfigMenuModel();

    $retorno     =  $menu->getRaizMenuPerfil($perfil_usu);
    for ($m = 0; $m < count($retorno); $m++) {
        $opc_menu = $retorno[$m];
        // CASO SEJA UM MENU
        if ($opc_menu['men_hierarquia'] == '2') {
            $retorno[$m]['niv1'] = $menu->getPrimeiroNivel($opc_menu['men_id'], $perfil_usu);
            for ($ms = 0; $ms < count($retorno[$m]['niv1']); $ms++) {
                $seg_niv = $retorno[$m]['niv1'][$ms];
                // CASO SEJA UM SUBMENU
                if ($seg_niv['men_hierarquia'] == '3') {
                    $subsub = $menu->getSegundoNivel($opc_menu['men_id'], $seg_niv['men_id'], $perfil_usu);
                    if (count($subsub) > 0) {
                        $retorno[$m]['niv1'][$ms]['niv2'] = $subsub;
                    }
                }
            }
        }
    }
    if (!$ordenacao) {
        for ($m = count($retorno) - 1; $m >= 0; $m--) {
            $opc_menu = $retorno[$m];
            if ($opc_menu['men_hierarquia'] == '2') {
                if (count($opc_menu['niv1']) == 0 || !isset($opc_menu['niv1'])) {
                    unset($retorno[$m]);
                } else {
                    for ($s = count($opc_menu['niv1']) - 1; $s >= 0; $s--) {
                        $opc_sub = $opc_menu['niv1'][$s];
                        if ($opc_sub['men_hierarquia'] == '3') {
                            if (!array_key_exists('niv2', $opc_sub) || !isset($opc_sub['niv2']) || count($opc_sub['niv2']) == 0)
                            {
                                unset($retorno[$m]['niv1'][$s]);
                            }
                        }
                    }
                }
            }
        }
        for ($m = count($retorno) - 1; $m >= 0; $m--) {
            if (isset($retorno[$m])) {
                $opc_menu = $retorno[$m];
                if ($opc_menu['men_hierarquia'] == '2') {
                    if (count($opc_menu['niv1']) == 0 || !isset($opc_menu['niv1'])) {
                        unset($retorno[$m]);
                    }
                }
            }
        }
        $arr = array_values($retorno);
        $retorno = $arr;
    }
    return $retorno;
}

function montaLista_itens_perfil($perfil = false) {
    $classes = new ConfigClasseModel();

    $lista_classes = $classes->getClassePerfil($perfil);
    return $lista_classes;
}

function monta_menu_etapas($setor_usu) {
    $etapas    = new EtapaModel();

    $retorno     =  $etapas->getEtapaSetor($setor_usu);
    for ($i=0; $i < count($retorno); $i++) { 
        $ret_etapas[$i]['eta_id'] = $retorno[$i]['eta_id'];
        $ret_etapas[$i]['eta_nome'] = $retorno[$i]['eta_nome'];
        $ret_etapas[$i]['eta_icone'] = $retorno[$i]['eta_icone'];
    }
    return $ret_etapas;
}

function monta_card($dados_op) {
    // debug($dados_op,false);
    $acao = '';
    if ($dados_op['pcp_inicio'] == '') {
        $acao = "onclick=\"".str_replace("\"","'",$dados_op['acao'])."\" ";
    }
    $ret_card = "";
    $ret_card .= "<div class='float-start p-2 clearfix' style='width:20%' " . $acao.">";
    $ret_card .= "<div class='card border border-2 border-dark rounded-4 shadow-lg' style='background-color:" . $dados_op['cor'] . "'>";
    $ret_card .= "   <div class='card-header py-1'>";
    $ret_card .= "   <h2 class='card-title text-center '><strong>" . $dados_op['pcp_previsto'] . "</strong></h2>";
    $ret_card .= "   <h5 class='card-title float-start m-0'>OP: <strong>" . $dados_op['op_id'] . "</strong></h5>";
    $ret_card .= "   <h5 class='card-title float-end m-0'>Ped: <strong>" . $dados_op['op_num_pedido'] . "</strong></h5>";
    $ret_card .= "   </div>";
    $ret_card .= "   <div class='col-12 div-img-etapa align-items-center m-auto row' style='background-color:" . $dados_op['cor'] . "' >";
    // $ret_card .= "       <div class='col-10' >";
    $ret_card .= "           <img loading='lazy' src='" . $dados_op['imagem'] . "' class='col-10 card-img-etapa img-responsive m-auto w-auto' alt='...'>";
    $ret_card .= "   </div>";
    $ret_card .= "   <div class='card-header'>";
    $ret_card .= "   <h5 class='card-title p-0 text-center m-0 ' style='height:42px'>";
    $ret_card .= "       ".substr($dados_op['op_nome_prod'],0,60)."";
    $ret_card .= "   </h5>";
    $ret_card .= "   </div>";
    if ($dados_op['conf_borracha'] == '1') {
        $ret_card .= "   <div class='card-title p-2 text-center m-2 position-absolute border border-2 border-white py-2 rounded-4 bg-primary text-white' style='font-size:12px; top: 22.7rem;right: -2rem;rotate: 90deg;'>";
        $ret_card .= "VERIFICAR";
        $ret_card .= "   </div>";
    }
    if ($dados_op['op_urgente'] == '1') {
        $ret_card .= "   <div class='card-title p-2 text-center m-2 position-absolute border border-2 border-white py-2 rounded-4 bg-warning text-white fw-bold' style='top: 5rem;right: 0;rotate: 20deg;'>";
        $ret_card .= "URGENTE";
        $ret_card .= "   </div>";
    }
    if ($dados_op['op_urgente'] == '2') {
        $ret_card .= "   <div class='card-title p-2 text-center m-2 position-absolute border border-2 border-white py-2 rounded-4 bg-danger text-white fw-bold' style='top: 5rem;left: 0;rotate: -20deg;'>";
        $ret_card .= "SEM FALTA";
        $ret_card .= "   </div>";
    }
    if ($dados_op['op_urgente'] == '3') {
        $ret_card .= "   <div class='card-title p-2 text-center m-2 position-absolute border border-2 border-white py-2 rounded-4 bg-info text-white fw-bold' style='top: 5rem;left: 0;rotate: -20deg;'>";
        $ret_card .= "REPOSIÇÃO";
        $ret_card .= "   </div>";
    }
    foreach ($dados_op['campos_op'] as $key => $value) {
        if (rtrim(trim($key)) == 'Cliente') {
            $ret_card .= "<div class='card-header'><h5 class='text-center m-0'><strong>" . $value."</strong></h5></div>";
        }
    }
    $ret_card .= "   <div class='card-body align-items-center'>";
    foreach ($dados_op['campos_op'] as $key => $value) {
        if (rtrim(trim($key)) != 'Cliente' ) {
            $ret_card .= "<div class='col-12'><span><strong>".rtrim(trim($key),',').":</strong></span>  <span><em>" . $value."</em></span></div>";
        }
    }
    $ret_card .= "   </div>";
    $ret_card .= "   <div class='card-footer text-center p-0'>";
    $ret_card .= "   <span class='fs-7 fst-italic text-center'>OP gerada em: " . $dados_op['op_data'] . " por: " . $dados_op['usu_nome'] . "</span><br>";
    if ($dados_op['pcp_inicio'] != '') {
        $ret_card .= "   <span class='fs-7 fst-italic text-center'>OP Iniciada em: " . $dados_op['pcp_inicio'] . " por: " . $dados_op['usu_inicio'] . "</span><br>";
    }
    if ($dados_op['pcp_final'] != '') {
        $ret_card .= "   <span class='fs-7 fst-italic text-center'>OP Finalizada em: " . $dados_op['pcp_final'] . " por: " . $dados_op['usu_final'] . "</span><br>";
    }
    if ($dados_op['pcp_inicio'] != '') {
        $ret_card .= "   <div class='mb-2'>" . $dados_op['acao'] . "</div>";
    }
    $ret_card .= "   </div>";
    $ret_card .= "</div>";
    $ret_card .= "</div>";
    return $ret_card;
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

        // se for a tela de classes
        if ($chave == 'cls_id') {
            $dados[$p]['cls_nome'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " .
            $dat_i['mod_nome'] . "  <i class='fa fa-arrow-right-long'></i>
            <i class='" . $dat_i['cls_icone'] . "'></i> " . $dat_i['cls_nome'];
        }

        // se for a tela de menus
        if ($chave == 'men_id') {
            $dados[$p]['men_modulo'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " . $dat_i['mod_nome'];
            $dados[$p]['men_classe'] = "<i class='" . $dat_i['cls_classe_icone'] . "'></i> " . $dat_i['cls_nome'];
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
                $retor[$fields[$f]] = "<div class='text-center'>" . data_br($retor[$fields[$f]]) . "</div>";
            } else {
                $data = DateTime::createFromFormat('Y-m-d', $retor[$fields[$f]]);
                if ($data && $data->format('Y-m-d') === $retor[$fields[$f]]) {
                    $retor[$fields[$f]] = "<div class='text-center'>" . data_br($retor[$fields[$f]]) . "</div>";
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

function montaColunasLista($data_lis, $chave)
{
    $classeLista    = new ConfigClasseListaModel();

    $lista = $classeLista->getListagem($data_lis['cls_id']);
    $arr_campos = array_column($lista, 'lis_rotulo');
    array_unshift($arr_campos, $chave);
    array_push($arr_campos, "Ação");

    // debug($arr_campos);
    return $arr_campos;
}

function montaListaColunas($data_lis, $chave, $dados, $nome)
{
    $classeLista = new ConfigClasseListaModel();

    $lista = $classeLista->getListagem($data_lis['cls_id']);
    $fields = array_column($lista, 'lis_campo');
    array_unshift($fields, $chave);
    array_push($fields, 'acao');
    // debug($fields);
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
                "<button class='btn btn-outline-danger btn-sm' data-mdb-toggle='tooltip' data-mdb-placement='top' title='Excluir este Registro' onclick='excluir(\"" .
                $url_del .
                "\",\"" .
                $dat_i[$nome] .
                "\")'><i class='far fa-trash-alt'></i></button>";
        }

        // se for a tela de classes
        if ($chave == 'cls_id') {
            $dados[$p]['cls_nome'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " .
            $dat_i['mod_nome'] . "  <i class='fa fa-arrow-right-long'></i>  <i class='" .
            $dat_i['cls_icone'] . "'></i> " . $dat_i['cls_nome'];
        }

        // se for a tela de menus
        if ($chave == 'men_id') {
            $dados[$p]['men_modulo'] = "<i class='" . $dat_i['mod_icone'] . "'></i> " . $dat_i['mod_nome'];
            $dados[$p]['men_classe'] = $dat_i['cls_nome'];
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
        $dados[$p]['acao'] = $edit . ' ' . $exclui;
        $retor = $dados[$p];
        $res = [];
        for ($f = 0; $f < count($fields); $f++) {
            // debug($fields, false);
            // testa se o campo é uma data
            if (strlen($fields[$f]) > 100) {
                $fields[$f] = strip_tags($fields[$f]);
            }

            $data = DateTime::createFromFormat('Y-m-d H:i:s', $retor[$fields[$f]]);
            if ($data && $data->format('Y-m-d H:i:s') === $retor[$fields[$f]]) {
                $retor[$fields[$f]] = "<div class='text-center'>" .
                data_br($retor[$fields[$f]]) . "</div>";
            } else {
                $data = DateTime::createFromFormat('Y-m-d', $retor[$fields[$f]]);
                if ($data && $data->format('Y-m-d') === $retor[$fields[$f]]) {
                    $retor[$fields[$f]] = "<div class='text-center'>" . data_br($retor[$fields[$f]]) .
                    "</div>";
                } else {
                    // testa se o campo é numérico, se for alinha a direita
                    if (floatval($retor[$fields[$f]]) > 0) {
                        $partes = explode('.', $retor[$fields[$f]]);
                        // debug($partes,false);
                        if (isset($partes[1]) && trim($partes[1]) != '') {
                            if (strlen($partes[1]) > 2) {
                                $retor[$fields[$f]] = "<div class='text-end'>" .
                                floatToQuantia($retor[$fields[$f]], $partes[1]) . "</div>";
                            } else {
                                $retor[$fields[$f]] = "<div class='text-end'>" .
                                floatToMoeda($retor[$fields[$f]]) . "</div>";
                            }
                        }
                    }
                }
            }
            array_push($res, $retor[$fields[$f]]);
        }
        // debug($res);
        array_push($result, $res);
    }
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

function monta_filtro($data_lis, $dados_base) {
    $dicionario = new ConfigDicDadosModel();

    $lista = $data_lis['filtros'];
    $arr_lista = explode(',',$lista);
    $tabela = $data_lis['tabela'];
    $arr_campos = [];
    // array_push($arr_campos, $this->dicionario->getCampoChave($tabela)[0]);
    for ($l=0;$l<count($arr_lista);$l++) {
        array_push($arr_campos, $dicionario->getDetalhesCampo($tabela, $arr_lista[$l])[0]);
    }
    $filtros = [];
    for ($c=0;$c<count($arr_campos);$c++) {
        $opcoes = array_column($dados_base,$arr_campos[$c]['COLUMN_NAME'],$arr_campos[$c]['COLUMN_NAME']);
        
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
