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
$action = GetVars('act', 'GET');
$p = (int) GetVars('page', 'GET');
if (!empty($action)) {
  CheckIsRefererValid();
}
switch ($action) {
  case 'Attachs':
    xnxf_CountAll_CheckAttach($p);
    break;
  case 'Tags':
    xnxf_CountAll_ByTag($p);
    break;
  case 'Other':
    xnxf_CountAll_Other($p);
    break;
  default:
    $zbp->StartSession();
    $dataUsr = xnxf_CountAll_ByUsr(GetVars('act', 'GET') == "run");
    $dataCate = xnxf_CountAll_ByCate(GetVars('act', 'GET') == "run");
    break;
}
$blogtitle = '统计更新';
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
    <style>
      table {
        width: 45%;
      }
      .result span {
        min-width: 12%;
        max-width: 25%;
        display: block;
        float: left;
      }
    </style>
    <div class="result">
      <table class="tableBorder">
        <tbody><?php xnxf_CountAll_Echo($dataUsr); ?></tbody>
      </table>
      -----
      <table class="tableBorder">
        <?php xnxf_CountAll_Echo($dataCate); ?>
      </table>
    </div>
    <div style="clear:both;"></div>
    <form id="form" action="<?php echo BuildSafeURL("main.php") ?>" method="get" id="form">
      <input type="hidden" value="run" name="act">
      <input type="hidden" value="<?php echo $zbp->GetCSRFToken();?>" name="csrfToken">
      <input type="submit" value="统计文章" class="button">
      <input type="button" value="统计附件" class="button js-Attachs">
      <input type="button" value="统计Tags" class="button js-Tags">
      <input type="button" value="其他" class="button js-Other">
      <a href="main.php">刷新</a>
    </form>
  </div>
</div>
<script>
  $(function() {
    $(".js-Attachs").click(function() {
      $(".result").html("<p>未在<b>文章或页面</b>中引用的附件（含空文件夹）</p>");
      fnAjax(0, "Attachs");
    });
    $(".js-Tags").click(function() {
      $(".result").html("");
      fnAjax(0, "Tags");
    });
    $(".js-Other").click(function() {
      $(".result").html("");
      fnAjax(0, "Other");
    });
    // $(".js-countAttachs").click();
    function fnAjax(page, act) {
      $.ajax({
        url: zbp.options.bloghost + "zb_users/plugin/xnxf_CountAll/main.php?act=" + act + "&page=" + page + "&csrfToken=" + $("meta[name=csrfToken]").attr("content") ,
        type: "get",
        success: function(result) {
          if (act == "Attachs") {
            const data = result.data
            console.log(data.files);
            for (let item in data.files) {
              $(".result").append('<a href="-url-" target="_blank" title="-url-">-url-</a><br />'.replace(/-url-/g, data["files"][item]));
              if (data["files"][item].substr(-1, 1) === "/") {
                $(".result").append("----<br>");
              }
            }
            if (data.more) {
              fnAjax(page + 1, act);
            } else {
              $(".result").append(`<h3>未使用文件将在附件管理中标红：<a target="_blank" title="转到附件管理" href="${zbp.options.bloghost}zb_system/admin/index.php?act=UploadMng">转到附件管理</a></h3>`);
            }
          } else {
            $(".result").append(result);
            if (/#echo/.test(result)) {
              fnAjax(page + 1, act);
              $(".result").append("<div style=\"clear:both;\">---运行中---</div>");
            } else {
              $(".result").append("<div style=\"clear:both;\">---完成---</div>");
            }
          }
          $('html, body').animate({
            scrollTop: $("#form").offset().top
          }, 300);
        }
      });
    }
  });
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>