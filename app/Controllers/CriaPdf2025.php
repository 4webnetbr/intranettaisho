<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MyPdf2025;
use App\Models\Estoqu\EstoquCompraModel;
use DateTime;

class CriaPdf2025 extends BaseController
{
    public $data;
    public $compra;
    public $compraproduto;
    public $produto;
    public $materiais;
    public $pdf;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->data             = session()->getFlashdata('dados_classe');
        $this->compra           = new EstoquCompraModel();
    }

    public function PedidoCompra($com_id)
    {
        $compras = $this->compra->getCompra($com_id);
        if ($compras) {
            $compra = $compras[0];
            // debug($compra, true);

            $this->pdf = new MyPdf2025();
            $this->pdf->SetAutoPageBreak(true, 12);

            $this->pdf->SetTitle(formata_texto('Pedido de Compra Nº: ' . $compra['com_id']));
            // $this->pdf->SetFooterCenter(formata_texto('Orçamento Nº: '.$orcam['orc_numanoversao'].' - '.$orcam['orc_ac']));
            $this->pdf->Add_Page('P', 'A4', 0);
            $dataem = dataDbToBr($compra['com_data']);
            $this->pdf->EtiqTexto('Pedido de Compra:', '', 'Arial', 11, 6, 0, 1, 0, 'L');
            $this->pdf->EtiqTexto('Emitido em:  ', '', 'Arial', 11, 6, 0, 0, 1, 'R');
            $this->pdf->EtiqTexto('', $compra['com_id'], 'Arial', 14, 6, 0, 1, 0, 'L', 0, 'B');
            $this->pdf->EtiqTexto('', $dataem, 'Arial', 11, 6, 0, 0, 1, 'R');
            $this->pdf->EtiqTexto('Empresa:  ', '', 'Arial', 11, 6, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('', $compra['emp_cnpj'] . ' - ' . $compra['emp_nome'] . ' - ' . $compra['emp_apelido'], 'Arial', 11, 6, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('', $compra['emp_endereco'], 'Arial', 11, 6, 0, 0, 1, 'L');

            $this->pdf->EtiqTexto('', '', 'Arial', 11, 4, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('Fornecedor:  ', '', 'Arial', 11, 6, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('', $compra['for_razao'] . ' - ' . $compra['for_cnpj'], 'Arial', 11, 6, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('', $compra['for_endereco'], 'Arial', 11, 6, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('', '', 'Arial', 11, 4, 0, 0, 1, 'L');
            $this->pdf->EtiqTexto('Produtos', '', 'Arial', 13, 4, 0, 0, 1, 'L');

            $produtos = $this->compra->getCompraProd($com_id);

            if (count($produtos) > 0) {
                $this->pdf->Ln(2);
                $this->pdf->EtiqTexto('Produto', '', 'Arial', 10, 6, 55, 1, 0, 'L', 0, 'B');
                $this->pdf->EtiqTexto('Marca', '', 'Arial', 10, 6, 30, 1, 0, 'L', 0, 'B');
                $this->pdf->EtiqTexto('Und', '', 'Arial', 10, 6, 23, 1, 0, 'L', 0, 'B');
                $this->pdf->EtiqTexto('Qtd.', '', 'Arial', 10, 6, 15, 1, 0, 'C', 0, 'B');
                $this->pdf->EtiqTexto('Unit', '', 'Arial', 10, 6, 20, 1, 0, 'C', 0, 'B');
                $this->pdf->EtiqTexto('Subtotal', '', 'Arial', 10, 6, 25, 1, 0, 'C', 0, 'B');
                $this->pdf->EtiqTexto('Prev Ent.', '', 'Arial', 10, 6, 22, 1, 1, 'C', 0, 'B');
                $totalgeral = 0;
                for ($p = 0; $p < count($produtos); $p++) {
                    $prod = $produtos[$p];
                    $previsao = $prod['cop_previsao'] ?? $compra['com_previsao'];
                    $this->pdf->SetFont('Arial', '', 8);
                    $this->pdf->MultiCellSafe(55, 3, formata_texto($prod['pro_nome']), 1, 'J');
                    $this->pdf->setY($this->pdf->GetY() - 6);
                    $this->pdf->setX(65);
                    $this->pdf->SetFont('Arial', '', 8);
                    $this->pdf->MultiCellSafe(30, 3, formata_texto($prod['mar_nome']), 1, 'J');
                    $this->pdf->setY($this->pdf->GetY() - 6);
                    $this->pdf->setX(95);

                    // $this->pdf->EtiqTexto('', $prod['mar_nome'] ?? '.', 'Arial', 8, 6, 30, 1, 0, 'L');
                    $this->pdf->EtiqTexto('', $prod['und_sigla'], 'Arial', 8, 6, 23, 1, 0, 'L');
                    $this->pdf->EtiqTexto('', formataQuantia($prod['cop_quantia'])['qtia'], 'Arial', 8, 6, 15, 1, 0, 'R');
                    $this->pdf->EtiqTexto('', floatToMoeda($prod['cop_valor']), 'Arial', 8, 6, 20, 1, 0, 'R');
                    $this->pdf->EtiqTexto('', floatToMoeda($prod['cop_total']), 'Arial', 8, 6, 25, 1, 0, 'R');
                    $this->pdf->EtiqTexto('', dataDbToBr($previsao), 'Arial', 8, 6, 22, 1, 1, 'R');
                    $totalgeral += $prod['cop_total'];
                    // $this->pdf->Ln(5);
                    if ($this->pdf->GetY() > 260) { // Se estiver muito perto do fim da página (por exemplo, 260mm)
                        $this->pdf->Add_Page('P', 'A4', 0);
                    }
                }
                $this->pdf->EtiqTexto('Total do Pedido', '', 'Arial', 10, 6, 150, 1, 0, 'C', 0, 'B');
                $this->pdf->EtiqTexto('', floatToMoeda($totalgeral), 'Arial', 10, 6, 40, 1, 1, 'C', 0, 'B');
            }
            $texto = $compra['emp_obs'];
            $enc = mb_detect_encoding($texto, "UTF-8, ISO-8859-1, Windows-1252", true);
            // Se necessário, converte o texto para UTF-8 (ajuste o parâmetro 'origem' conforme seu caso)
            if ($enc !== 'UTF-8') {
                $texto = mb_convert_encoding($texto, 'UTF-8', $enc);
            }
            $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
            $texto = utf8_decode($texto);
            $this->pdf->SetFont('Arial', '', 10);
            $this->pdf->Ln(2);
            $this->pdf->WriteHTML($texto);

            $this->pdf->AliasNbPages();

            $output = $this->pdf->Output();
            $output = base64_encode($output);
            echo $output;
        }
    }
}
