<?
    $txt = "<div class='col-12 bg-white'>";
    $txt .= "<div class='row p-0 m-0'>";
    for ($i = 0; $i < count($indica); $i++) {
        $corIndex = $i % count($cores);// reinicia o índice ao atingir o fim do array $cores
        if (substr($indica[$i], -1) === ' ') {
            $txt .= "</div>";
            $txt .= "<div class='row p-0 m-0'>";
        }
        $txt .= "<div class='col-2 float-start border border-3 border-black text-center p-2 m-2 {$cores[$corIndex]}'>";
        $txt .= "<h1 class='fw-bold p-0 m-0'>{$valores[$i]}</h1>";
        $txt .= "<br>";
        $txt .= "<h3>{$indica[$i]}</h3>";
        $txt .= "</div>";
    }
    $txt .= "</div>";
    $txt .= "</div>";
    echo $txt;
?>
