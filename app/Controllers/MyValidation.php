<?php 
namespace App\Controllers;
use App\Models\Config\ConfigStatusModel;
use App\Models\Produt\ProdutClasseModel;

class MyValidation
{
    /**
     * Validação personalizada, utilizada na Classe Status
     * Usada para validar se já existe um Status com o nome informado, na Tela informada
     * @param mixed $value  //o nome do status
     * @param string $params // parametro obrigatório definido como [] vazio
     * @param array $data // os dados que estão sendo submetidos
     * @return bool
     */
    public function nome_status_existe($value, string $params, array $data): bool
    {
        $params = explode(',', $params);

        $nome = $value;
        $tel_id   = $data['tel_id'];
        $stt_id   = $data['stt_id'];
        $stat = new ConfigStatusModel();
        $tem = $stat->getStatusNomeTela($tel_id, $nome, $stt_id);
        if(count($tem)==0){
            return true;
        }
        else
        {
            return false;  
        }		
	}    

}