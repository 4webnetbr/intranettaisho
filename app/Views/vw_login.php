<?=$this->extend('templates/login_template')?>

<?=$this->section('content');?>
<div class="container text-start col-12 justify-content-center float-none align-items-center d-flex" style="max-height:90vh!important; height:90vh!important;">
  	<div class="row justify-content-center" style="max-height:50vh!important; height:50vh!important;">
    	<div class="col col-12 bg-login align-items-center p-0">
			<div class="col-7 float-start h-100 align-items-center d-flex" style='border: 0;border-right: 2px solid;'>
				<img src='<?=$logo;?>' width='90%' />
			</div>
			<!-- <div class="row justify-content-center"> -->
				<div class="col-5 text-center p-4 mt-4 bg-white float-start d-flex">
					<form method="post" type="normal" action="<?= site_url($destino) ?>" class=""> 
						<div class="card-body">
							<h3 class="card-title text-start mb-4 text-start">Identificação</h3>
							<?php
							for($c=0;$c<sizeof($campos);$c++){
								echo $campos[$c];
							}
							?>
							<br>
							<span style='color:red;font-size:15px;'><?= session('msg');?></span>
						</div>
					</form>    
				</div>
			<!-- </div> -->
		</div>
	</div>
</div>
<!-- <script src="<?//=base_url('assets/jscript/my_fields.js');?>"></script> -->

<?=$this->endSection();?>
