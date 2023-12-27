<!-- Section Menu -->
<?=$this->section('footer');
if (isset($log['operacao']) && $log['operacao'] != '') {
?>
  <div id='rodape' class='footer bg-light-subtle col-12 text-center position-fixed bottom-0'>
        <?=$log['operacao'];?>: <?=$log['data_alterou'];?> por: <?=$log['usua_alterou'];?>
        <a href='<?=base_url('Logger/show/' . $log['tabela'] . '/' . $log['registro']);?>'>Ver Log</a>
  </div>
<?
}?>
<?=$this->endSection();?>
