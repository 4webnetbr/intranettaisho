<?php

namespace App\Models;

use CodeIgniter\Model;

class GerenteV2Model extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfy_cuponsvenda';
    protected $view       = 'vw_total_faturamento_aj';

    protected $returnType = 'array';


    public function getFaturamento($empresa, $filial = null, $inicio = false, $fim = false)
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        // $db = db_connect($this->DBGroup);
        $builder = $this->db->table($this->view);
        $builder->select('*')
            ->whereIn('NrEmpresa', $empresa)
            ->where('DataMovimento >=', $inicio)
            ->where('DataMovimento <=', $fim);

        if ($filial !== null) {
            $builder->where('NrFilial', $filial);
        }

        // $sql = $builder->getCompiledSelect();
        // debug($sql);
        $ret = $builder->get()->getResultArray();
        if (count($empresa) > 1) {
            $sql = $this->db->getLastQuery();
            echo $sql;
        }

        return $ret;
    }

    public function getFatDiaSem($empresa, $filial = null, $inicio = false, $fim = false)
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        // $db = db_connect($this->DBGroup);
        $builder = $this->db->table($this->view);
        $builder->select(
            'dia_sem, NrEmpresa, NrFilial, Empresa, '
                . 'SUM(Fat_Almoco) as fat_almoco_sem, '
                . 'SUM(Fat_Janta) as fat_janta_sem, '
                . 'COUNT(dia_sem) as cont_dia_sem'
        );
        $builder->whereIn('NrEmpresa', $empresa);
        if ($filial !== null) {
            $builder->where('NrFilial', $filial);
        }
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->groupBy('dia_sem,NrEmpresa,NrFilial');
        $builder->orderBy('dia_sem_num');

        $ret = $builder->get()->getResultArray();
        // if (count($empresa) > 1) {
        //     $sql = $this->db->getLastQuery();
        //     echo $sql;
        // }
        // $sql = $this->db->getLastQuery();
        // echo $sql;

        return $ret;
    }


    public function getFatMes($empresa, $filial = null, $inicio = false, $fim = false)
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        // $db = db_connect($this->DBGroup);
        $builder = $this->db->table($this->view);
        $builder->select(
            'mes_nome, mes_num, ano, NrEmpresa, NrFilial, Empresa, '
                . 'SUM(Fat_Almoco) as fat_almoco_mes, '
                . 'SUM(Fat_Janta) as fat_janta_mes'
        );
        $builder->whereIn('NrEmpresa', $empresa);
        if ($filial !== null) {
            $builder->where('NrFilial', $filial);
        }
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->groupBy('mes_num, ano, NrEmpresa,NrFilial');
        $builder->orderBy('DataMovimento');

        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // echo $sql;

        return $ret;
    }


    public function getNotasNpsDiario($empresa, $filial, $inicio = false, $fim = false)
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }
        // debug($inicio);
        // debug($fim);
        // $db = db_connect('default');
        $builder = $this->db->table('opi_diario');
        $builder->SELECT('opi_dia,
        dia_sem,
        opi_codempresa,
        opi_codfilial,
        respostas,
        nota10,soma10,
        nota9,soma9,
        nota8,soma8,
        nota7,soma7,
        nota6,soma6,
        nota5,soma5,
        nota4,soma4,
        nota3,soma3,
        nota2,soma2,
        nota1,soma1,
        nota0,soma0');
        $builder->where('opi_codempresa', $empresa);
        $builder->where('opi_codfilial', $filial);
        $builder->where('opi_dia >=', $inicio);
        $builder->where('opi_dia <=', $fim);
        $builder->groupBy('opi_dia,opi_codempresa, opi_codfilial');
        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // echo $sql;
        return $ret;
    }

    public function getNotasNpsSemana($empresa, $filial, $inicio = false, $fim = false)
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }
        // $db = db_connect('default');
        $builder = $this->db->table('opi_diario');
        $builder->SELECT('dia_sem,
        opi_codempresa,
        opi_codfilial,
        respostas,
        nota10,soma10,
        nota9,soma9,
        nota8,soma8,
        nota7,soma7,
        nota6,soma6,
        nota5,soma5,
        nota4,soma4,
        nota3,soma3,
        nota2,soma2,
        nota1,soma1,
        nota0,soma0');
        $builder->where('opi_codempresa', $empresa);
        $builder->where('opi_codfilial', $filial);
        $builder->where('opi_dia >=', $inicio);
        $builder->where('opi_dia <=', $fim);
        $builder->groupBy('dia_sem, opi_codempresa, opi_codfilial');
        // $sql = $builder->getCompiledSelect();
        // debug($sql);
        return $builder->get()->getResultArray();
    }

    public function getNotasNpsMes($empresa, $filial, $inicio = false, $fim = false)
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }
        // $db = db_connect('default');
        $builder = $this->db->table('opi_diario');
        $builder->SELECT('MONTH(opi_dia) as mes,
        YEAR(opi_dia) as ano,
        opi_codempresa,
        opi_codfilial,
        respostas,
        nota10,soma10,
        nota9,soma9,
        nota8,soma8,
        nota7,soma7,
        nota6,soma6,
        nota5,soma5,
        nota4,soma4,
        nota3,soma3,
        nota2,soma2,
        nota1,soma1,
        nota0,soma0');
        $builder->where('opi_codempresa', $empresa);
        $builder->where('opi_codfilial', $filial);
        $builder->where('opi_dia >=', $inicio);
        $builder->where('opi_dia <=', $fim);
        $builder->groupBy('opi_dia, opi_codempresa, opi_codfilial');
        $builder->orderBy('opi_dia ASC');
        return $builder->get()->getResultArray();
    }
}
