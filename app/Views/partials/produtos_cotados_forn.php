    <div class="accordion-body p-1" style="max-height:50vh; height:50vh; overflow-y: auto">
        <div class="d-block float-start col-12 p-0">
            <div class="d-inline-flex float-start col-3 fw-bold text-center">Fornecedor</div>
            <div class="d-inline-flex float-start col-2 fw-bold text-center">Marca:</div>
            <div class="d-inline-flex float-start col-1 fw-bold text-center">Validade</div>
            <div class="d-inline-flex float-start col-1 fw-bold text-center">R$ <?= $produtos['und_consumo'] ?></div>
            <div class="d-inline-flex float-start col-1 fw-bold text-center">R$ <?= $produtos['und_compra'] ?></div>
            <div class="d-inline-flex float-start col-1 fw-bold text-center">Quantia</div>
            <div class="d-inline-flex float-start col-2 fw-bold text-center">Prev. Entrega</div>
        </div>
        <div class="d-block float-start col-12 p-0" style="max-height:45vh; overflow-y: auto">
            <?php for ($i = 1; $i <= 10; $i++): ?>
            <div class="d-block float-start col-12 p-0">
                <div class="d-inline-flex float-start col-3"><?= $produtos["pro_id_$i"].$produtos["ped_id_$i"].$produtos["cot_id_$i"].$produtos["cop_id_$i"].$produtos["for_id_$i"]?></div>
                <div class="d-inline-flex float-start col-2"><?= $produtos["mar_id_$i"]?></div>
                <div class="d-inline-flex float-start col-1 me-3"><?= $produtos["cof_id_$i"].$produtos["cof_validade_$i"]?></div>
                <div class="d-inline-flex float-start col-1 me-3"><?= $produtos["cof_preco_$i"]?></div>
                <div class="d-inline-flex float-start col-1 me-3"><?= $produtos["cof_precoundcompra_$i"]?></div>
                <div class="d-inline-flex float-start col-1 me-3"><?= $produtos["com_quantia_$i"]?></div>
                <div class="d-inline-flex float-start col-2"><?= $produtos["cop_previsao_$i"]?></div>
                <?php if (!empty($produtos["cof_observacao_$i"])): ?>
                <div class="d-inline-flex float-start col-12 px-5 mb-3">Obs.: <?= $produtos["cof_observacao_$i"] ?></div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
