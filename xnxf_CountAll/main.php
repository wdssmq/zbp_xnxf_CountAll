<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('xnxf_CountAll')) {
  $zbp->ShowError(48);
  die();
}
InstallPlugin_xnxf_CountAll();

$blogtitle = '「水水」强制更新各种文章统计';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
    <a href="main.php"><span class="m-left m-now">首页</span></a>
    <?php require "about.php"; ?>
  </div>
  <div id="divMain2">

  </div>
</div>
<script>

</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
