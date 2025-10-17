<?
    $txt = "<div class='col-12 bg-white'>";
    for ($i = 0; $i < count($indica); $i++) {
        $txt .= "<div class='col-2 float-start border border-3 border-black text-center p-2 m-2 {$cores[$i]}'>";
        $txt .= "<h3>{$indica[$i]}</h3>";
        $txt .= "<br>";
        $txt .= "<h1 class='fw-bold'>{$valores[$i]}</h1>";
        $txt .= "</div>";
    }
    $txt .= "</div>";
    echo $txt;
?>
