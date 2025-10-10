<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Config\ConfigDicDadosModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigModuloModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Estoqu\EstoquContagemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Rechum\RechumCargoModel;
use App\Models\Rechum\RechumColaboradorModel;
use App\Models\Rechum\RechumHoleriteModel;
use App\Models\Rechum\RechumSetorModel;
use App\Models\Rechum\RechumTurnoModel;

class Buscas extends BaseController
{
    public $data = [];
    public $menu;
    public $modulo;
    public $tela;
    public $usuario;
    public $admDados;
    public $empresa;
    public $produto;
    public $deposito;
    public $fornecedor;
    public $marca;
    public $contagem;
    public $turno;
    public $setor;
    public $colaborador;
    public $holerite;
    public $cargo;

    public function __construct()
    {
        $this->menu                 = new ConfigMenuModel();
        $this->modulo                 = new ConfigModuloModel();
        $this->tela                 = new ConfigTelaModel();
        $this->usuario              = new ConfigUsuarioModel();
        $this->admDados             = new ConfigDicDadosModel();
        $this->empresa              = new ConfigEmpresaModel();
        $this->produto              = new EstoquProdutoModel();
        $this->deposito              = new EstoquDepositoModel();
        $this->fornecedor           = new EstoquFornecedorModel();
        $this->marca                = new EstoquMarcaModel();
        $this->contagem             = new EstoquContagemModel();
        $this->cargo                = new RechumCargoModel();
        $this->setor                = new RechumSetorModel();
        $this->colaborador          = new RechumColaboradorModel();
        $this->holerite             = new RechumHoleriteModel();
        helper('funcoes_helper');
    }

    public function busca_hierarquia()
    {

        $ret    = [];
        if ($_REQUEST['campo']) {
            $data = $_REQUEST;
            $termo              = $data['campo'][0]['id_dep'];
            if ($termo == 1) {
                $hierarquia[2] = 'Pai';
                $hierarquia[3] = 'Filho';
            } else {
                $hierarquia[1] = 'Órfão';
                $hierarquia[3] = 'Filho';
                $hierarquia[4] = 'Neto';
            }
        }
        echo json_encode($hierarquia);
    }
    public function busca_menu_pai()
    {
        $menus = $this->menu->getMenuPai();
        $menu_pai = array_column($menus, 'men_etiqueta', 'men_id');
        echo json_encode($menu_pai);
    }

    public function busca_submenu()
    {
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $submenus = $this->menu->getSubMenu($termo);
            if (sizeof($submenus) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'SubMenu não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($submenus); $c++) {
                    $ret[$c]['id']      = $submenus[$c]['men_id'];
                    $ret[$c]['text']    = $submenus[$c]['men_etiqueta'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_modulo()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $modulos            = $this->modulo->getModulosSearch($termo);
            if (sizeof($modulos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Módulo não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($modulos); $c++) {
                    $ret[$c]['id']      = $modulos[$c]['mod_id'];
                    $ret[$c]['text']    = $modulos[$c]['mod_nome'];
                    $ret[$c]['icone']    = $modulos[$c]['mod_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_modulo_id()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $modulos            = $this->modulo->getModulo($termo);
            if (sizeof($modulos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Módulo não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($modulos); $c++) {
                    $ret[$c]['id']      = $modulos[$c]['mod_id'];
                    $ret[$c]['text']    = $modulos[$c]['mod_nome'];
                    $ret[$c]['icone']    = $modulos[$c]['mod_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_menu()
    {
        $data = $_REQUEST;
        $tipo = $data['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);
        // echo $this->db->last_query();
        if (count($menus) > 0) {
            $menu = array_column($menus, 'men_nome', 'men_id');
            $menu_ret = json_encode($menu);
            echo $menu_ret;
        }
        exit;
    }

    public function busca_tela_modulo()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $telas = $this->tela->getTelaModulo($termo);
            if (sizeof($telas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($telas); $c++) {
                    $ret[$c]['id']      = $telas[$c]['tel_id'];
                    $ret[$c]['text']    = $telas[$c]['tel_nome'];
                    $ret[$c]['icone']    = $telas[$c]['tel_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_tela()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $telas = $this->tela->getTelaSearch($termo);
            if (sizeof($telas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($telas); $c++) {
                    $ret[$c]['id']      = $telas[$c]['tel_id'];
                    $ret[$c]['text']    = $telas[$c]['tel_nome'];
                    $ret[$c]['icone']    = $telas[$c]['tel_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_tela_id()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $class = $this->tela->getTelaId($termo);
            if (sizeof($class) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($class); $c++) {
                    $ret[$c]['id']      = $class[$c]['tel_id'];
                    $ret[$c]['text']    = $class[$c]['tel_nome'];
                    $ret[$c]['icone']    = $class[$c]['tel_icone'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_menupai()
    {
        $data = $_REQUEST;
        $tipo = $data['campo'][0]['id_dep'];
        $menus = $this->menu->getMenuModulo($tipo);
        // echo $this->db->last_query();
        if (COUNT($MENUS) > 0) {
            $MENU = ARRAY_COLUMN($MENUS, 'MEN_NOME', 'MEN_ID');
            $MENU_RET = JSON_ENCODE($MENU);
            echo $MENU_RET;
        }
        exit;
    }

    public function busca_tabela()
    {
        $ret    = [];
        // debug($_REQUEST,false);
        if ($_REQUEST['busca']) {
            $termo            = $_REQUEST['busca'];
            $tabelas         = $this->admDados->getTabelaSearch($termo);
            if (sizeof($tabelas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Tabela não encontrada...';
            } else {
                for ($c = 0; $c < sizeof($tabelas); $c++) {
                    $ret[$c]['id'] = $tabelas[$c]['table_name'];
                    $ret[$c]['text'] = $tabelas[$c]['table_name'];
                }
            }
        }
        echo json_encode($ret);
    }

    public function busca_produto()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $produtos            = $this->produto->getProdutoSearch($termo);
            if (sizeof($produtos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Produto não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($produtos); $c++) {
                    $ret[$c]['id']      = $produtos[$c]['pro_id'];
                    $ret[$c]['text']    = $produtos[$c]['pro_nome'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_deposito_empresa()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $depositos            = $this->deposito->getDeposito(false, $termo);
            if (sizeof($depositos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Depósito não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($depositos); $c++) {
                    $ret[$c]['id']      = $depositos[$c]['dep_id'];
                    $ret[$c]['text']    = $depositos[$c]['dep_nome'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function busca_contagem_deposito()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $contagem            = $this->contagem->getContagemDeposito($termo);
            if (sizeof($contagem) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Contagem não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($contagem); $c++) {
                    $log = buscaLog('est_contagem', $contagem[$c]['cta_id']);
                    // debug($log);

                    $ret[$c]['id']      = $contagem[$c]['cta_id'];
                    $ret[$c]['text']    = $contagem[$c]['cta_id'] . ' - ' . dataDbToBr($contagem[$c]['cta_data']) . ' - ' . $log['usua_alterou'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function cnpjcpfcadastrado()
    {
        $retorno = [];
        $data = $_REQUEST;
        $cnpjcpf = $data['cpfcnpf'];
        $relacao = isset($data['relacao']) ? $data['relacao'] : 10;
        $temfornec = $this->fornecedor->getFornecedorCNPJ($cnpjcpf);
        if ($temfornec) {
            $retorno['tem']   = '1';
            $retorno['id']    = $temfornec[0]['for_id'];
            $retorno['fornecedor']    = $temfornec[0];
        } else {
            $retorno['tem']   = '0';
        }
        echo json_encode($retorno);
        exit;
    }

    public function cpfcolabcadastrado()
    {
        $retorno = [];
        $data = $_REQUEST;
        $cnpjcpf = $data['cpfcnpf'];

        $temcolabo = $this->colaborador->getCPF($cnpjcpf);
        if ($temcolabo) {
            $retorno['tem']   = '1';
            $retorno['id']    = $temcolabo[0]['col_id'];
        } else {
            $retorno['tem']   = '0';
        }
        echo json_encode($retorno);
        exit;
    }

    public function buscaprodutomarca()
    {
        $ret    = [];
        if ($_REQUEST['marca']) {
            $termo              = $_REQUEST['marca'];
            $produtos            = $this->marca->getMarcaCod($termo);
            if (sizeof($produtos) <= 0) {
                $ret['id'] = '-1';
            } else {
                $qtia = formataQuantia(isset($produtos[0]['mar_conversao']) ? $produtos[0]['mar_conversao'] : 0);
                $ret['id']      = $produtos[0]['pro_id'];
                $ret['produto']      = $produtos[0]['pro_nome'];
                $ret['marca']        = $produtos[0]['mar_nome'] . ' - ' . $produtos[0]['mar_apresenta'];
                $ret['conversao']    = $qtia['qtiv'];
                $ret['und_marca']    = $produtos[0]['und_marca'];
                $ret['und_produ']    = $produtos[0]['und_prod'];
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscasaldoproduto()
    {
        $ret    = [];
        if ($_REQUEST['produto']) {
            $prod              = $_REQUEST['produto'];
            $depo              = $_REQUEST['deposito'];
            $produtos            = $this->produto->getSaldos($depo, $prod);
            if (sizeof($produtos) <= 0) {
                $ret['id'] = '-1';
            } else {
                $qtia = formataQuantia(isset($produtos[0]['saldo']) ? $produtos[0]['saldo'] : 0);
                $ret['saldo']    = $qtia['qtiv'];
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaproduto()
    {
        $ret    = [];
        if ($_REQUEST['id']) {
            $termo              = $_REQUEST['id'];
            $produtos            = $this->produto->getProduto($termo);
            if (sizeof($produtos) <= 0) {
                $ret['id'] = '-1';
            } else {
                $ret['id']      = $produtos[0]['pro_id'];
                $ret['produto']    = $produtos[0]['pro_nome'];
                $ret['und_produ']    = $produtos[0]['und_id'];
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaFornecedor()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $forneceds          = $this->fornecedor->getFornecedorSearch($termo);
            if (sizeof($forneceds) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Fornecedor não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($forneceds); $c++) {
                    $ret[$c]['id']      = $forneceds[$c]['for_id'];
                    $ret[$c]['text']    = $forneceds[$c]['for_razao'] . ' - ' . $forneceds[$c]['for_fantasia'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaDadosFornecedor()
    {
        $data = $_REQUEST;
        $forn = $data['campo'][0]['valor'];
        $forneced = [];
        $forneced = $this->fornecedor->getFornecedor($forn);
        if (count($forneced) > 0) {
            $forneced = $forneced[0];
        }
        echo json_encode($forneced);
        exit;
    }

    public function buscaTurno()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $turnos            = $this->turno->getTurnoEmp($termo);
            if (sizeof($turnos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Turno não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($turnos); $c++) {
                    $ret[$c]['id']      = $turnos[$c]['tur_id'];
                    $ret[$c]['text']    = $turnos[$c]['tur_nome'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaSetor()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $setors            = $this->setor->getSetor();
            if (sizeof($setors) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Setor não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($setors); $c++) {
                    $ret[$c]['id']      = $setors[$c]['set_id'];
                    $ret[$c]['text']    = $setors[$c]['set_nome'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaCargos()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $cargos            = $this->cargo->getCargo(false, $termo);
            if (sizeof($cargos) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Cargo não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($cargos); $c++) {
                    $ret[$c]['id']      = $cargos[$c]['cag_id'];
                    $ret[$c]['text']    = $cargos[$c]['cag_nome'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function verificaSessao()
    {
        $ret['sessao'] = true;
        $sessao = session();
        $ret['dados'] =  (array)$sessao;
        if ($sessao->logged_in != true) {
            $ret['sessao'] = false;
            $ret['url'] = site_url('login');
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaCompetencia()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $competencias            = $this->holerite->getCompetencia($termo);
            if (sizeof($competencias) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Sem dados...';
            } else {
                for ($c = 0; $c < sizeof($competencias); $c++) {
                    $ret[$c]['id']      = $competencias[$c]['competencia'];
                    $ret[$c]['text']    = $competencias[$c]['competencia'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaColaborador()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $colaboradores            = $this->colaborador->getColaborador(false, $termo);
            if (sizeof($colaboradores) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Sem dados...';
            } else {
                for ($c = 0; $c < sizeof($colaboradores); $c++) {
                    $ret[$c]['id']      = $colaboradores[$c]['col_id'];
                    $ret[$c]['text']    = $colaboradores[$c]['col_nome'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }

    public function buscaCodbar()
    {
        $codigo = $this->request->getPost('codigo');

        $retorno = buscarProdutoMultiFonte($codigo);

        echo json_encode($retorno);
    }

    public function gravasessao()
    {
        if ($_REQUEST['msg']) {
            $msg            = $_REQUEST['msg'];
            session()->setFlashdata('msg', $msg);
            $ret['erro'] = false;
        } else {
            $ret['erro'] = true;
        }
        echo json_encode($ret);
    }

    public function verSessao()
    {
        $sessao = session();
        $ret['sessao'] = $sessao->logged_in;
        echo json_encode($ret);
    }

    public function buscaEmpRegistro()
    {
        $ret    = [];
        if ($_REQUEST['busca']) {
            $termo              = $_REQUEST['busca'];
            $empresas            = $this->colaborador->getEmpresaRegistro($termo);
            
            if (sizeof($empresas) <= 0) {
                $ret[0]['id'] = '-1';
                $ret[0]['text'] = 'Empresa não encontrado...';
            } else {
                for ($c = 0; $c < sizeof($empresas); $c++) {
                    $ret[$c]['id']      = $empresas[$c]['emp_id_registro'];
                    $ret[$c]['text']    = $empresas[$c]['emp_registro'];
                }
            }
        }
        echo json_encode($ret);
        exit;
    }
}
