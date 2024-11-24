<?php

namespace App\Models;

use CodeIgniter\Model;

class DelivModel extends Model
{
    protected $DBGroup          = 'dbDelivery';
    protected $table            = '4deliv_rota';
    protected $view             = 'vw_tempo_entrega';

    protected $returnType       = 'array';

    public function getTempos60($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('dbDelivery');
        $query = $db->query("SELECT *
                            FROM vw_tempo_maior60
                            WHERE ope_empresa = '".$empresa."'
                            AND ope_filial = '".$filial."'
                            AND pcfy_DataPedido >= '".$inicio."'
                            AND pcfy_DataPedido <= '".$fim."'");
        $ret = $query->getResultArray();
        return $ret;

    }

}
