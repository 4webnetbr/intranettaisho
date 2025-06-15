<?php

namespace App\Libraries;

use FPDF;

class MyPdf2025 extends FPDF
{
    protected $orientation;
    protected $size;
    protected $rotation;
    protected $units;
    protected $logo;
    protected $head_title;
    protected $head_subtitle;
    protected $footer_page_literal;
    protected $footer_center;


    private $base_url;
    private $format;
    private $temheader;
    private $temfooter;
    var $B; // Negrito
    var $I; // Itálico
    var $U; // Sublinhado
    var $HREF; // Hyperlink
    var $ALIGN; // Alinhamento
    var $FONTFAMILY; // Família de fontes
    var $FONTSIZE; // Tamanho da fonte
    var $COLOR; // Cor
    var $TEXTCOLOR; // Cor do texto
    var $BGCOLOR; // Cor de fundo

    function __construct($temheader = true, $temfooter = true, $size = false)
    {
        $config = config('Pdf');
        $this->orientation          =   $config->orientation;
        if (!$size) {
            $this->size                 =   $config->size;
        } else {
            $this->size                 =   $size;
        }
        $this->rotation             =   $config->rotation;
        $this->units                =   $config->units;
        $this->format               =   $config->format;
        $this->head_title           =   $this->format($config->head_title);
        $this->head_subtitle        =   $this->format($config->head_subtitle);
        $this->footer_page_literal  =   $this->format($config->footer_page_literal);

        $this->base_url         =   $config->url_wrapper;
        if ($this->base_url === TRUE)
            $this->logo = base_url($config->logo);
        else
            $this->logo = $config->logo;

        $this->temheader = $temheader;
        $this->temfooter = $temfooter;
        // lets construct the fpdf objet!
        parent::__construct($this->orientation, $this->units, $this->size);

        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->ALIGN = 'left';
        $this->FONTFAMILY = 'Arial';
        $this->FONTSIZE = 12;
        $this->COLOR = array(0, 0, 0);
        $this->TEXTCOLOR = array(0, 0, 0);
        $this->BGCOLOR = array(255, 255, 255);
    }

    /**
     * header function
     *
     * @param none
     * @return none
     **/
    function header()
    {
        if ($this->temheader) {
            $this->Image($this->logo, 11, 10, 30);
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 6, utf8_decode('GRUPO TAISHO'), 0, 1, 'R');
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 3, utf8_decode('LMD JAPAN FOOD COMPANY LTDA'), 0, 1, 'R');
            $this->Ln(10);
        }
    }

    /**
     * footer function
     *
     * @param none
     * @return none
     **/
    function footer()
    {
        // if ($this->temfooter) {
        //     $this->SetY(-10);
        //     $this->SetFont('Arial', '', 8);
        //     $this->Cell(80, 3, utf8_decode('R. Professor Alfredo Valente, 1158 - Jd Gramados - Alm. Tamandaré - CEP 83.504-000'), 0, 0, 'L');
        //     $this->Cell(0, 3, utf8_decode('+55 41 3657-7755'), 0, 0, 'C');
        //     // $this->Cell(0,3,utf8_decode('E-mail: pelegrini@pelegrini.ind.br'),0,1,'R');
        //     // $this->Cell(0,0,utf8_decode('Artefatos de Metais Pelegrini Ltda'),0,1,'L');
        //     // $this->Cell(0,0,$this->footer_center,0,0,'C');
        //     $this->Cell(0, 3, "{$this->footer_page_literal} " . $this->PageNo() . '/{nb}', 0, 0, 'R');
        // }
    }

    /**
     * logo getter
     *
     * @param none
     * @return string
     **/
    function get_logo()
    {
        return $this->logo;
    }

    /**
     * orientation getter
     *
     * @param none
     * @return string
     **/
    function get_orientation()
    {
        return $this->orientation;
    }

    /**
     * size getter
     *
     * @param none
     * @return string
     **/
    function get_size()
    {
        return $this->size;
    }

    /**
     * rotation getter
     *
     * @param none
     * @return int
     **/
    function get_rotation()
    {
        return $this->rotation;
    }

    /**
     * units getter
     *
     * @param none
     * @return string
     **/
    function get_units()
    {
        return $this->units;
    }

    /**
     * Head title getter
     *
     * @param none
     * @return string
     **/
    function get_head_title()
    {
        return $this->head_title;
    }

    /**
     * Head subtitle getter
     *
     * @param none
     * @return string
     **/
    function get_head_subtitle()
    {
        return $this->head_subtitle;
    }

    /**
     * Footer center set
     *
     * @param none
     * @return string
     **/
    function SetFooterCenter($footcenter)
    {
        $this->footer_center = $footcenter;
    }

    /**
     * addpage function
     *
     * @param string
     * @param mixed
     * @param int
     * @return void
     **/

    function Add_Page($orientation = NULL, $size = NULL, $rotation = NULL)
    {
        if (is_null($orientation))
            $orientation = $this->orientation;
        else
            $this->orientation = $orientation;

        if (is_null($size))
            $size = $this->size;
        else
            $this->size = $size;

        if (is_null($rotation))
            $rotation = $this->rotation;
        else
            $this->rotation = $rotation;

        $this->AddPage($this->orientation, $this->size, $this->rotation);
    }

    /**
     * render function
     *
     * @param string
     * @param string
     * @param bool
     * @return void
     *
     * Behaviour:
     * dest,             indicates where send the documment. It can bo one of following
     *                   'I': send the file inline to the browser. The PDF viewer is used if available.
     *                   'D': send to the browser and force a file download with the name given by name.
     *                   'F': save to a local file with the name given by name (may include a path).
     *                   'S': return the document as a string.
     *
     * name,             The name of the file. It is ignored in case of destination S.
     *                   The default value is doc.pdf.
     *
     * $this->format,    Indicates if name is encoded in ISO-8859-1 (false) or UTF-8 (true).
     *                   Only used for destinations I and D.
     *                   The default value is false.
     **/
    function render($dest = 'I', $name = 'document.pdf')
    {
        $this->Output($dest, $name, $this->format);
    }


    /**
     * format function
     *
     * @param string
     * @return string
     **/
    function format($str)
    {
        return utf8_decode($str);
    }

    /**
     * imageprop function
     *
     * @param string
     * @param mixed
     * @param int
     * @return void
     **/

    function ImageProp($image, $x, $y, $w, $h)
    {
        list($width, $height) = getimagesize($image);

        // Calculando a proporção 
        $ratio_orig = $width / $height;

        $worig = $w;
        $horig = $h;

        if ($width >= $height) {
            $hn = $h + 10;
            while ($hn > $h) {
                $hn = $w / $ratio_orig;
                if ($hn > $h) {
                    $w = $w - 1;
                }
            }
            $h = $hn;
        } else {
            $wn = $w + 10;
            while ($wn > $w) {
                $wn = $h * $ratio_orig;
                if ($wn > $w) {
                    $h = $h - 1;
                }
            }
            $w = $wn;
        }
        // acha o centro
        $dif = $worig - $w;
        $x = $x + ($dif / 2);

        $dify = $horig - $h;
        $y = $y + ($dify / 2);

        $this->Image($image, $x, $y, $w, $h);

        $this->setY($y + $h + 1);
    }

    function EtiqTexto($etiq, $texto, $font, $tamfont, $h, $w, $border = 0, $ln = 0, $align = 'L', $preenche = 0, $negita = '')
    {
        $this->SetFont($font, 'B', $tamfont);
        $x = $this->GetX();
        if (strlen($etiq) > 0) {
            if (strlen($texto) > 0) {
                $alignetiq = 'L';
            } else {
                $alignetiq = $align;
            }
            if ($align == 'R') {
                $x = $x + (strlen(formata_texto($texto)) * 2) + 2;
                $this->SetX($x);
            }
            $this->Cell($w, $h, formata_texto($etiq), $border, 0, $alignetiq, $preenche);
            if ($w == 0) {
                $x = $x + (strlen(formata_texto($etiq)) * 2) + 2;
                $this->SetX($x);
            }
        }
        // $x += $w;
        $this->SetFont($font, $negita, $tamfont);
        if (strlen(formata_texto($texto)) > 0) {
            $this->Cell($w, $h, formata_texto($texto), $border, $ln, $align, $preenche);
            if ($w == 0  && $ln == 0) {
                $x = $x + (strlen(formata_texto($texto)) * 2) + 10;
                $this->SetX($x);
            }
        } else if ($ln > 0) {
            $this->Cell(0, $h, '', $border, $ln, $align, $preenche);
        }
        // debug($etiq.' '.$x);
        // if($ln > 0){
        //     $x = 10;
        // }
        // $this->SetX($x);
    }


    // Função que interpreta o HTML e escreve no PDF
    function WriteHTML($html)
    {
        // Remove quebras de linha
        $html = str_replace("\n", ' ', $html);
        // Separa o HTML em um array onde os elementos são textos ou tags
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Texto
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->Write(5, $e);
            } else {
                // Tag
                // Se for tag de fechamento
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    // Separa a tag dos atributos
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    // Lê os atributos, se existirem
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)["\']?/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    // Função para abrir uma tag e aplicar seus atributos/estilos
    function OpenTag($tag, $attr)
    {
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = isset($attr['HREF']) ? $attr['HREF'] : '';
        if ($tag == 'BR')
            $this->Ln(5);
    }

    // Função para fechar uma tag e retirar os estilos aplicados
    function CloseTag($tag)
    {
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';

        if ($tag == 'P') {
            $this->Ln(5);
        }
    }

    // Função para alterar o estilo de fonte (negrito, itálico, sublinhado)
    function SetStyle($tag, $enable)
    {
        // Atualiza o contador do estilo
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        if ($this->B > 0)
            $style .= 'B';
        if ($this->I > 0)
            $style .= 'I';
        if ($this->U > 0)
            $style .= 'U';
        $this->SetFont('', $style);
    }

    // Função para inserir um hyperlink
    function PutLink($URL, $txt)
    {
        // Cor azul para links
        $this->SetTextColor(0, 0, 255);
        // Sublinha o link
        $this->SetStyle('U', true);
        // Escreve o texto e cria o link
        $this->Write(5, $txt, $URL);
        // Restaura o estilo
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    function CheckPageBreak($h)
    {
        // Posição Y atual
        $y = $this->GetY();

        // Altura máxima da página (menos a margem inferior)
        $pageHeight = $this->h - $this->bMargin;

        // Se a altura do MultiCell ultrapassar a página, adiciona nova página
        if ($y + $h > $pageHeight) {
            $this->AddPage();
        }
    }

    function MultiCellSafe($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        // Calcula a altura do MultiCell antes de adicioná-lo
        $nbLines = $this->GetStringWidth($txt) / ($w - 2);
        if (ceil($nbLines) < 2) {
            $h = $h * 2;
        }
        $multiCellHeight = ceil($nbLines) * $h;

        // Verifica se cabe na página antes de adicionar
        $this->CheckPageBreak($multiCellHeight);

        // Agora adiciona o MultiCell
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
    }
}
