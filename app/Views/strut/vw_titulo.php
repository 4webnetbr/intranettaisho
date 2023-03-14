<!-- Section Menu -->
<?=$this->section('titulo');
if(!isset($desc_metodo)){
  if($metodo == 'index'|| $metodo == ''){
    $desc_metodo = '';
  } else if($metodo == 'add'){
    $desc_metodo = 'Cadastro de ';
  } else if($metodo == 'edit'){
    $desc_metodo = 'Alteração de ';
  } else if($metodo == 'show'){
    $desc_metodo = 'Consulta de ';
  }
}
?>
<div id='title' class='title col-12 px-lg-4 px-1 bg-light border-1 border-bottom'>
  <div class='col-lg-6 col-7 float-start text-nowrap'>
    <h4 class='d-inline-flex'  style='font-size: calc(1.275rem + 1.1vw)'>
      <?="<i class='".$icone."'></i> <span class='ms-4' style='font-size:calc(1.275rem + 0.3vw);line-height: 2.5rem'>".$desc_metodo." ".$title.'</span>';?>
    </h4>
  </div>
  <div class='col-lg-5 col-4 float-start text-right'>
  <?
    // VERIFICA O MÉTODO E AS PERMISSÕES PARA MOSTRAR OS BOTÕES
    // echo $metodo;
    if($metodo == 'index'|| $metodo == ''){
      if(strlen($bt_add) > 2 && strpbrk($permissao, 'A')){?>
        <button class="btn btn-outline-primary bt-manut btn-sm mb-2 float-end" 
            data-mdb-toggle="tooltip"
            data-mdb-placement="top" 
            title="Novo Registro" 
            onclick="redireciona('<?=$controler;?>/add/')">
            <div class="align-items-center py-1 text-start float-start font-weight-bold" style="">            
              <i class="fa fa-circle-plus btn-info" style="font-size: 2rem;"></i>
            </div>
            <div class="align-items-start mx-4 txt-bt-manut d-none">
              <?=$bt_add;?>
            </div>
        </button>
  <?  }
    } else if($metodo == 'filtro' || $metodo == 'show' ){?>
        <button class="btn btn-outline-info bt-manut btn-sm mb-2 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          title="Voltar" 
          onclick="redireciona('<?=base_url($controler);?>')">
          <div class="align-items-center py-1 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-arrow-left" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start mx-4 txt-bt-manut d-none">
            Voltar
          </div>
        </button>
  <?
    } else if((strpbrk($permissao, 'A') || strpbrk($permissao, 'E')) && $erromsg == ''){?>
        <button class="btn btn-outline-secondary bt-manut btn-sm mb-2 ms-1 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          data-bs-original-title="Cancelar Edição"
          title="Cancelar" 
          onclick="retorna_url()">
          <div class="align-items-center py-1 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-undo" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start mx-4 txt-bt-manut d-none ">
            Cancelar
          </div>
        </button>
        <button class="btn btn-outline-success bt-manut btn-sm mb-2 float-end" 
          data-mdb-toggle="tooltip"
          data-mdb-placement="top" 
          form="form1" 
          data-bs-original-title="Salvar os Dados"
          title="Salvar" 
          type="submit">
          <div class="align-items-center py-1 text-start float-start font-weight-bold" style="">            
            <i class="fas fa-save" style="font-size: 2rem;"></i>
          </div>
          <div class="align-items-start mx-4 txt-bt-manut d-none">
            Salvar
          </div>
        </button>
  <? }?>
  </div>
  <div class='col-lg-1 col-1 float-end text-nowrap'>
    <!-- <div class="align-items-center py-1 text-center float-end" style=""> -->
      <button type="button" class="btn btn-outline-secondary disabled float-end position-relative" tooltip='Notificações'>
        <i class="fa-regular fa-bell" style='font-size: 2rem !important' >
        </i>
        <span class="position-absolute top- start-100 translate-middle badge rounded-pill bg-info d-none">
          <span class="visually-hidden">Mensagens</span>
        </span>
      </button>
    <!-- </div> -->
  </div>
</div>
<?=$this->endSection();?>
