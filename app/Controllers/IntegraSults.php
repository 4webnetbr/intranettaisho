<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CommonModel;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;


class IntegraSults extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $common;


	public function __construct(){
		// $this->data  = session()->getFlashdata('dados_tela');
        $this->common     = new  CommonModel();
	}

	public function integrar($start = 0)
	{
        $inicio = date('Y-m-d\TH:i:s\Z',strtotime('2025-08-01 08:00:00'));
        $inicio = date('Y-m-d\TH:i:s\Z', strtotime('-120 days midnight'));
        debug($inicio);
        $apis['api_nome']       = 'API Sults';
        $apis['api_url']        = "https://api.sults.com.br/api/v1/chamado/ticket?start={$start}&limit=100&abertoStart={$inicio}";
        $apis['api_tokenkey']   = 'O3RhaXNobzsxNzU1MTM5NDE4MDM5';
        // debug($apis);
        
            $ressults = get_sults_curl($apis);
            $registros = count($ressults['data']);
            debug('Registros: '.$registros);

            // debug($ressults);
            for ($rs=0; $rs < count($ressults['data']) ; $rs++) { 
                $sults = $ressults['data'][$rs];
                debug($sults);
                // PEGAR SOLICITANTE
                $solic['pes_id'] = $sults['solicitante']['id'];
                $solic['pes_nome'] = $sults['solicitante']['nome'];
                $chave = 'pes_id = '.$solic['pes_id'];
                $existesol = $this->common->saveReg('default', 'ger_sults_pessoas', $solic,$chave);
                // PEGAR RESPONSAVEL
                if(isset($sults['responsavel']) && count($sults['responsavel']) > 0){
                    $respo['pes_id'] = $sults['responsavel']['id'];
                    $respo['pes_nome'] = $sults['responsavel']['nome'];
                    $chave = 'pes_id = '.$respo['pes_id'];
                    $existesol = $this->common->saveReg('default', 'ger_sults_pessoas', $respo, $chave);
                } else {
                    $respo['pes_id'] = 99999; // FILA
                }
                // PEGAR UNIDADE
                $unida['und_id'] = $sults['unidade']['id'];
                $unida['und_nome'] = $sults['unidade']['nome'];
                $chave = 'und_id = '.$unida['und_id'];
                $existesol = $this->common->saveReg('default', 'ger_sults_unidade', $unida, $chave);
                // PEGAR DEPARTAMENTO
                if($sults['departamento']['id'] != null){
                    $depar['dep_id'] = $sults['departamento']['id'];
                    $depar['dep_nome'] = $sults['departamento']['nome'];
                    $chave = 'dep_id = '.$depar['dep_id'];
                    $depar['dep_tipo'] = 'UND';
                    if(str_contains(strtoupper($sults['unidade']['nome']), 'GRUPO')){
                        $depar['dep_tipo'] = 'ADM';
                    }
                    $existesol = $this->common->saveReg('default', 'ger_sults_departamento', $depar, $chave);
                }
                // PEGAR ASSUNTO
                if($sults['assunto']['id'] != null){
                    $assun['ass_id'] = $sults['assunto']['id'];
                    $assun['ass_nome'] = $sults['assunto']['nome'];
                    $assun['ass_tipo'] = 'UND';
                    if(str_contains(strtoupper($sults['unidade']['nome']), 'GRUPO')){
                        $assun['ass_tipo'] = 'ADM';
                    }
                    $chave = 'ass_id = '.$assun['ass_id'];
                    $existesol = $this->common->saveReg('default', 'ger_sults_assunto', $assun, $chave);
                }

                $chama['id'] = $sults['id'];
                $chama['titulo'] = $sults['titulo'];
                $chama['id_solicitante'] = $solic['pes_id'];
                $chama['id_responsavel'] = $respo['pes_id'];
                $chama['id_unidade'] = $unida['und_id'];
                $chama['id_departamento'] = $depar['dep_id'];
                $chama['id_assunto'] = $assun['ass_id'];
                $chama['id_etiqueta'] = null;
                $chama['id_apoio'] = null;
                $chama['tipo'] = $sults['tipo'];
                $chama['aberto'] = $sults['aberto'];
                $chama['resolvido'] = $sults['resolvido'];
                $chama['concluido'] = $sults['concluido'];
                $chama['resolverPlanejado'] = $sults['resolverPlanejado'];
                $chama['resolverEstipulado'] = $sults['resolverEstipulado'];
                $chama['avaliacaoNota'] = $sults['avaliacaoNota'];
                $chama['avaliacaoObservacao'] = $sults['avaliacaoObservacao'];
                $chama['situacao'] = $sults['situacao'];
                $chama['primeiraInteracao'] = $sults['primeiraInteracao'];
                $chama['ultimaAlteracao'] = $sults['ultimaAlteracao'];
                $chama['countInteracaoPublico'] = $sults['countInteracaoPublico'];
                $chama['countInteracaoInterno'] = $sults['countInteracaoInterno'];

                debug($chama);
                $chave = 'id = '.$sults['id'];
                
                $chamado = $this->common->saveReg('default', 'ger_sults', $chama, $chave);
                # code...
                // PEGAR ETIQUETA
                if(count($sults['etiqueta']) > 0){
                    $etiid = '';
                    for ($et=0; $et < count($sults['etiqueta']) ; $et++) { 
                        $etq = $sults['etiqueta'][$et];
                        $etiqu['eti_id'] = $etq['id'];
                        $etiqu['eti_nome'] = $etq['nome'];
                        $chave = 'eti_id = '.$etiqu['eti_id'];
                        $etiid .= $etiqu['eti_id'].',';
                        $existesol = $this->common->saveReg('default', 'ger_sults_etiqueta', $etiqu, $chave);
                        $link = [];
                        $link['id_sults'] = $sults['id'];
                        $link['eti_id'] = $etiqu['eti_id'];
                        $chave = 'id_sults = '.$sults['id'].' AND eti_id = '.$etiqu['eti_id'];
                        debug($link);
                        debug($chave);
                        $existesol = $this->common->saveReg('default', 'ger_sults_etiqueta_link', $link, $chave);
                    }
                    $etiqu['eti_id'] = substr($etiid, 0, -1);
                } else {
                    $etiqu['eti_id'] = null;
                }
                // PEGAR APOIO
                if(isset($sults['apoio']) && count($sults['apoio']) > 0){
                    $apoid = '';
                    for ($ap=0; $ap < count($sults['apoio']) ; $ap++) { 
                        $apo = $sults['apoio'][$ap]['pessapoiooa'];
                        $apoio['apo_id'] = $apo['id'];
                        $apoio['apo_nome'] = $apo['nome'];
                        $chave = 'apo_id = '.$apo['id'];
                        $apoid .= $apoio['apo_id'].',';
                        $existesol = $this->common->saveReg('default', 'ger_sults_apoio', $apoio, $chave);
                        $link = [];
                        $link['id_sults'] = $sults['id'];
                        $link['apo_id'] = $apoio['apo_id'];
                        $chave = 'id_sults = '.$sults['id'].' AND apo_id = '.$apoio['apo_id'];
                        debug($link);
                        debug($chave);
                        $existesol = $this->common->saveReg('default', 'ger_sults_apoio_link', $link, $chave);
                    }
                    $apoio['apo_id'] = substr($apoid, 0, -1);
                } else {
                    $apoio['apo_id'] = null;
                }
            }
            if($registros == 100){
                $start = $ressults['start'] + 1;
                $this->integrar($start);
            }
    }
}