<?=$this->extend('templates/login_template')?>

<?=$this->section('content');?>
<div class="container text-center col-lg-6 col-10">
  	<div class="row justify-content-center h-100">
    	<div class="col col-lg-8 col-12 mt-5 bg-login align-middle p-0">
			<div class="col-12 bg-white">
				<img src='<?=$logo;?>' width='70%' />
			</div>
			<div class="row justify-content-center">
				<div class="col-8 text-center p-4">
					<form method="post" type="normal" action="<?= site_url($destino) ?>" class=""> 
						<div class="card-body">
							<h5 class="card-title text-center mb-3">Identificação</h5>
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
			</div>
		</div>
	</div>
</div>
<?=$this->endSection();?>
