<?php
namespace App\Libraries;

use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeGen {
    protected $generator;

    public function __construct() {
        $this->generator = new BarcodeGeneratorPNG();
    }

    public function generate($text) {
        return $this->generator->getBarcode($text, $this->generator::TYPE_CODE_128);
    }
}
