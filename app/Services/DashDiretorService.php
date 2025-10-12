<?php

namespace App\Services;

use App\Models\Config\ConfigApiModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\DelivModel;
use App\Models\GerenteV2Model;

class DashDiretorService
{
    protected ConfigEmpresaModel $empresaModel;
    protected DelivModel          $delivModel;
    protected GerenteV2Model      $gerenteModel;
    protected ConfigApiModel      $apiModel;

    public function __construct()
    {
        // Instâncias dos models originais
        $this->empresaModel = new ConfigEmpresaModel();
        $this->delivModel   = new DelivModel();
        $this->gerenteModel = new GerenteV2Model();
        $this->apiModel     = new ConfigApiModel();
    }

    /**
     * Armazena variáveis de sessão (fase de "secao")
     */
    public function buscarSecao(array $vars): void
    {
        session()->set($vars);
    }

    /**
     * Monta e retorna o array ['dados','registros','cores'] para cada indicador
     */
    public function buscarDadosVant(
        string $busca,
        string $inicio,
        string $fim,
        $empresa,
        string $tipo
    ): array {
        // prepara retorno inicial
        $ret = [
            'dados'     => [],
            'registros' => 0,
            'cores'     => [],
        ];

        $fimbu = strrpos($busca, '_');
        $tipoemp = substr($busca, $fimbu + 1);
        $busca = substr($busca, 0, $fimbu);
        // debug($busca);
        $semana = array(
            'Sun' => 'Dom',
            'Mon' => 'Seg',
            'Tue' => 'Ter',
            'Wed' => 'Qua',
            'Thu' => 'Qui',
            'Fri' => 'Sex',
            'Sat' => 'Sáb'
        );
        $diasemana = array(['Dom', 'Sun'], ['Seg', 'Mon'], ['Ter', 'Tue'], ['Qua', 'Wed'], ['Qui', 'Thu'], ['Sex', 'Fri'], ['Sáb', 'Sat']);

        $meses = array(
            'January'   => 'Janeiro',
            'February'  => 'Fevereiro',
            'March'     => 'Março',
            'April'     => 'Abril',
            'May'       => 'Maio',
            'June'      => 'Junho',
            'July'      => 'Julho',
            'August'    => 'Agosto',
            'September' => 'Setembro',
            'October'   => 'Outubro',
            'November'  => 'Novembro',
            'December'  => 'Dezembro'
        );

        $ret = [];
        $cores =  [];

        if (gettype($empresa[0]) == 'array') {
            $emp = $this->empresaModel->getEmpresa($empresa);
        } else {
            $emp = $this->empresaModel->getEmpresa($empresa)[0];
        }
        $empresa = $emp['emp_codempresa'];
        $filial = $emp['emp_codfilial'];

        // se for comparativo "all", ignoramos o suffix do busca
        $baseBusca = preg_replace('/_all$/', '', $busca);

        // FATURAMENTO DIÁRIO
        if ($baseBusca === 'fat_dia') {
            $fatur = $this->gerenteModel->getFaturamento(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $ret['registros'] = count($fatur);
            $res_f = [];
            foreach ($fatur as $idx => $row) {
                $label = substr(dataDbToBr($row['DataMovimento']), 0, 5);

                if (substr($busca, -3) === 'all') {
                    $res_f[$idx] = [
                        'Data'  => $label,
                        'Total' => (float) $row['Fat_Total'],
                    ];
                } else {
                    $res_f[$idx] = [
                        'Data'   => $label,
                        'Almoço' => (float) $row['Fat_Almoco'],
                        'Janta'  => (float) $row['Fat_Janta'],
                    ];
                }

                $ret['cores'][$idx] = $this->getColorForPeriod($row['DataMovimento']);
            }

            // foreach ($fatur as $idx => $row) {
            //     $label = substr(dataDbToBr($row['DataMovimento']), 0, 5);
            //     $res_f[$idx] = [
            //         'Data'   => $label,
            //         'Almoço' => (float) $row['Fat_Almoco'],
            //         'Janta'  => (float) $row['Fat_Janta'],
            //     ];
            //     $ret['cores'][$idx] = $this->getColorForPeriod($row['DataMovimento']);
            // }
            $ret['dados'] = array_values($res_f);
        }

        // FATURAMENTO MÉDIO POR DIA DA SEMANA
        else if ($baseBusca === 'fat_sem') {
            $fatur = $this->gerenteModel->getFatDiaSem(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $ret['registros'] = count($fatur);
            $res_f = [];
            foreach ($fatur as $idx => $row) {
                $res_f[$idx] = [
                    'Dia'    => $row['dia_sem'],
                    'Almoço' => (float) $row['fat_almoco_sem'],
                    'Janta'  => (float) $row['fat_janta_sem'],
                ];
                $ret['cores'][$idx] = $this->getColorForDiaSem($row['dia_sem']);
            }
            $ret['dados'] = array_values($res_f);
        }

        // FATURAMENTO MENSAL
        else if ($baseBusca === 'fat_mes') {
            $fatur = $this->gerenteModel->getFatMes(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $ret['registros'] = count($fatur);
            $res_f = [];
            foreach ($fatur as $idx => $row) {
                $label = $row['mes_nome'] . '/' . $row['ano'];
                $res_f[$idx] = [
                    'Mês'    => $label,
                    'Almoço' => (float) $row['fat_almoco_mes'],
                    'Janta'  => (float) $row['fat_janta_mes'],
                ];
                $ret['cores'][$idx] = $this->getColorForMes($row['mes_num']);
            }
            $ret['dados'] = array_values($res_f);
        }

        // NPS DIÁRIO
        else if (substr($baseBusca, 0, 7) === 'nps_dia') {
            $nps = $this->gerenteModel->getNotasNpsDiario(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $ret['registros'] = count($nps);
            $res = [];
            $cores = [];
            foreach ($nps as $idx => $row) {
                $res[$idx] = [
                    'Dia'   => $row['opi_dia'],
                    'NPS'   => $this->calculaNps($row),
                ];
                $cores[$idx] = $this->getColorNps($row);
            }
            $ret['dados']     = array_values($res);
            $ret['cores']     = $cores;
            $ret['respostas'] = array_sum(array_column($nps, 'respostas'));
        }

        // NPS MÉDIO POR DIA DA SEMANA
        else if (substr($baseBusca, 0, 7) === 'nps_sem') {
            $nps = $this->gerenteModel->getNotasNpsSemana(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $ret['registros'] = count($nps);
            $res = [];
            $cores = [];
            foreach ($nps as $idx => $row) {
                $res[$idx]  = [
                    'Dia' => $row['dia_sem'],
                    'NPS' => $this->calculaNps($row),
                ];
                $cores[$idx] = $this->getColorForDiaSem($row['dia_sem']);
            }
            $ret['dados']     = array_values($res);
            $ret['cores']     = $cores;
            $ret['respostas'] = array_sum(array_column($nps, 'respostas'));
        }

        // NPS MENSAL
        else if (substr($baseBusca, 0, 7) === 'nps_mes') {
            $nps = $this->gerenteModel->getNotasNpsMes(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $ret['registros'] = count($nps);
            $res = [];
            $cores = [];
            foreach ($nps as $idx => $row) {
                $label = $row['mes'] . '/' . $row['ano'];
                $res[$idx] = [
                    'Mês' => $label,
                    'NPS' => $this->calculaNps($row),
                ];
                $cores[$idx] = $this->getColorForMes($row['mes']);
            }
            $ret['dados']     = array_values($res);
            $ret['cores']     = $cores;
            $ret['respostas'] = array_sum(array_column($nps, 'respostas'));
        }

        // NPS - CLIENTES (acumula todas as notas do período)
        else if ($baseBusca === 'nps_clientes') {
            $notas_res = $this->gerenteModel->getNotasNpsDiario(
                $empresa,
                $filial,
                $inicio,
                $fim
            );
            $notas  = array_fill(0, 11, 0);
            $somas  = array_fill(0, 11, 0);
            $resps  = 0;
            foreach ($notas_res as $row) {
                $resps += $row['respostas'];
                for ($i = 0; $i <= 10; $i++) {
                    $notas[$i] += $row["nota{$i}"] ?? 0;
                    $somas[$i] += $row["soma{$i}"] ?? 0;
                }
            }
            $npsVal = $this->calculaNps([
                'nota10' => $notas[10],
                'soma10' => $somas[10],
                'nota9' => $notas[9],
                'soma9' => $somas[9],
                // ...
                'nota0' => $notas[0],
                'soma0' => $somas[0],
                'respostas' => $resps
            ]);
            $ret['dados']     = [['Clientes' => $npsVal]];
            $ret['registros'] = 1;
            $ret['cores']     = ['#000']; // cor padrão
            $ret['respostas'] = $resps;
        }

        // ATRASOS DE ENTREGA (>60 min)
        else if ($baseBusca === 'atrasos_deliv') {
            $delivs = $this->delivModel->getAtrasosEntrega(
                $inicio,
                $fim,
                $empresa,
            );
            $res = [];
            $cores = [];
            foreach ($delivs as $idx => $row) {
                $res[$idx] = [
                    'Data'   => substr($row['DataMovimento'], 0, 10),
                    'Almoço' => (float)$row['Atrasos_Almoco'],
                    'Janta'  => (float)$row['Atrasos_Janta'],
                ];
                $cores[$idx] = '#f00';
            }
            $ret['dados']     = array_values($res);
            $ret['registros'] = count($res);
            $ret['cores']     = $cores;
        }

        return $ret;
    }

    /**
     * Calcula NPS a partir de linha de notas/somas
     */
    protected function calculaNps(array $row): float
    {
        $prom = ($row['soma10'] ?? 0) + ($row['soma9'] ?? 0);
        $det  = ($row['soma0'] ?? 0) + ($row['soma1'] ?? 0)
            + ($row['soma2'] ?? 0) + ($row['soma3'] ?? 0)
            + ($row['soma4'] ?? 0) + ($row['soma5'] ?? 0);
        $total = $row['respostas'] > 0 ? $row['respostas'] : 1;
        return round((($prom - $det) / $total) * 100);
    }

    /**
     * Mapeia cor do indicador NPS (exemplo: >=0 verdep, <0 vermelho)
     */
    protected function getColorNps(array $row): string
    {
        $nps = $this->calculaNps($row);
        $map = [
            10 => 'rgb(0,128,0)',
            9 => 'rgb(0,128,0)',
            8 => 'rgb(255,255,0)',
            7 => 'rgb(255,255,0)',
            6 => 'rgb(255,0,0)',
            5 => 'rgb(255,0,0)',
            4 => 'rgb(255,0,0)',
            3 => 'rgb(255,0,0)',
            2 => 'rgb(255,0,0)',
            1 => 'rgb(255,0,0)',
            0 => 'rgb(255,0,0)',
        ];
        return $map[$nps] ?? 'rgb(0,0,0)';
    }

    private function getColorForPeriod(string $date): string
    {
        $dia = date('D', strtotime($date));
        $map = [
            'Sun' => 'rgb(255,0,0)',
            'Mon' => 'rgb(0,0,255)',
            'Tue' => 'rgb(255,255,0)',
            'Wed' => 'rgb(0,128,0)',
            'Thu' => 'rgb(255,165,0)',
            'Fri' => 'rgb(139,0,139)',
            'Sat' => 'rgb(0,255,255)',
        ];
        return $map[$dia] ?? 'rgb(0,0,0)';
    }

    private function getColorForDiaSem(string $diaSem): string
    {
        $map = [
            'Dom' => 'rgb(255,0,0)',
            'Seg' => 'rgb(0,0,255)',
            'Ter' => 'rgb(255,255,0)',
            'Qua' => 'rgb(0,128,0)',
            'Qui' => 'rgb(255,165,0)',
            'Sex' => 'rgb(139,0,139)',
            'Sab' => 'rgb(0,255,255)',
        ];
        return $map[$diaSem] ?? 'rgb(0,0,0)';
    }

    private function getColorForMes(int $mes): string
    {
        $map = [
            1  => 'rgb(255,0,0)',
            2  => 'rgb(0,0,255)',
            3  => 'rgb(255,255,0)',
            4  => 'rgb(0,128,0)',
            5  => 'rgb(255,165,0)',
            6  => 'rgb(139,0,139)',
            7  => 'rgb(0,255,255)',
            8  => 'rgb(139,69,19)',
            9  => 'rgb(70,130,180)',
            10 => 'rgb(238,130,238)',
            11 => 'rgb(240,230,140)',
            12 => 'rgb(75,0,130)',
        ];
        return $map[$mes] ?? 'rgb(0,0,0)';
    }


    public function buscarDados(
        string $busca,
        string $inicio,
        string $fim,
        $empresa,
        string $tipo
    ): array {
        $ret = [
            'dados'     => [],
            'registros' => 0,
            'cores'     => [],
        ];
        // debug($inicio . ' - ' . $fim);
        $fimbu = strrpos($busca, '_');
        $tipoemp = substr($busca, $fimbu + 1);
        $baseBusca = substr($busca, 0, $fimbu);

        $semana = [
            'Sun' => 'Dom',
            'Mon' => 'Seg',
            'Tue' => 'Ter',
            'Wed' => 'Qua',
            'Thu' => 'Qui',
            'Fri' => 'Sex',
            'Sat' => 'Sáb'
        ];
        $meses = array(
            'January'   => 'Janeiro',
            'February'  => 'Fevereiro',
            'March'     => 'Março',
            'April'     => 'Abril',
            'May'       => 'Maio',
            'June'      => 'Junho',
            'July'      => 'Julho',
            'August'    => 'Agosto',
            'September' => 'Setembro',
            'October'   => 'Outubro',
            'November'  => 'Novembro',
            'December'  => 'Dezembro'
        );
        $cordia = []; // Defina as cores por dia, se necessário

        // debug($empresa);
        if (gettype($empresa ?? null) === 'array') {
            $empres = $this->empresaModel->getEmpresa($empresa);
            $empr = array_column($empres, 'emp_codempresa');
            $emp = array_values($empr);
            $filiais = array_column($empres, 'emp_codfilial');
            $filial = array_values($filiais);
            // debug($empres);
            // debug($empr);
            // debug($empf);
            // debug($emp);
            // $filial = null;
        } else {
            $empres = $this->empresaModel->getEmpresa($empresa);
            $emp[] = $empres[0]['emp_codempresa'];
            $filial[] = $empres[0]['emp_codfilial'];
        }
        // debug($empres);
        // $empresa = $emp['emp_codempresa'];
        // $filial = $emp['emp_codfilial'];

        // FATURAMENTO DIÁRIO
        if ($baseBusca === 'fat_dia') {
            $fatur = $this->gerenteModel->getFaturamento($emp, $filial, $inicio, $fim);
            if ($tipoemp === 'all') {
                $grouped = $this->agruparFaturamentoComparativo($fatur, 'dia', $semana, $meses, $cordia);
                $ret['dados'] = $grouped['dados'];
                $ret['cores'] = $grouped['cores'];
            } else {
                foreach ($fatur as $idx => $row) {
                    $label = substr(dataDbToBr($row['DataMovimento']), 0, 5);
                    $ret['dados'][$idx] = [
                        'Data'   => $label,
                        'Almoço' => (float)$row['Fat_Almoco'],
                        'Janta'  => (float)$row['Fat_Janta'],
                    ];
                    $ret['cores'][$idx] = $this->getColorForPeriod($row['DataMovimento']);
                }
            }
            $ret['registros'] = count($ret['dados']);
        }

        // FATURAMENTO POR DIA DA SEMANA
        else if ($baseBusca === 'fat_sem') {
            $fatur = $this->gerenteModel->getFatDiaSem($emp, $filial, $inicio, $fim);
            if ($tipoemp === 'all') {
                $grouped = $this->agruparFaturamentoComparativo($fatur, 'sem', $semana, $meses, $cordia);
                $ret['dados'] = $grouped['dados'];
                $ret['cores'] = $grouped['cores'];
            } else {
                foreach ($fatur as $idx => $row) {
                    $ret['dados'][$idx] = [
                        'Data'    => $semana[$row['dia_sem']],
                        'Almoço' => (float)$row['fat_almoco_sem'],
                        'Janta'  => (float)$row['fat_janta_sem'],
                    ];
                    $ret['cores'][$idx] = $this->getColorForDiaSem($semana[$row['dia_sem']]);
                }
            }
            $ret['registros'] = count($ret['dados']);
        }

        // FATURAMENTO MENSAL
        else if ($baseBusca === 'fat_mes') {
            $fim = date('Y-m-d');
            $inicio = date('Y-m-d', strtotime("-14 months"));
            $fatur = $this->gerenteModel->getFatMes($emp, $filial, $inicio, $fim);
            if ($tipoemp === 'all') {
                $grouped = $this->agruparFaturamentoComparativo($fatur, 'mes', $semana, $meses, $cordia);
                $ret['dados'] = $grouped['dados'];
                $ret['cores'] = $grouped['cores'];
            } else {
                foreach ($fatur as $idx => $row) {
                    $label = $meses[$row['mes_nome']] . '/' . $row['ano'];
                    $ret['dados'][$idx] = [
                        'Data'    => $label,
                        'Almoço' => (float)$row['fat_almoco_mes'],
                        'Janta'  => (float)$row['fat_janta_mes'],
                    ];
                    $ret['cores'][$idx] = $this->getColorForMes($row['mes_num']);
                }
            }
            $ret['registros'] = count($ret['dados']);
        }

        // NPS DIÁRIO
        else if ($baseBusca === 'nps_dia') {
            $nps = $this->gerenteModel->getNotasNpsDiario($empresa, $filial, $inicio, $fim);
            foreach ($nps as $idx => $row) {
                $ret['dados'][$idx] = [
                    'Dia' => $row['opi_dia'],
                    'NPS' => $this->calculaNps($row),
                ];
                $ret['cores'][$idx] = $this->getColorNps($row);
            }
            $ret['registros'] = count($ret['dados']);
            $ret['respostas'] = array_sum(array_column($nps, 'respostas'));
        }

        // NPS SEMANAL
        else if ($baseBusca === 'nps_sem') {
            $nps = $this->gerenteModel->getNotasNpsSemana($empresa, $filial, $inicio, $fim);
            foreach ($nps as $idx => $row) {
                $ret['dados'][$idx] = [
                    'Dia' => $row['dia_sem'],
                    'NPS' => $this->calculaNps($row),
                ];
                $ret['cores'][$idx] = $this->getColorForDiaSem($row['dia_sem']);
            }
            $ret['registros'] = count($ret['dados']);
            $ret['respostas'] = array_sum(array_column($nps, 'respostas'));
        }

        // NPS MENSAL
        else if ($baseBusca === 'nps_mes') {
            $nps = $this->gerenteModel->getNotasNpsMes($empresa, $filial, $inicio, $fim);
            foreach ($nps as $idx => $row) {
                $label = $row['mes'] . '/' . $row['ano'];
                $ret['dados'][$idx] = [
                    'Mês' => $label,
                    'NPS' => $this->calculaNps($row),
                ];
                $ret['cores'][$idx] = $this->getColorForMes($row['mes']);
            }
            $ret['registros'] = count($ret['dados']);
            $ret['respostas'] = array_sum(array_column($nps, 'respostas'));
        }

        // NPS CLIENTES
        else if ($baseBusca === 'nps_clientes') {
            $notas_res = $this->gerenteModel->getNotasNpsDiario($empresa, $filial, $inicio, $fim);
            $notas = array_fill(0, 11, 0);
            $somas = array_fill(0, 11, 0);
            $resps = 0;

            foreach ($notas_res as $row) {
                $resps += $row['respostas'];
                for ($i = 0; $i <= 10; $i++) {
                    $notas[$i] += $row["nota{$i}"] ?? 0;
                    $somas[$i] += $row["soma{$i}"] ?? 0;
                }
            }

            $npsVal = $this->calculaNps([
                'respostas' => $resps,
                'nota10' => $notas[10],
                'soma10' => $somas[10],
                'nota9'  => $notas[9],
                'soma9'  => $somas[9],
                'nota0'  => $notas[0],
                'soma0'  => $somas[0],
            ]);

            $ret['dados'] = [['Clientes' => $npsVal]];
            $ret['registros'] = 1;
            $ret['cores'] = ['#000'];
            $ret['respostas'] = $resps;
        }

        // ATRASOS
        else if ($baseBusca === 'atrasos_deliv') {
            $delivs = $this->delivModel->getAtrasosEntrega($inicio, $fim, $empresa);
            foreach ($delivs as $idx => $row) {
                $ret['dados'][$idx] = [
                    'Data'   => substr($row['DataMovimento'], 0, 10),
                    'Almoço' => (float)$row['Atrasos_Almoco'],
                    'Janta'  => (float)$row['Atrasos_Janta'],
                ];
                $ret['cores'][$idx] = '#f00';
            }
            $ret['registros'] = count($ret['dados']);
        }

        return $ret;
    }

    private function agruparFaturamentoComparativo(array $dados, string $modo, array $semana, array $meses, array $cordia = []): array
    {
        // debug($dados);
        $res_fatur = [];
        $fatu = [];
        $chaveAnt = '';
        $cont = -1;
        $cores = [];

        foreach ($dados as $row) {
            // debug($row);
            switch ($modo) {
                case 'dia':
                    $dataAtual = $row['DataMovimento'];
                    $total = $row['Fat_Total'];
                    $chave = $dataAtual;
                    $label = $semana[date('D', strtotime($dataAtual))] . ' - ' . substr(dataDbToBr($dataAtual), 0, 5);
                    break;
                case 'sem':
                    $chave = $row['dia_sem'];
                    $total = $row['fat_almoco_sem'] + $row['fat_janta_sem'];
                    $label = $chave;
                    break;
                case 'mes':
                    $chave = $meses[$row['mes_nome']] . '/' . $row['ano'];
                    $total = $row['fat_almoco_mes'] + $row['fat_janta_mes'];
                    $label = $chave;
                    break;
                default:
                    throw new \InvalidArgumentException("Modo '$modo' não suportado.");
            }

            if ($chaveAnt !== $chave) {
                if (!empty($fatu)) {
                    $cont++;
                    $cores[$cont] = $this->getColorForPeriod($chave);
                    $res_fatur[$cont] = $fatu;
                }

                $fatu = [];
                $fatu['Data'] = $label;
                // $fatu['TSS'] = 0;
                $chaveAnt = $chave;
            }

            $FatPct = floatval($total ?? 0);
            $abrev = substr($row['Empresa'], 0, 3) ?? 'EMP';
            $fatu[$abrev] = number_format($FatPct, 2, '.', '');
            // $fatu['TSS'] += $FatPct;
        }

        if (!empty($fatu)) {
            $cont++;
            $cores[$cont] = $this->getColorForPeriod($chave);
            $res_fatur[$cont] = $fatu;
        }

        return ['dados' => array_values($res_fatur), 'cores' => $cores];
    }
}
