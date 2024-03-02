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

$act = GetVars('act', 'GET');

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
    <?php if (!$zbp->CheckPlugin('zbpWCm')) { ?>
      <p>请安装并启用 zbpWCm 插件！！</p>
      <p>前端组件管理 - Z-Blog 应用中心</p>
      <p><a href="<?php echo $zbp->host . 'zb_users/plugin/AppCentre/main.php?id=16847' ?>" target="_blank" title="前端组件管理 - Z-Blog 应用中心"><?php echo $zbp->host . 'zb_users/plugin/AppCentre/main.php?id=16847' ?></a></p>
    <?php } else { ?>
      <?php include "main_tabs/def.php"; ?>
    <?php } ?>
    <hr>
    <form id="xnxf-form" action="<?php echo BuildSafeURL("main.php") ?>" method="get">
      <input type="hidden" value="none" name="act">
      <input type="hidden" value="<?php echo $zbp->GetCSRFToken(); ?>" name="csrfToken">
      <input type="button" value="用户及分类" data-act="CountDef" class="button">
      <!-- <input type="button" value="统计附件" class="button"> -->
      <!-- <input type="button" value="统计Tags" class="button"> -->
      <!-- <input type="button" value="其他" class="button"> -->
      ← 点击按钮更新；
      <a href="main.php">刷新</a>
    </form>
  </div>
</div>
<script>
  $(function() {
    $("#xnxf-form input[type=button]").click(function() {
      const act = $(this).data("act");
      if (act) {
        $("#xnxf-form input[name=act]").val(act);
        $("#xnxf-form").submit();
      }
    });
  });
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
