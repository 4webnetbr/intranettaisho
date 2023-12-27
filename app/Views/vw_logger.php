<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
  <div id='content' class='container page-content bg-light m-0 overflow-auto'>
    <?php
      for ($l = 0; $l < count($dados); $l++) {
        $dad = $dados[$l];
    ?>
        <div class="card col-4 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Data</h5></div>
          <div class="card-body">
            <?=$dad['data_alterou'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Operação</h5></div>
          <div class="card-body">
            <?=$dad['operacao'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Usuário</h5></div>
          <div class="card-body">
            <?=$dad['usua_alterou'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Tabela</h5></div>
          <div class="card-body">
            <?=$dad['tabela'];?>
          </div>
        </div>
        <div class="card col-2 float-start d-inline">
          <div class="card-header bg-info-subtle"><h5>Registro</h5></div>
          <div class="card-body">
            <?=$dad['registro'];?>
          </div>
        </div>
        <div class="card col-12 float-start d-block mb-5">
          <div class="card-header bg-info-subtle"><h5>Informações Salvas na Operação</h5></div>
          <div class="card-body">
        <?php
          foreach ($dad['dados'][0] as $key => $value) {
              echo "<b>" . $key . "</b>" . " = " . $value . "<br>";
          }
        ?>
          </div>
        </div>
    <?php
      }
      // debug($dados);
    ?>
  </div>
<?=$this->endSection();?>
