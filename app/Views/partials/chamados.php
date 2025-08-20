    <div id='content' class='container page-content bg-light m-0'>
        <?php
        $secoes[0] = 'Dados';
        $secoes[1] = 'Gráficos';
        echo "<div class='col-lg-12 col-md-12 d-lg-flex d-none'>";
        echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
        $active = 'active';
        for ($s = 0;$s < sizeof($secoes);$s++) {
            echo "<li class='nav-item ' role='presentation'>";
            $secao = url_amigavel($secoes[$s]);
            echo "<span id='".$secao."-valid' class='float-end valid-tab badge rounded-pill bg-danger d-none'>!</span>";
            echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'>";
            echo "<i class='far fa-hand-point-right'> </i> - ";
            echo $secoes[$s];
            echo "</button>";
            echo "</li>";
            $active = '';
        }
        echo "</ul>";
        echo "</div>";
        $active = 'show active';
        echo "<div class='tab-content bg-white' id='myTabContent'>";
        // for ($s = 0;$s < count($secoes);$s++) {
            // for ($s = 0; $s < count($secoes); $s++) {
                $secao = url_amigavel($secoes[0]);
                echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='" . $secao . "' role='tabpanel' 
                        aria-labelledby='" . $secao . "-tab' tabindex='0'>";
                ?>
                <table id="table" class="display compact table table-sm table-info table-striped table-hover table-borderless col-12">
                <thead class="table-default col-12">
                    <tr>
                    <?
                    for ($c = 0; $c < sizeof($colunas); $c++){
                        echo "<th class='text-center text-nowrap'><h5>$colunas[$c]</h5></th>";
                    }
                    ?>
                    </tr>
                </thead>
                <tbody>
                    <?
                    // debug($chamados);
                    if(count($chamados)> 0){
                        for ($c = 0; $c < count($chamados); $c++){
                            echo "<tr>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['id']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['unidade_nome']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['departamento_nome']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['assunto_nome']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['solicitante_nome']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['aberto']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['responsavel_nome']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['resolvido']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['concluido']}</td>";
                            echo "<td class='text-center text-nowrap'>{$chamados[$c]['descsituacao']}</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
                </table>
            <?
            echo "</div>";
            // }
        // }
        echo "</div>";
        ?>
    </div>
