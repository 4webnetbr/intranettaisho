<?
    $txt = "<div class='col-12 bg-white'>";
    $txt .= "<div class='row p-0 m-0'>"; // abre a linha
    $primeiralinha = true;
    for ($i = 0; $i < count($indica); $i++) {
        if (substr($indica[$i], 0,1) != '%') {
            if(!$primeiralinha){
                $txt .= "</div>"; // fecha o card
            }
            $primeiralinha = false;
            if (substr($indica[$i], -1) === ' ') {
                $txt .= "</div>"; // fecha a linha
                $txt .= "<div class='row p-0 m-0'>"; // abre a linha
                $txt .= "<div class='col-3 float-start border border-3 border-black text-center p-2 m-2 {$cores[$i]}'>"; // abre o card
            } else {
                // $txt .= "</div>";
                $txt .= "<div class='col-3 float-start border border-3 border-black text-center p-2 m-2 {$cores[$i]}'>"; // abre o card
            }
        } else {
            $txt .= "<br>";
        }
        $txt .= "<h1 class='fw-bold p-0 m-0'>{$valores[$i]}</h1>";
        $txt .= "<h3>{$indica[$i]}</h3>";
    }
    $txt .= "</div>"; // fecha o último card
    $txt .= "</div>"; // fecha a última linha
    $txt .= "</div>"; // fecha a div principal
    echo $txt;
?>
