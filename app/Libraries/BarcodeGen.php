<?php namespace App\Libraries;

use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeGen 
{
    protected $generatorPNG;
    protected $generatorHTML;

    public function __construct() {
        $this->generatorPNG = new BarcodeGeneratorPNG();
        $this->generatorHTML = new BarcodeGeneratorHTML();
    }

    public function generatePNG($text) {
        return $this->generatorPNG->getBarcode($text, $this->generatorPNG::TYPE_EAN_13);
    }
    public function generateHTML($text) {
        return $this->generatorHTML->getBarcode($text, $this->generatorHTML::TYPE_EAN_13);
    }

    public function saveBarcode($data, $path)
    {
        $barcodeImage = $this->generatePNG($data);
        file_put_contents($path, $barcodeImage);
        return $path;
    }    
}
