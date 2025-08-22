<script src='https://cdn.jsdelivr.net/npm/apexcharts'></script>

    <div id='content' class='container page-content bg-light m-0'>
        <?php
        $secoes[0] = 'Gráficos';
        $secoes[1] = 'Dados';
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
            $active = "";
        }
        echo "</ul>";
        echo "</div>";
        $active = "show active";
        echo "<div class='tab-content bg-white' id='myTabContent'>";
                $secao = url_amigavel($secoes[0]);
                echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='" . $secao . "' role='tabpanel' aria-labelledby='" . $secao . "-tab' tabindex='0'>";
                    $cores = ['bg-primary', 'bg-info', 'bg-danger', 'bg-white', 'bg-warning', 'bg-gradiente bg-dark text-white'];
                    $i = 0;

                    foreach ($scores as $key => $value) {
                        $cor = $cores[$i % count($cores)];
                        echo "<div class='col-2 float-start border border-3 border-white text-center {$cor}'>";
                        echo $key;
                        echo "<h2>{$value}</h2>";
                        echo "</div>";
                        $i++;
                    }
                    echo "<div id='graphs' class='col-12 float-start'>";
                    echo "<div class='grid' style='display:grid;gap:1rem;grid-template-columns:1fr;'>
                            <div class='chart-box mb-5 pb-4 border border-2' style='position: relative; height:40vh; max-height:40vh; width:60vw'>
                                <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;'>
                                <h3 style='margin:0;'> Abertos x Resolvidos por Unidade</h3>
                                <button id='openUnidade' class='btn btn-outline-info'>Abrir em nova janela</button>
                                </div>
                                <canvas id='chartUnidade' height='360'></canvas>
                            </div>
                            <div class='chart-box mb-5 pb-4 border border-2' style='position: relative; height:40vh; max-height:40vh; width:60vw'>
                                <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;'>
                                <h3 style='margin:0;'> Abertos x Resolvidos por Responsável</h3>
                                <button id='openResponsavel' class='btn btn-outline-info'>Abrir em nova janela</button>
                                </div>
                                <canvas id='chartResponsavel' height='360'></canvas>
                            </div>
                            <div class='chart-box mb-5 pb-4 border border-2' style='position: relative; height:40vh; max-height:40vh; width:60vw'>
                                <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;'>
                                <h3 style='margin:0;'> Abertos x Resolvidos por Assunto</h3>
                                <button id='openAssunto' class='btn btn-outline-info'>Abrir em nova janela</button>
                                </div>
                                <canvas id='chartAssunto' height='360'></canvas>
                            </div>
                            </div>
                            </div>
                            </div>
                            ";
                $secao = url_amigavel($secoes[1]);
                echo "<div class='tab-pane fade p-lg-3 p-2' id='" . $secao . "' role='tabpanel' aria-labelledby='" . $secao . "-tab' tabindex='0'>";
                ?>
                <table id='table' class='table table-sm table-info table-striped table-hover table-borderless'>
                <thead class='table-default'>
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
                            $link = "<a href='https://taisho.sults.com.br/chamados/interacoes/".$chamados[$c]['id']."' target='_blank'>{$chamados[$c]['id']}</a>";
                            echo "<tr>";
                            echo "<td class='text-nowrap'>{$link}</td>";
                            for ($cp=1; $cp < count($campos); $cp++) { 
                                $info = $chamados[$c][$campos[$cp]];
                                $info = isValidDate($info, 'Y-m-d H:i:s')?dataDbToBr($info):$info;
                                echo "<td class='text-nowrap'>{$info}</td>";
                            }
                            echo '</tr>';
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
