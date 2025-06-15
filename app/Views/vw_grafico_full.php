<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Gráfico em Tela Cheia</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        canvas {
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>

    <!-- 1) jQuery (com SRI válido) -->
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>

    <!-- 2) Chart.js e plugin DataLabels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <!-- 3) Sua biblioteca de gráficos v2 -->
    <script src="<?= base_url('assets/jscript/my_mask.js') ?>"></script>
    <script src="<?= base_url('assets/jscript/my_graficv2.js') ?>"></script>
</head>

<body>
    <?php
    // Monta o atributo data-cols a partir das chaves do primeiro registro
    $cols = '';
    if (! empty($dados) && is_array($dados) && isset($dados[0]) && is_array($dados[0])) {
        $keys = array_keys($dados[0]);
        $cols = implode(',', $keys) . ',';
    }
    ?>
    <canvas
        id="gr_full"
        data-cols="<?= esc($cols) ?>"></canvas>

    <script>
        jQuery(function() {
            // Dados vindos do controller
            var valores = <?= json_encode($dados, JSON_UNESCAPED_UNICODE) ?>;
            var cores = <?= json_encode($cores) ?>;
            var tipo = '<?= esc($tipo) ?>';

            // Monta o gráfico no canvas de id "gr_full"
            montaGrafico('full', valores, tipo, cores);
        });
    </script>
</body>

</html>