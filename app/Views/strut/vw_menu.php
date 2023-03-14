<!-- Section Menu -->
<?=$this->section('menu');?>
<?php
  $url_sair = base_url('/login');
  $avatar = session()->get('usu_avatar'); 
  $nomeus = session()->get('usu_nome'); 
  if($avatar != ''){
    $image_avatar = "<img src='$avatar' class='img-user rounded-circle me-3 float-start' />";
  } else {
    $image_avatar = "<i class='far fa-laugh-wink float-start'></i> ";
  }
  // echo $image_avatar; 
?>
<div id='show_user' class="card col-lg-2 col-6 border border-1 border-info shadow p-3 me-1 float-start" >
  <div class="card-header">
  <?php
    if($avatar != ''){
        $image_user = "<img src='$avatar' class='rounded-circle me-3 card-img-top float-start'/>";
    } else {
        $image_user = "<i class='far fa-laugh-wink float-start'></i> ";
    }
    echo $image_user;
  ?>
  </div>
  <div class="card-body">
    <h5 class="card-title"><?=$nomeus;?></h5>
    <p class="card-text"><?=session()->get('usu_perfil');?></p>
    <?php 
      echo "<hr>";
      echo anchor('AdmUsuario/edit_senha', 'Alterar Senha');
      echo "<hr>";
      echo anchor(base_url('/login'), '<i class="fas fa-sign-out-alt"></i> - Sair');
    ?>
  </div>
</div>

<!-- Vertical navbar -->
<div class="col-lg-2 col-8 position-absolute bg-white shadow sidebar" id="sidebar">
  <div class="pt-2 pb-3 ps-1 pe-1">
    <div id='sistema' class='text-start p-2 m-4'>
      <a href="<?=site_url();?>" >
        <img src='<?=session()->logo.'?noc='.time();?>' class="logo-menu border" />
      </a>
    </div>
    <div id='div_user' class='div-user text-start w-100' style='height: 4rem'>
      <button name="bt_user" type="button" id="bt_user" class="bt_user btn btn-outline-info px-2 w-100 text-start float-start" >
        <?=$image_avatar;?>
        <div class="usu_nome txt-bt-manut">
            <?=$nomeus;?>
        </div>
      </button>    
    </div>

    <div class="accordion" id="accordionMenu">
      <?php 
        $modu = '';
        for ($m=0; $m < count($it_menu); $m++) { 
          $item = $it_menu[$m];
          if($modu != $item['mod_nome'] ){
            if($modu != ''){
              echo "</div>";
              echo "</div>";
              echo "</div>";
            }
            $modu = $item['mod_nome'];
          ?>
            <div class="accordion-item">
              <h2 class="accordion-header" id="head<?=$modu;?>">
                <button class="accordion-button px-1 pt-2 pb-1 collapsed text-primary-emphasis" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$m;?>" aria-expanded="false" aria-controls="collapse<?=$m;?>">
                  <div class='align-items-center border border-primary rounded-circle p-0 text-center float-start font-weight-bold' style=''>
                      <div class='icon-menu'><i class='<?=$item['mod_icone'];?>'></i></div>
                  </div>
                  <div class='align-items-start ms-2 mod_nome'>
                      <?=$item['mod_nome'];?>
                  </div>
                </button>
              </h2>
              <div id="collapse<?=$m;?>" class="accordion-collapse collapse" aria-labelledby="head<?=$modu;?>" data-bs-parent="#accordionMenu">
                <div class="accordion-body ms-2 py-1 px-0 h-auto bg-blue-claro">
          <?
          }
          if($item['men_metodo'] != 'index'){
            $item['clas_controler'] = $item['clas_controler'].'/'.$item['men_metodo'];
          }
          ?>
            <div id="<?=strtolower($item['clas_controler']);?>" class='nav-dropdown-menu mt-0 ms-1'>
              <a id="<?=$item['men_id'];?>" href="<?=base_url($item['clas_controler']);?>" class="">
                <div class='align-items-center border border-primary rounded-circle p-0 me-2 mt-1 text-center float-start'  style='width:1.5rem'>
                  <div style=""><?=$item['men_icone'];?></div>
                </div>
                <div class='align-items-start ms-2 men_nome py-1'><?=$item['men_nome'];?></div>
              </a>
            </div>
        <?
        }
        ?>
          </div>
        </div>
      </div>
    </div>
    <div id='div_sair' class='div-user text-start w-100 position-absolute bottom-0 pe-2' style='height: 4rem'>
      <button name="bt_sair" type="button" id="bt_sair" class="bt_user btn btn-outline-dark px-2 py-0 w-100 text-start float-start"  data-mdb-toggle="tooltip" data-mdb-placement="top" title="Sair do Sistema" onclick="redireciona('<?=$url_sair;?>')">
        <div class='icon-menu float-start me-2'><i class="fas fa-sign-out-alt" aria-hidden="true"></i></div>
        <div class="usu_nome txt-bt-manut ms-1 float-start">
            Sair
        </div>
      </button>    
    </div>
  </div>
</div>

<script>
jQuery(document).ready(function() {
  jQuery(".nav-dropdown-menu").each(function( index ) {
    if(jQuery(this)[0].id == <?="'".strtolower($controler)."'";?>){
      jQuery(this).addClass('active');
      jQuery(this).parent("div").parent("div").addClass('show');
    }
  });

});
</script>
<?=$this->endSection();?>
