<?php

namespace App\Filters;

use App\Models\Config\ConfigClasseModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigPerfilItemModel;
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
                return redirect()->to(site_url('home'));
            }
            $segmentos = $request->getUri()->getSegments();
            $modal = false;
            if ($segmentos[count($segmentos) - 1] == 'modal=true') {
                $modal = true;
            }
            $nomeclasse             = $segmentos[0];
            $metodo                 = 'index';
            if (isset($segmentos[1])) {
                $metodo             = $segmentos[1];
            }
            // d($metodo);
            $perfil_usu             = session()->get('usu_perfil_id');
            $setor_usu              = session()->get('usu_setor_id');
            $tipo_usu               = session()->get('usu_tipo');
            $classe                 = new ConfigClasseModel();
            $perfil_pit             = new ConfigPerfilItemModel();
            $menu                   = new ConfigMenuModel();
            $busca_classe           = $classe->getClasseSearch($nomeclasse);
            if (!$busca_classe) {
                $retorno['title']       = $nomeclasse;
                $retorno['permissao']   = false;
                $retorno['erromsg'] = '<h2>Atenção</h2>A Classe <b>' . $nomeclasse .
                '</b> <span style="color:red">Não foi Encontrada!</span><br>
                Informe o Problema ao Administrador do Sistema!';
            } else {
                $busca_classe           = $busca_classe[0];
                $retorno['modal']       = $modal;
                $retorno['cls_id']      = $busca_classe['cls_id'];
                $retorno['icone']       = $busca_classe['cls_classe_icone'];
                $retorno['title']       = $busca_classe['cls_nome'];
                $retorno['controler']   = $busca_classe['cls_controler'];
                $retorno['tabela']      = $busca_classe['cls_tabela'];
                $retorno['listagem']      = $busca_classe['cls_lista'];
                // $retorno['filtros']      = $busca_classe['cls_filtros'];
                $retorno['metodo']      = $metodo;
                $retorno['regras_gerais']    = $busca_classe['cls_regras_gerais'];
                $retorno['regras_cadastro']    = $busca_classe['cls_regras_cadastro'];
                $retorno['bt_add']      = $busca_classe['cls_texto_botao'];
                $retorno['perfil_usu']  = $perfil_usu;
                $retorno['it_menu']     =  montaMenu($perfil_usu, $tipo_usu);
                // debug($retorno['etapas']);
                if ($busca_classe['cls_id']) { 
                    $retorno['permissao']       = $this->buscaPermis($retorno['it_menu'], $busca_classe['cls_id']);
                } else {
                    $retorno['permissao']   = 'CAEX';
                }
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
            $ret['dados_classe'] = $retorno;
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

    public function buscaPermis($menu, $classe) {
        $ret = 'CAEX';
        for ($m = 0; $m < count($menu); $m++) {
            $opcao = $menu[$m];
            foreach ($opcao as $key => $value) {
                if ($key == 'men_classe_id') {
                    if ($value == $classe) {
                        $ret = $key['pit_permissao'];
                    }
                } elseif (gettype($value) == 'array') {
                    for ($k = 0; $k < count($value); $k++) {
                        $val1 = $value[$k];
                        foreach ($val1 as $key1 => $value1) {
                            if ($key1 == 'men_classe_id') {
                                if ($value1 == $classe) {
                                    $ret = $val1['pit_permissao'];
                                }
                            } elseif (gettype($value1) == 'array') {
                                for ($k2 = 0; $k2 < count($value1); $k2++) {
                                    $val2 = $value1[$k2];
                                    foreach ($val2 as $key2 => $value2) {
                                        if ($key2 == 'men_classe_id') {
                                            if ($value2 == $classe) {
                                                $ret = $val2['pit_permissao'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }


    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}