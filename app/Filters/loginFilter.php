<?php

namespace App\Filters;

use App\Models\Config\ConfigMensagemModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigPerfilModel;
use App\Models\Config\ConfigTelaModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class loginFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('funcoes_helper');
        $sessao = session();
        if ($sessao->logged_in === true) {
            $request = Services::request();
            $path    = $request->getPath();
            if ($path == '/') {
                $dash_usu             = session()->get('usu_dashboard');
                return redirect()->to(site_url($dash_usu));
            }
            $segmentos = $request->getUri()->getSegments();
            $modal = false;
            if ($segmentos[count($segmentos) - 1] == 'modal=true') {
                $modal = true;
            }
            $nometela             = $segmentos[0];
            $metodo                 = 'index';
            if (isset($segmentos[1])) {
                $metodo             = $segmentos[1];
            }
            // d($metodo);
            // CARREGA MENSAGENS PARA O CACHE
            $msgmodel          = new ConfigMensagemModel();
            $mensagens_cfg['msg_cfg']          = $msgmodel->getMensagem();
            // echo "<script>localStorage.setItem('msg_cfg', '".json_encode($mensagens_cfg)."');</script>";

            session()->set($mensagens_cfg);
            $perfil_usu             = session()->get('usu_perfil_id');
            $setor_usu              = session()->get('usu_setor_id');
            $tipo_usu               = session()->get('usu_tipo');
            $tela                   = new ConfigTelaModel();
            $perfil_pit             = new ConfigPerfilItemModel();
            $menu                   = new ConfigMenuModel();
            $busca_tela           = $tela->getTelaSearch($nometela);
            if (!$busca_tela) {
                $retorno['title']       = $nometela;
                $retorno['permissao']   = false;
                $retorno['erromsg'] = '<h2>Atenção</h2>A Tela <b>' . $nometela .
                    '</b> <span style="color:red">Não foi Encontrada!</span><br>
                Informe o Problema ao Administrador do Sistema!';
            } else {
                $busca_tela           = $busca_tela[0];
                $retorno['modal']       = $modal;
                $retorno['tel_id']      = $busca_tela['tel_id'];
                $retorno['icone']       = $busca_tela['tel_tela_icone'];
                $retorno['title']       = $busca_tela['tel_nome'];
                $retorno['controler']   = $busca_tela['tel_controler'];
                $retorno['model']       = $busca_tela['tel_model'];
                // $retorno['listagem']      = $busca_tela['tel_lista'];
                // $retorno['filtros']      = $busca_tela['tel_filtros'];
                $retorno['metodo']      = $metodo;
                $retorno['regras_gerais']    = $busca_tela['tel_regras_gerais'];
                $retorno['regras_cadastro']    = $busca_tela['tel_regras_cadastro'];
                $retorno['bt_add']      = $busca_tela['tel_texto_botao'];
                $retorno['perfil_usu']  = $perfil_usu;
                $retorno['it_menu']     =  montaMenu($perfil_usu, $tipo_usu);
                // debug($retorno['etapas']);
                // debug($busca_tela['tel_id'],true);
                if ($busca_tela['tel_id']) {
                    // $retorno['permissao']       = $this->buscaPermis($retorno['it_menu'], $busca_tela['tel_id']);
                    $retorno['permissao']       = $this->buscaPermisTela($perfil_pit, $perfil_usu, $busca_tela['tel_id']);
                } else {
                    $retorno['permissao']   = 'CAEX';
                }
                // debug($retorno['permissao'],true);
                $retorno['erromsg'] = '';
                if ($metodo == '' || $metodo == 'index') {
                    if ($retorno['permissao'] == '') {
                        $retorno['erromsg'] = '<h2>Sem autorização para acessar a lista de <br>' .
                            $retorno['title'] .
                            '</h2><br>Solicite acesso ao Administrador do Sistema';
                    }
                } elseif ($metodo == 'add') {
                    if (!strpbrk($retorno['permissao'], 'A')) {
                        $retorno['erromsg'] = '<h2>Sem autorização para Adicionar <br>' .
                            $retorno['title'] .
                            '</h2><br>Solicite acesso ao Administrador do Sistema';
                    }
                } elseif ($metodo == 'edit') {
                    if (!strpbrk($retorno['permissao'], 'E')) {
                        $retorno['erromsg'] = '<h2>Sem autorização para Editar <br>' .
                            $retorno['title'] .
                            '</h2><br>Solicite acesso ao Administrador do Sistema';
                    }
                }
            }
            $ret['dados_tela'] = $retorno;
            // debug($ret, true);
            $sessao->setFlashdata($ret);
            if (trim($retorno['erromsg']) != '') {
                if ($modal) {
                    echo view('vw_semacesso_modal', $retorno);
                } else {
                    echo view('vw_semacesso', $retorno);
                }
                exit;
            }
        } else { // se não está logado
            return redirect()->to(site_url('login'));
        }
        // Do something here
    }

    public function buscaPermisTela($perfil_pit, $perfil, $tela)
    {
        $ret = '';
        $permis = $perfil_pit->getItemPerfilClasse($perfil, $tela);
        if ($permis) {
            $ret = $permis[0]['pit_permissao'];
        }
        return $ret;
    }

    // public function buscaPermis($menu, $tela) {
    //     $ret = '';
    //     // debug($menu, true);
    //     // debug('Tela '.$tela);
    //     for ($m = 0; $m < count($menu); $m++) {
    //         $opcao = $menu[$m];
    //         foreach ($opcao as $key => $value) {
    //             if ($key == 'tel_id') {
    //                 if ($value == $tela && isset($key['pit_permissao'])) {
    //                     $ret = $key['pit_permissao'];
    //                     break;
    //                 }
    //             } elseif (gettype($value) == 'array') {
    //                 for ($k = 0; $k < count($value); $k++) {
    //                     $val1 = $value[$k];
    //                     foreach ($val1 as $key1 => $value1) {
    //                         if ($key1 == 'tel_id') {
    //                             if ($value1 == $tela && isset($val1['pit_permissao'])) {
    //                                 $ret = $val1['pit_permissao'];
    //                                 break;
    //                             }
    //                         } elseif (gettype($value1) == 'array') {
    //                             // debug($value1);
    //                             for ($k2 = 0; $k2 < count($value1); $k2++) {
    //                                 $val2 = $value1[$k2];
    //                                 foreach ($val2 as $key2 => $value2) {
    //                                     if ($key2 == 'tel_id') {
    //                                         if ($value2 == $tela && isset($val2['pit_permissao'])) {
    //                                             $ret = $val2['pit_permissao'];
    //                                             break;
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $ret;
    // }


    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
