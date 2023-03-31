<?php

use App\Models\Setup\SetupClasseModel;
use App\Models\Setup\SetupMenuModel;

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

function monta_menu($perfil_usu, $tipo_usu, $ordenacao = false){
    $menu    = new SetupMenuModel();

    $retorno     =  $menu->getRaizMenuPerfil($perfil_usu, $tipo_usu);
    for($m=0;$m<count($retorno);$m++){
        $opc_menu = $retorno[$m];
        // CASO SEJA UM MENU
        if($opc_menu['men_hierarquia'] == '2'){ 
            $retorno[$m]['niv1'] = $menu->getPrimeiroNivel($opc_menu['men_id'], $perfil_usu);
            for($ms=0;$ms<count($retorno[$m]['niv1']);$ms++){
                $seg_niv = $retorno[$m]['niv1'][$ms];
                // CASO SEJA UM SUBMENU
                if($seg_niv['men_hierarquia'] == '3'){ 
                    $subsub = $menu->getSegundoNivel($opc_menu['men_id'], $seg_niv['men_id'], $perfil_usu);
                    if(count($subsub) > 0){
                        $retorno[$m]['niv1'][$ms]['niv2'] = $subsub;
                    }
                }
            }
        }
    }
    if(!$ordenacao){
        for($m=count($retorno)-1;$m>=0;$m--){
            $opc_menu = $retorno[$m];
            if($opc_menu['men_hierarquia'] == '2'){ 
                if(count($opc_menu['niv1']) == 0 || !isset($opc_menu['niv1'])){
                    unset($retorno[$m]);
                } else {
                    for($s=count($opc_menu['niv1'])-1;$s>=0;$s--){
                        $opc_sub = $opc_menu['niv1'][$s];
                        if($opc_sub['men_hierarquia'] == '3'){ 
                            if(!array_key_exists('niv2', $opc_sub) || !isset($opc_sub['niv2']) || count($opc_sub['niv2'] ) == 0){
                                unset($retorno[$m]['niv1'][$s]);
                            }
                        }
                    }
                }
            }
        }
        for($m=count($retorno)-1;$m>=0;$m--){
            $opc_menu = $retorno[$m];
            if($opc_menu['men_hierarquia'] == '2'){ 
                if(count($opc_menu['niv1']) == 0 || !isset($opc_menu['niv1'])){
                    unset($retorno[$m]);
                }
            }
        }
        $arr = array_values($retorno);
        $retorno = $arr;
    }
    return $retorno;
}

function monta_lista_itens_perfil($perfil = false){
    $classes = new SetupClasseModel();

    $lista_classes = $classes->getClassePerfil($perfil);
    return $lista_classes;
}