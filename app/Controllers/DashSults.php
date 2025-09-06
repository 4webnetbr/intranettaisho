<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\IntegraOpinae;
use App\Libraries\MyCampo;
use App\Models\CommonModel;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\SultsModel;


class DashSults extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $common;
    public $sults;
    public $apis;


	public function __construct(){
		$this->data  = session()->getFlashdata('dados_tela');
        $this->permissao  = $this->data['permissao'];
        $this->common      = new CommonModel();
        $this->data['scripts'] = 'my_lista,my_consulta';
        if($this->data['erromsg'] != ''){
            $this->__erro();
        }
	}

    function __erro(){
        echo view('vw_semacesso', $this->data);
    }
 
    public function index()
    {
        $integ_sults = new IntegraSults();
        // $integ_sults->integrar();
        
        $this->def_campos();
        $campos[0] = $this->dash_aberto;
        $campos[1] = $this->dash_resolvido;
        $campos[2] = $this->dash_concluido;
        $campos[3] = $this->dash_unidade;
        $campos[4] = $this->dash_departamento;
        $campos[5] = $this->dash_assunto;
        $campos[6] = $this->dash_solicitante;
        $campos[7] = $this->dash_responsavel;
        $campos[8] = $this->dash_situacao;

        $this->data['campos']     	= $campos;  
        return view('vw_dashsults', $this->data);
    }
    
    public function def_campos(){
        $aberto =  new MyCampo();
        $aberto->nome        = 'aberto'; 
        $aberto->id          = 'aberto';
        $aberto->label       = 'Aberto entre';
        $aberto->valor       = '';
        $aberto->size        = 30;
        $aberto->funcChan    = 'carrega_sults(this)';
        $aberto->dispForm    = 'col-12';
        $this->dash_aberto    = $aberto->crDaterange();

        $resolvido =  new MyCampo();
        $resolvido->nome        = 'resolvido'; 
        $resolvido->id          = 'resolvido';
        $resolvido->label       = 'Resolvido entre';
        $resolvido->valor       = '';
        $resolvido->size        = 30;
        $resolvido->funcChan    = 'carrega_sults(this)';
        $resolvido->func    = 'carrega_sults(this)';
        $resolvido->dispForm    = 'col-12';
        $this->dash_resolvido    = $resolvido->crDaterange();

        $concluido =  new MyCampo();
        $concluido->nome        = 'concluido'; 
        $concluido->id          = 'concluido';
        $concluido->label       = 'Concluido entre';
        $concluido->valor       = '';
        $concluido->size        = 30;
        $concluido->funcChan    = 'carrega_sults(this)';
        $concluido->dispForm    = 'col-12';
        $this->dash_concluido    = $concluido->crDaterange();

        $unidades = $this->common->getResult('default', 'ger_sults_unidade','1=1','trim(und_nome) as und_nome,und_id','trim(und_nome),und_id');
        $opcunida = array_column($unidades,'und_nome','und_id');
        asort($opcunida);

        $unidade =  new MyCampo();
        $unidade->nome        = 'unidade'; 
        $unidade->id          = 'unidade';
        $unidade->label       = 'Unidade';
        $unidade->valor       = '';
        $unidade->largura     = 30;
        $unidade->funcFoco    = 'foco_sults(this)';
        $unidade->funcChan    = 'carrega_sults(this)';
        $unidade->dispForm    = 'col-12';
        $unidade->opcoes      = $opcunida;
        $unidade->selecionado = [$unidade->valor];
        $this->dash_unidade    = $unidade->crMultiple();

        $departamentos = $this->common->getResult('default', 'ger_sults_departamento','1=1','CONCAT(dep_tipo," - ",trim(dep_nome)) as dep_nome,dep_id','trim(dep_nome),dep_id');
        $opcdepto = array_column($departamentos,'dep_nome','dep_id');
        asort($opcdepto);

        $departamento =  new MyCampo();
        $departamento->nome        = 'departamento'; 
        $departamento->id          = 'departamento';
        $departamento->label       = 'Departamento';
        $departamento->valor       = '';
        $departamento->largura     = 30;
        $departamento->funcFoco    = 'foco_sults(this)';
        $departamento->funcChan    = 'carrega_sults(this)';
        $departamento->dispForm    = 'col-12';
        $departamento->opcoes      = $opcdepto;
        $departamento->selecionado = [$departamento->valor];
        $this->dash_departamento    = $departamento->crMultiple();

        $asuntos = $this->common->getResult('default', 'ger_sults_assunto','1=1','CONCAT(ass_tipo," - ",trim(ass_nome)) as ass_nome,ass_id','trim(ass_nome),ass_id');
        $opcassu = array_column($asuntos,'ass_nome','ass_id');
        // asort($opcassu);

        $assunto =  new MyCampo();
        $assunto->nome        = 'assunto'; 
        $assunto->id          = 'assunto';
        $assunto->label       = 'Assunto';
        $assunto->valor       = '';
        $assunto->largura     = 30;
        $assunto->funcFoco    = 'foco_sults(this)';
        $assunto->funcChan    = 'carrega_sults(this)';
        $assunto->dispForm    = 'col-12';
        $assunto->opcoes      = $opcassu;
        $assunto->selecionado = [$assunto->valor];
        $this->dash_assunto    = $assunto->crMultiple();

        $solicitantes = $this->common->getResult('default', 'ger_sults_pessoas','1=1','trim(pes_nome) as pes_nome,pes_id','trim(pes_nome),pes_id');
        // debug($solicitantes);
        $opcsolicitan = array_column($solicitantes,'pes_nome','pes_id');
        asort($opcsolicitan);
        reset($opcsolicitan); // move o ponteiro para o primeiro item
        unset($opcsolicitan[key($opcsolicitan)]); // remove a chave atual  exclui fila
        asort($opcsolicitan);

        $solicitante =  new MyCampo();
        $solicitante->nome        = 'solicitante'; 
        $solicitante->id          = 'solicitante';
        $solicitante->label       = 'Solicitante';
        $solicitante->valor       = '';
        $solicitante->largura     = 30;
        $solicitante->funcFoco    = 'foco_sults(this)';
        $solicitante->funcChan    = 'carrega_sults(this)';
        $solicitante->dispForm    = 'col-12';
        $solicitante->opcoes      = $opcsolicitan;
        $solicitante->selecionado = [$solicitante->valor];
        $this->dash_solicitante    = $solicitante->crMultiple();

        $responsavels = $this->common->getResult('default', 'ger_sults_pessoas','1=1','trim(pes_nome) as pes_nome,pes_id','trim(pes_nome),pes_id');
        // debug($responsavels);
        $opcresponsav = array_column($responsavels,'pes_nome','pes_id');
        asort($opcresponsav);

        $responsavel =  new MyCampo();
        $responsavel->nome        = 'responsavel'; 
        $responsavel->id          = 'responsavel';
        $responsavel->label       = 'Responsavel';
        $responsavel->valor       = '';
        $responsavel->largura     = 30;
        $responsavel->funcFoco    = 'foco_sults(this)';
        $responsavel->funcChan    = 'carrega_sults(this)';
        $responsavel->dispForm    = 'col-12';
        $responsavel->opcoes      = $opcresponsav;
        $responsavel->selecionado = [$responsavel->valor];
        $this->dash_responsavel    = $responsavel->crMultiple();

        $situacaos = $this->common->getResult('default', 'ger_sults','1=1','DISTINCT(descsituacao) as sit_nome,situacao','situacao,descsituacao');
        $opcsituac = array_column($situacaos,'sit_nome','situacao');
        // asort($opcsituac);

        $situacao =  new MyCampo();
        $situacao->nome        = 'situacao'; 
        $situacao->id          = 'situacao';
        $situacao->label       = 'Situação';
        $situacao->valor       = '';
        $situacao->largura     = 30;
        $situacao->funcFoco    = 'foco_sults(this)';
        $situacao->funcChan    = 'carrega_sults(this)';
        $situacao->dispForm    = 'col-12';
        $situacao->opcoes      = $opcsituac;
        $situacao->selecionado = [$situacao->valor];
        $this->dash_situacao    = $situacao->crMultiple();

    }

	public function secao(){
		$vars  		= $this->request->getPost();
        debug($vars);
		session()->set($vars);
	}
    
    public function busca_dados()
    {
        $postData = $this->request->getPost();

        // 1) Normaliza arrays do tipo [""] -> []
        foreach ($postData as $k => $v) {
            if (is_array($v) && count($v) === 1 && $v[0] === '') {
                $postData[$k] = [];
            }
        }
        $clausulas   = [];
        $datasInicio = [];
        $datasFim    = [];

        // Utilitário: aceita array ou CSV e devolve lista limpa
        // Aceita array, array com 1 item CSV ["73,65"] ou string "73,65"
        $toList = static function ($v): array {
            if (is_array($v)) {
                if (count($v) === 1 && is_string($v[0]) && strpos($v[0], ',') !== false) {
                    $arr = explode(',', $v[0]);         // trata ["73,65"]
                } else {
                    $arr = $v;                           // ex.: ["73","65"]
                }
            } else {
                $arr = explode(',', (string)$v);         // trata "73,65"
            }

            $arr = array_map('trim', $arr);
            $arr = array_filter($arr, static fn($x) => $x !== '' && $x !== null);
            return array_values(array_unique($arr));
        };

        // 2) Loop principal
        foreach ($postData as $key => $value) {

            // --- Datas: separam-se para montar BETWEEN depois ---
            if (substr($key, -7) === '_inicio') {
                $base = substr($key, 0, -7);
                $datasInicio[$base] = trim((string)$value);
                continue;
            }
            if (substr($key, -4) === '_fim') {
                $base = substr($key, 0, -4);
                $datasFim[$base] = trim((string)$value);
                continue;
            }

            // --- [AQUI] Arrays/CSV -> IN com prefixo id_ e cada item entre aspas ---
            if (is_array($value) || (is_string($value) && strpos($value, ',') !== false)) {
                $lista = $toList($value);
                if (!empty($lista)) {
                    $campo = $key;
                    if($key != 'situacao'){
                        $campo = 'id_' . $key;
                    }
                    $vals  = array_map(static fn($v) => "'" . addslashes((string)$v) . "'", $lista);
                    $clausulas[] = "$campo IN (" . implode(', ', $vals) . ")";
                }
                continue; // já tratou, vai pro próximo campo
            }

            // --- Escalares -> igualdade ---
            $val = trim((string)$value);
            if ($val !== '') {
                $clausulas[] = "$key = '" . addslashes($val) . "'";
            }
        }

        // 3) Monta BETWEEN para datas (ou >= / <= se só vier um lado)
        $bases = array_unique(array_merge(array_keys($datasInicio), array_keys($datasFim)));
        foreach ($bases as $base) {
            $ini = $datasInicio[$base] ?? '';
            $fim = $datasFim[$base] ?? '';

            if ($ini !== '' && $fim !== '') {
                $iniDb = dataBrToDb($ini);
                $fimDb = dataBrToDb($fim);
                $clausulas[] = "CAST($base as DATE) BETWEEN '" . addslashes(trim($iniDb)) . "' AND '" . addslashes(trim($fimDb)) . "'";
            } elseif ($ini !== '') {
                $iniDb = dataBrToDb($ini);
                $clausulas[] = "CAST($base as DATE) >= '" . addslashes(trim($iniDb)) . "'";
            } elseif ($fim !== '') {
                $fimDb = dataBrToDb($fim);
                $clausulas[] = "CAST($base as DATE) <= '" . addslashes(trim($fimDb)) . "'";
            }
        }
        // debug($clausulas);
        if(count($clausulas)){
            $filtroFinal = implode(' AND ', $clausulas);

            $retorno = $this->common->getResult('default', 'vw_ger_sults_relac',$filtroFinal);
            
            $unidade = array_values(array_unique(array_column($retorno, 'id_unidade')));
            $departamento = array_values(array_unique(array_column($retorno, 'id_departamento')));
            $assunto = array_values(array_unique(array_column($retorno, 'id_assunto')));
            $solicitante = array_values(array_unique(array_column($retorno, 'id_solicitante')));
            $responsavel = array_values(array_unique(array_column($retorno, 'id_responsavel')));
            $situacao = array_values(array_unique(array_column($retorno, 'situacao')));
        } else {
            $filtroFinal = '';
            $retorno = [];
            $unidade = [];
            $departamento = [];
            $assunto = [];
            $solicitante = [];
            $responsavel = [];
            $situacao = [];
        }
        $colunas = ['Id','Unidade','Departamento', 'Assunto', 'Solicitante','Aberto em','Responsável','Resolvido em','Concluído em','Situação','Aberto c/ Atraso','Fechado c/ Atraso','Título'];
        $campos  = ['id','unidade_nome','departamento_nome','assunto_nome','solicitante_nome','aberto','responsavel_nome','resolvido','concluido','descsituacao','aberto_com_atraso','resolvido_com_atraso','titulo'];
        
        // $resolvido = array_values(array_unique(array_column($retorno, 'resolvido')));

        // debug($resolvido, true);
        $score['Chamados'] = count($retorno);
        $score['Abertos'] = count(array_filter($retorno, function($item) {
            return empty($item['resolvido']);
        }));
        $score['Atrasados'] = count(array_filter($retorno, function($item) {
            return isset($item['aberto_com_atraso']) && $item['aberto_com_atraso'] === 'Sim';
        }));
        $score['Resolvidos'] = count(array_filter($retorno, function($item) {
            return isset($item['resolvido']) && $item['resolvido'] != '';
        }));
        $score['Resolvidos c/ Atraso'] = count(array_filter($retorno, function($item) {
            return isset($item['resolvido_com_atraso']) && $item['resolvido_com_atraso'] === 'Sim';
        }));
        $score['Na Fila'] = count(array_filter($retorno, function($item) {
            return isset($item['responsavel_nome']) && trim($item['responsavel_nome']) === ">> na Fila";
        }));
        
        $tabela = view('partials/chamados', ['colunas' => $colunas,'chamados' => $retorno,'campos' => $campos,'scores' => $score]);

        // Função utilitária: agrega por campo (id + label) e monta estrutura para ApexCharts
        $makeChartData = function(array $rows, string $idField, string $labelField): array {
            $groups = [];

            foreach ($rows as $r) {
                if (!isset($r[$idField])) continue;

                $id    = $r[$idField];
                $label = $r[$labelField] ?? (string)$id;

                if (!isset($groups[$id])) {
                    $groups[$id] = [
                        'label' => $label,
                        // separe sem atraso vs com atraso (nada de duplicar total na pilha)
                        'abertosSemAtr'    => 0,
                        'abertosAtr'       => 0,
                        'resolvidosSemAtr' => 0,
                        'resolvidosAtr'    => 0,
                    ];
                }

                $isAberto = empty($r['resolvido']); // aberto se não tem data de resolvido

                if ($isAberto) {
                    if (($r['aberto_com_atraso'] ?? '') === 'Sim') {
                        $groups[$id]['abertosAtr']++;
                    } else {
                        $groups[$id]['abertosSemAtr']++;
                    }
                } else {
                    if (($r['resolvido_com_atraso'] ?? '') === 'Sim') {
                        $groups[$id]['resolvidosAtr']++;
                    } else {
                        $groups[$id]['resolvidosSemAtr']++;
                    }
                }
            }

            $categories=[]; $abertosSemAtr=[]; $abertosAtr=[]; $resolvidosSemAtr=[]; $resolvidosAtr=[];
            foreach ($groups as $g) {
                $categories[]       = $g['label'];
                $abertosSemAtr[]    = (int)$g['abertosSemAtr'];
                $abertosAtr[]       = (int)$g['abertosAtr'];
                $resolvidosSemAtr[] = (int)$g['resolvidosSemAtr'];
                $resolvidosAtr[]    = (int)$g['resolvidosAtr'];
            }

            return [
                'categories' => $categories,
                'series' => [
                    // mesma stack = empilha; nomes continuam os que você usa no front
                    [ 'name' => 'Abertos',               'stack' => 'stack_abertos',    'data' => $abertosSemAtr ],
                    [ 'name' => 'Abertos com atraso',    'stack' => 'stack_abertos',    'data' => $abertosAtr ],
                    [ 'name' => 'Resolvidos',            'stack' => 'stack_resolvidos', 'data' => $resolvidosSemAtr ],
                    [ 'name' => 'Resolvidos com atraso', 'stack' => 'stack_resolvidos', 'data' => $resolvidosAtr ],
                ],
            ];
        };

        // Monte os 3 conjuntos de dados
        $chartUnidade = $makeChartData($retorno, 'id_unidade', 'unidade_nome');
        $chartResp    = $makeChartData($retorno, 'id_responsavel', 'responsavel_nome');
        $chartAssunto = $makeChartData($retorno, 'id_assunto', 'assunto_nome');

        return $this->response->setJSON([
            'status'       => 'ok',
            'tabela'       => $tabela,
            'unidade'      => $unidade,
            'departamento' => $departamento,
            'assunto'      => $assunto,
            'solicitante'  => $solicitante,
            'responsavel'  => $responsavel,
            'situacao'     => $situacao,
            'scores'       => $score,
            'charts' => [
                'unidade'     => $chartUnidade,
                'responsavel' => $chartResp,
                'assunto'     => $chartAssunto,
            ],
        ]);
    }
}
