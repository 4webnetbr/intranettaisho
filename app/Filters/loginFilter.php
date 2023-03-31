<?php

namespace App\Filters;

use App\Models\Setup\SetupClasseModel;
use App\Models\Setup\SetupMenuModel;
use App\Models\Setup\SetupPerfilItemModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class loginFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $sessao = session();
        if($sessao->logged_in === true){
            $request = Services::request();
		    $path    = $request->getPath();
            if($path == '/'){
                return redirect()->to(site_url('home'));
            }
            $segmentos = $request->getUri()->getSegments();
            $nomeclasse             = $segmentos[0];
            $metodo                 = 'index';
            if (isset($segmentos[1])) {
                $metodo             = $segmentos[1];
            }
            // d($metodo);
            $perfil_usu             = session()->get('usu_perfil_id');
            $tipo_usu               = session()->get('usu_tipo');
            $classe                 = new SetupClasseModel();
            $perfil_pit             = new SetupPerfilItemModel();
            $menu                   = new SetupMenuModel();
            $busca_classe           = $classe->getClasseSearch($nomeclasse);
            if(!$busca_classe){
                $retorno['title']       = $nomeclasse;
                $retorno['permissao']   = false;
                $retorno['erromsg'] = '<h2>Atenção</h2>A Classe <b>'.$nomeclasse.'</b> <span style="color:red">Não foi Encontrada!</span><br>Informe o Problema ao Administrador do Sistema!';
            } else {
                $busca_classe           = $busca_classe[0];
                $retorno['icone']       = $busca_classe['clas_icone'];
                $retorno['title']       = $busca_classe['clas_titulo'];
                $retorno['controler']   = $busca_classe['clas_controler'];
                $retorno['metodo']      = $metodo;
                $retorno['regras_gerais']    = $busca_classe['clas_regras_gerais'];
                $retorno['regras_cadastro']    = $busca_classe['clas_regras_cadastro'];
                $retorno['bt_add']      = $busca_classe['clas_texto_botao']; 
                $retorno['perfil_usu']  = $perfil_usu; 
                // $retorno['it_menu']     =  $menu->getMenuPerfil($perfil_usu, $tipo_usu);
                $retorno['it_menu']     =  monta_menu($perfil_usu, $tipo_usu);
                // d($retorno['it_menu']);
                if ($busca_classe['clas_id']) { 
                    $busca_permissoes       = $perfil_pit->getItemPerfilClasse($perfil_usu, $busca_classe['clas_id']);
                    // d($busca_permissoes);
                    if (sizeof($busca_permissoes) == 0) {
                        $retorno['permissao']   = '';
                    } else {
                        $retorno['permissao']   = $busca_permissoes[0]['pit_permissao'];
                    }
                } else {
                    $retorno['permissao']   = 'CAEX';
                }   
                // $response = service('response');
                // d($retorno);
                $retorno['erromsg'] = '';
                if($metodo == '' || $metodo == 'index'){
                    if ($retorno['permissao'] == '') {
                        $retorno['erromsg'] = '<h2>Sem autorização para acessar a lista de <br>'.$retorno['title'].'</h2><br>Solicite acesso ao Administrador do Sistema';
                    }
                } else if($metodo == 'add'){
                    if (!strpbrk($retorno['permissao'], 'A')) {
                        $retorno['erromsg'] = '<h2>Sem autorização para Adicionar <br>'.$retorno['title'].'</h2><br>Solicite acesso ao Administrador do Sistema';
                    }
                } else if($metodo == 'edit'){
                    if (!strpbrk($retorno['permissao'], 'E')) {
                        $retorno['erromsg'] = '<h2>Sem autorização para Editar <br>'.$retorno['title'].'</h2><br>Solicite acesso ao Administrador do Sistema';
                    }
                }
            }
            $ret['dados_classe'] = $retorno;
            $sessao->setFlashdata($ret);
            if(trim($retorno['erromsg']) != ''){
                echo view('vw_semacesso', $retorno);
                exit;
            }
        } else { // se não está logado
            return redirect()->to(site_url('login'));
        }
        // Do something here
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}