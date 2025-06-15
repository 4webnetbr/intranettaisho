<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class GerenteModelV2 extends Model
{
    protected string $DBGroup    = 'default';
    protected string $table      = 'vw_total_faturamento_aj';
    protected string $view       = 'vw_total_faturamento_aj';
    protected string $returnType = 'array';


    public function getFaturamento(array $empresa, int $filial = null, string $inicio = false, string $fim = false): array
    {
        debug('Aqui', true);

        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        $db = db_connect($this->DBGroup);
        $builder = $db->table($this->view);
        $builder->select('*')
            ->whereIn('NrEmpresa', $empresa)
            ->where('DataMovimento >=', $inicio)
            ->where('DataMovimento <=', $fim);

        if ($filial !== null) {
            $builder->where('NrFilial', $filial);
        }

        return $builder->get()->getResultArray();
    }

    public function getFatDiaSem(array $empresa, int $filial = null, string $inicio = false, string $fim = false): array
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        $db = db_connect($this->DBGroup);
        $builder = $db->table($this->view);
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
        $builder->groupBy('dia_sem');
        $builder->orderBy('dia_sem_num');

        return $builder->get()->getResultArray();
    }


    public function getFatMes(int $empresa, int $filial = null, string $inicio = false, string $fim = false): array
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        $db = db_connect($this->DBGroup);
        $builder = $db->table($this->view);
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
        $builder->groupBy('mes_num, ano');
        $builder->orderBy('DataMovimento');

        return $builder->get()->getResultArray();
    }


    public function getNotasNpsDiario(int $empresa, int $filial = null, string $inicio = false, string $fim = false): array
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        $builder = db_connect($this->DBGroup)
            ->table('opi_diario')
            ->select(
                'opi_dia, dia_sem, opi_codempresa, opi_codfilial, respostas, '
                    . 'nota10, soma10, nota9, soma9, nota8, soma8, '
                    . 'nota7, soma7, nota6, soma6, nota5, soma5, nota4, soma4, '
                    . 'nota3, soma3, nota2, soma2, nota1, soma1, nota0, soma0'
            );
        $builder->where('opi_codempresa', $empresa);
        if ($filial !== null) {
            $builder->where('opi_codfilial', $filial);
        }
        $builder->where('opi_dia >=',      $inicio);
        $builder->where('opi_dia <=',      $fim);
        $builder->groupBy('opi_dia, opi_codempresa, opi_codfilial');

        return $builder->get()->getResultArray();
    }

    public function getNotasNpsSemana(int $empresa, int $filial = null, string $inicio = false, string $fim = false): array
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        $builder = db_connect($this->DBGroup)
            ->table('opi_diario')
            ->select(
                'dia_sem, opi_codempresa, opi_codfilial, respostas, '
                    . 'nota10, soma10, nota9, soma9, nota8, soma8, '
                    . 'nota7, soma7, nota6, soma6, nota5, soma5, nota4, soma4, '
                    . 'nota3, soma3, nota2, soma2, nota1, soma1, nota0, soma0'
            );
        $builder->where('opi_codempresa', $empresa);
        if ($filial !== null) {
            $builder->where('opi_codfilial', $filial);
        }
        $builder->where('opi_dia >=',      $inicio);
        $builder->where('opi_dia <=',      $fim);
        $builder->groupBy('dia_sem, opi_codempresa, opi_codfilial');

        return $builder->get()->getResultArray();
    }

    public function getNotasNpsMes(int $empresa, int $filial = null, string $inicio = false, string $fim = false): array
    {
        if (!$inicio) {
            $inicio = $fim = date('Y-m-d');
        }

        $builder = db_connect($this->DBGroup)
            ->table('opi_diario')
            ->select(
                'MONTH(opi_dia) as mes, YEAR(opi_dia) as ano, '
                    . 'opi_codempresa, opi_codfilial, respostas, '
                    . 'nota10, soma10, nota9, soma9, nota8, soma8, '
                    . 'nota7, soma7, nota6, soma6, nota5, soma5, nota4, soma4, '
                    . 'nota3, soma3, nota2, soma2, nota1, soma1, nota0, soma0'
            );
        $builder->where('opi_codempresa', $empresa);
        if ($filial !== null) {
            $builder->where('opi_codfilial', $filial);
        }
        $builder->where('opi_dia >=',      $inicio);
        $builder->where('opi_dia <=',      $fim);
        $builder->groupBy('opi_dia, opi_codempresa, opi_codfilial');
        $builder->orderBy('opi_dia ASC');

        return $builder->get()->getResultArray();
    }
}
