<?php foreach ($produtos as $produto): ?>
  <div class="accordion-item">
    <h2 class="accordion-header border border-bottom-1" id="heading<?= $produto['pro_id'] ?>">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapse<?= $produto['pro_id'] ?>" aria-expanded="false"
              aria-controls="collapse<?= $produto['pro_id'] ?>" onclick="posicionaProdutoTopo(this)">
        <div class="col-2"><b>Grupo</b><br><?= $produto['grc_nome'] ?></div>
        <div class="col-4"><b>Produto</b><br><?= $produto['pro_nome'] ?></div>
        <div class="col-2"><b>Data Solic</b><br><?= $produto['ped_datains'] ?></div>
        <div class="col-1"><b>Quantia</b><br><?= $produto['ped_qtia'] ?></div>
        <div class="col-1"><b>Sugestão</b><br><?= $produto['ped_sugestao'] ?></div>
        <div class="col-2"><b>Und.</b><br><?= $produto['und_compra'] ?></div>
      </button>
    </h2>
    <div id="collapse<?= $produto['pro_id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $produto['pro_id'] ?>" data-bs-parent="#accProdutos">
      <div class="accordion-body p-1" style="max-height:50vh; height:50vh; overflow-y: auto">
        <div class="d-block float-start col-12 p-0">
          <div class="d-inline-flex float-start col-3 fw-bold text-center">Fornecedor</div>
          <div class="d-inline-flex float-start col-2 fw-bold text-center">Marca:</div>
          <div class="d-inline-flex float-start col-1 fw-bold text-center">Validade</div>
          <div class="d-inline-flex float-start col-1 fw-bold text-center">R$ <?= $produto['und_consumo'] ?></div>
          <div class="d-inline-flex float-start col-1 fw-bold text-center">R$ <?= $produto['und_compra'] ?></div>
          <div class="d-inline-flex float-start col-1 fw-bold text-center">Quantia</div>
          <div class="d-inline-flex float-start col-2 fw-bold text-center">Prev. Entrega</div>
        </div>
        <div class="d-block float-start col-12 p-0" style="max-height:45vh; overflow-y: auto">
          <?php for ($i = 1; $i <= 10; $i++): ?>
            <div class="d-block float-start col-12 p-0">
              <div class="d-inline-flex float-start col-3"><?= $produto["pro_id_$i"].$produto["ped_id_$i"].$produto["cot_id_$i"].$produto["cop_id_$i"].$produto["for_id_$i"]?></div>
              <div class="d-inline-flex float-start col-2"><?= $produto["mar_id_$i"]?></div>
              <div class="d-inline-flex float-start col-1 me-3"><?= $produto["cof_id_$i"].$produto["cof_validade_$i"]?></div>
              <div class="d-inline-flex float-start col-1 me-3"><?= $produto["cof_preco_$i"]?></div>
              <div class="d-inline-flex float-start col-1 me-3"><?= $produto["cof_precoundcompra_$i"]?></div>
              <div class="d-inline-flex float-start col-1 me-3"><?= $produto["com_quantia_$i"]?></div>
              <div class="d-inline-flex float-start col-2"><?= $produto["cop_previsao_$i"]?></div>
              <?php if (!empty($produto["cof_observacao_$i"])): ?>
                <div class="d-inline-flex float-start col-12 px-5 mb-3">Obs.: <?= $produto["cof_observacao_$i"] ?></div>
              <?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
