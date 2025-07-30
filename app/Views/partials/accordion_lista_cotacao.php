<?php foreach ($produtos as $produto): ?>
  <div class="accordion-item">
    <h2 class="accordion-header border border-bottom-1" id="headpro<?= $produto['pro_id'] ?>">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#collaProd<?= $produto['pro_id'] ?>" aria-expanded="false"
              aria-controls="collaProd<?= $produto['pro_id'] ?>" data-proid="<?= $produto['pro_id'] ?>" onclick="posicionaProdutoTopo(this)">
        <div class="col-2"><b>Grupo</b><br><?= $produto['grc_nome'] ?></div>
        <div class="col-4"><b>Produto</b><br><?= $produto['pro_nome'] ?></div>
        <div class="col-2"><b>Data Solic</b><br><?= $produto['ped_datains'] ?></div>
        <div class="col-1"><b>Quantia</b><br><?= $produto['ped_qtia'] ?></div>
        <div class="col-1"><b>Sugestão</b><br><?= $produto['ped_sugestao'] ?></div>
        <div class="col-2"><b>Und.</b><br><?= $produto['und_compra'] ?></div>
      </button>
    </h2>
    <div id="collaProd<?= $produto['pro_id'] ?>" class="accordion-collapse collapse" aria-labelledby="headpro<?= $produto['pro_id'] ?>" data-bs-parent="#accProdutos">
    </div>
  </div>
<?php endforeach; ?>
