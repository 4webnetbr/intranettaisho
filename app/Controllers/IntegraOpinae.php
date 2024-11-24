<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CommonModel;
use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;


class IntegraOpinae extends BaseController
{
    public $data = [];
    public $permissao = '';
    public $empresa;
    public $apis;
    public $common;


	public function __construct(){
		// $this->data  = session()->getFlashdata('dados_tela');
        $this->empresa    = new  ConfigEmpresaModel();
        $this->apis       = new  ConfigApiModel();
        $this->common     = new  CommonModel();
	}

	public function integrar()
	{
        $device = [
            'Matriz'        => ['opi_codempresa' => 1063, 'opi_codfilial' => 1],
            'Filial 04'     => ['opi_codempresa' => 1073, 'opi_codfilial' => 1],
            'Filial 05'     => ['opi_codempresa' => 1073, 'opi_codfilial' => 2],
            'SHOP.CTBA'     => ['opi_codempresa' => 1073, 'opi_codfilial' => 3],
        ];
        // debug($device);
        $apis = $this->apis->getApisSearch('Opinae');
        // debug($apis);
        
        for($a=0;$a<count($apis);$a++){
            $api = $apis[$a];
            $pesquisas = get_opinae_curl($api);
            // debug('Pesquisa');
            // debug($pesquisas, true);
            if(isset($pesquisas->tableData)){
                for($p=0;$p<count($pesquisas->tableData);$p++){
                    $respes = $pesquisas->tableData[$p];
                    $array = array_values((array) $respes);
                    $sqlres = [
                        'opi_id_opinae' => $respes->id,
                        'opi_data'      => dataBrToDb($respes->date),
                        'opi_empresa'   => $respes->device,
                        'opi_nota'      => $array[3],
                        'opi_codempresa' => $device[$respes->device]['opi_codempresa'],
                        'opi_codfilial'  => $device[$respes->device]['opi_codfilial'],
                        'opi_api'        => $api['api_nome'],
                    ];
                    // debug($sqlres);
                    $condicao = "opi_id_opinae = ".$respes->id." AND opi_api = '".$api['api_nome']."' ";
                    $existe = $this->common->getExiste('default','ger_opinae',$condicao);
                    if(!isset($existe[0]['opi_id_opinae'])){
                        log_message('info','Insert na base '.json_encode($sqlres));
                        $sv_opinae = $this->common->insertReg('default', 'ger_opinae',$sqlres);
                    } else {
                        log_message('info','Registro já Existe '.json_encode($sqlres));
                    }
                    // debug($sv_opinae);
                }
            }
        }
    }
}