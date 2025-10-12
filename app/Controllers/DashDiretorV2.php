<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\IntegraOpinae;
use App\Libraries\MyCampo;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\GerenteV2Model;
use App\Services\DashDiretorService;

class DashDiretorV2 extends BaseController
{
    public    $data;
    public    $permissao;
    public    $empresa;
    public    $deliv;
    public    $gerente;
    public    $apis;
    public    $dash_periodo;
    public    $dash_empresa;
    public    $dash_indicadores;
    protected $service;

    /**
     * Substitui o __construct(): 
     * CodeIgniter 4 usa initController() em vez de chamar o construtor diretamente.
     */
    public function __construct()
    {
        // ler dados de sessão e permissão
        $this->data      = session()->getFlashdata('dados_tela');
        $this->permissao = $this->data['permissao'] ?? null;

        // instâncias dos models originais
        $this->empresa  = new ConfigEmpresaModel();
        $this->deliv    = new DelivModel();
        $this->apis     = new ConfigApiModel();
        $this->gerente  = new GerenteV2Model();
        $this->service  = new DashDiretorService();
        // service de negócio

        // bloqueio de acesso em caso de erro
        if (! empty($this->data['erromsg'])) {
            $this->__erro();
            // encerra a requisição para não continuar renderizando
            exit;
        }
    }

    public function index()
    {
        $integ_nps = new IntegraOpinae();
        $integ_nps->integrar();

        $this->def_campos();
        $campos[0] = $this->dash_periodo;
        $campos[1] = $this->dash_empresa;
        $campos[2] = $this->dash_indicadores;

        $this->data['campos']         = $campos;
        return view('vw_dashdiretorv2', $this->data);
    }

    public function def_campos()
    { 
        $periodo =  new MyCampo();
        $periodo->nome        = 'periodo';
        $periodo->id          = 'periodo';
        $periodo->label       = 'Informe o Período';
        $periodo->valor       = '';
        $periodo->size        = 30;
        $periodo->funcChan    = 'carrega_graficos_diretor()';
        $periodo->dispForm    = '3col';
        $this->dash_periodo    = $periodo->crDaterange();

        $empresas = explode(',', session()->get('usu_empresa'));
        // debug($empresas);
        $dados_emp = $this->empresa->getEmpresa($empresas);
        $empres = array_column($dados_emp, 'emp_apelido', 'emp_id');

        $emp                        = new MyCampo();
        // $emp->valor                 = $empresas;
        $emp->nome                  = 'empresa';
        $emp->id                    = 'empresa';
        $emp->label                 = 'Empresa(s)';
        $emp->selecionado           = $empresas;
        $emp->opcoes                = $empres;
        $emp->funcChan              = 'carrega_graficos_diretor()';
        $emp->dispForm              = '3col';
        $emp->largura               = 40;
        if (count($empresas) == 1) {
            $emp->leitura           = true;
        }
        $this->dash_empresa         = $emp->crMultiple();

        $indica['']        = 'Escolha um Indicador';
        $indica['fat_dia'] = 'Faturamento Diário';
        $indica['fat_sem'] = 'Faturamento Semanal';
        $indica['fat_mes'] = 'Faturamento Mensal';
        $indica['nps_dia'] = 'NPS Diário';
        $indica['nps_sem'] = 'NPS Semanal';
        $indica['nps_mes'] = 'NPS Mensal';
        $indica['atrasos_deliv'] = '% de atrasos com mais de 60 minutos';
        $ind                        = new MyCampo();
        $ind->nome                  = 'indicadores';
        $ind->id                    = 'indicadores';
        $ind->label                 = 'Indicadores';
        $ind->selecionado           = '';
        $ind->opcoes                = $indica;
        $ind->funcChan              = 'carrega_graficos_diretor()';
        $ind->dispForm              = '3col';
        $ind->largura               = 40;
        $this->dash_indicadores         = $ind->crSelect();
    }

    public function secao()
    {
        $post = $this->request->getPost();
        // armazena filtros em sessão para uso posterior
        $this->service->buscarSecao($post);
        echo json_encode(['status' => 'ok']);
    }

    public function busca_dados()
    {
        $post = $this->request->getPost();
        $ret  = $this->service->buscarDados(
            $post['busca'],
            dataBrToDb($post['inicio']),
            dataBrToDb($post['fim']),
            $post['empresa'],
            $post['tipo']
        );
        echo json_encode($ret);
    }

    public function __erro()
    {
        echo view('vw_semacesso', $this->data);
    }

    /**
     * Abre o gráfico em tela cheia.
     */
    public function graficoWindow()
    {
        // lê GET params
        $busca   = $this->request->getGet('busca');
        $tipo    = $this->request->getGet('tipo');
        $inicio  = $this->request->getGet('inicio');
        $fim     = $this->request->getGet('fim');
        $empRaw  = $this->request->getGet('empresa');

        // decodifica empresa (JSON array ou single)
        $empresa = json_decode($empRaw, true) ?? $empRaw;

        // busca dados via service
        $ret = $this->service->buscarDados($busca, $inicio, $fim, $empresa, $tipo);
        // debug($ret);
        // envia para a view 'vw_grafico_full'
        echo view('vw_grafico_full', [
            'dados'   => $ret['dados'],
            'cores'   => $ret['cores'],
            'tipo'    => $tipo,
            'titulo'  => '',        // você pode passar um título opcional aqui
            'labels'  => array_column($ret['dados'], array_key_first($ret['dados'][0])),
        ]);
    }
}
