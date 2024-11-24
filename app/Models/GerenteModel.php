<?php

namespace App\Models;

use CodeIgniter\Model;

class GerenteModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cfy_cuponsvenda';
    protected $view             = 'vw_total_faturamento';

    protected $returnType       = 'array';

    public function getFaturamento($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('vw_total_faturamento');
        $builder->select('*');
        $builder->where('NrEmpresa', $empresa);
        $builder->where('NrFilial', $filial);
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        // $sql  = $builder->getCompiledSelect();
        // debug($sql);
        return $builder->get()->getResultArray();
    }

    public function getFaturamentoEmpresas($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('vw_total_faturamento');
        $builder->select('*');
        // $builder->whereIn('NrFilial', $filial);
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->whereIn('emp_id', $empresa);
        $builder->groupBy('DataMovimento, emp_id');
        $builder->orderBy('DataMovimento, emp_id');
        // $sql  = $builder->getCompiledSelect();
        // debug($sql);
        $ret = $builder->get()->getResultArray();
        // debug($ret);
        return $ret;
    }

    public function getFatDiaSem($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('vw_total_faturamento');
        $builder->select('dia_sem, 
                        NrEmpresa, 
                        NrFilial, 
                        Empresa, 
                        SUM(FatTotal) as fat_dia_sem,
                        COUNT(dia_sem) as cont_dia_sem, 
                        AVG(FatTotal) as fat_medio_dia');
        $builder->where('NrEmpresa', $empresa);
        $builder->where('NrFilial', $filial);
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->groupBy('dia_sem');
        $builder->orderBy('dia_sem_num');
        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // debug($sql);
        return $ret;
    }

    public function getFatDiaSemEmpresas($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('vw_total_faturamento');
        $builder->select('dia_sem, NrEmpresa, NrFilial, Empresa, emp_id, emp_abrev, SUM(FatTotal) as fat_dia_sem');
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->whereIn('emp_id', $empresa);
        $builder->groupBy('dia_sem, emp_id');
        $builder->orderBy('dia_sem_num, emp_id');
        $ret = $builder->get()->getResultArray();
        // $sql = $this->db->getLastQuery();
        // debug($sql);
        return $ret;
    }

    public function getFatMes($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('vw_total_faturamento');
        $builder->select('mes_nome, mes_num, ano, NrEmpresa, NrFilial, Empresa, SUM(FatTotal) as fat_mes');
        $builder->where('NrEmpresa', $empresa);
        $builder->where('NrFilial', $filial);
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->groupBy('mes_num, ano' );
        $builder->orderBy('DataMovimento');
        return $builder->get()->getResultArray();
    }

    public function getFatMesEmpresas($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('vw_total_faturamento');
        $builder->select('mes_nome, mes_num, ano, NrEmpresa, NrFilial, Empresa,emp_id, emp_abrev,  SUM(FatTotal) as fat_mes');
        $builder->where('DataMovimento >=', $inicio);
        $builder->where('DataMovimento <=', $fim);
        $builder->whereIn('emp_id', $empresa);
        $builder->groupBy('mes_num, ano, emp_id');
        $builder->orderBy('DataMovimento, emp_id');

        return $builder->get()->getResultArray();
    }

    public function getNotasNpsDiario($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        // debug($inicio);
        // debug($fim);
        $db = db_connect('default');
        $builder = $db->table('opi_diario');
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
        $sql = $this->db->getLastQuery();
        // echo $sql;
        return $ret;
    }

    public function getNotasNpsSemana($empresa, $filial, $inicio = false, $fim = false)
    {
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('opi_diario');
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
        if(!$inicio){
            $inicio =$fim = date('Y-m-d');
        }
        $db = db_connect('default');
        $builder = $db->table('opi_diario');
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
