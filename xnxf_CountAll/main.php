<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('xnxf_CountAll')) {$zbp->ShowError(48);die();}

$blogtitle='统计更新';

function fnCountUsr(&$arrNums, $r = null)
{
  global $zbp;
  $zbp->LoadMembers();
  $arrID = array();
  foreach ($zbp->members as $User) {
    $arrID[] = $User->ID;
    if (!isset($arrNums[$User->ID])) {
      $arrNums[$User->ID][] = '更新前';
    } else {
      $arrNums[$User->ID][] = '更新后';
    }
    $arrNums[$User->ID][] = $User->Name;
    $arrNums[$User->ID][] = $User->Articles;
    $arrNums[$User->ID][] = $User->Pages;
    $arrNums[$User->ID][] = $User->Comments;
    $arrNums[$User->ID][] = $User->Uploads;
    if ($r === true) {
      $arrNums[$User->ID][] = 'br';
    }
  }
  if ($r === true) {
    CountMemberArray($arrID, array(
      null,
      null,
      null,
      null
    ));
    fnCountUsr($arrNums, null);
  }
}

function fnCountCat(&$arrNums, $r = null)
{
  global $zbp;
  $zbp->LoadCategorys();
  $arrID = array();
  foreach ($zbp->categories as $Cate) {
    if (!isset($arrNums[$Cate->ID])) {
      $arrNums[$Cate->ID][] = '更新前';
    } else {
      $arrNums[$Cate->ID][] = '更新后';
    }
    $arrID[]              = $Cate->ID;
    $arrNums[$Cate->ID][] =  $Cate->Name;
    $arrNums[$Cate->ID][] =  $Cate->Count;
    if ($r === true) {
      $arrNums[$Cate->ID][] = 'br';
    }
  }
  if ($r === true) {
    CountCategoryArray($arrID, null);
    fnCountCat($arrNums, null);
  }
}

function fnEcho($arr)
{
  foreach ($arr as $item) {
    if (is_array($item)) {
      echo "<tr>";
      fnEcho($item);
      echo "</tr>";
    } else {
      if ($item !== "br"){
        echo "<td>$item</td>";
      }else{
        echo "</tr><tr>";
      }
    }
  }
}

function fnCountAttach(&$arrFiles,$dir){
  global $zbp;
  foreach(GetFilesInDir($dir,$zbp->option['ZC_UPLOAD_FILETYPE']) as $file){
    $arrFiles [] = str_replace(ZBP_PATH,"",$file);
  }
  $dirSub = GetDirsInDir($dir);
  if (count($dirSub) > 0){
    foreach($dirSub as $d) {
      fnCountAttach($arrFiles, "$dir$d/");
    }
  }
}

function fnCheckAttach($page = 1) {
  global $zbp;
  $files = array();
  $filesJSON = ZBP_PATH . 'zb_users/cache/OOYOG7.json';

  if (!file_exists($filesJSON)){
    $dirUp = ZBP_PATH . 'zb_users/upload/';
    fnCountAttach($files,$dirUp);
    file_put_contents($filesJSON,json_encode($files));
  } else {
    $files = json_decode(file_get_contents($filesJSON));
  }
  $objResponse = json_decode('{"more":0,"files":[]}');
  if (count($files) == 0)
    return json_encode($objResponse);
  // var_dump($files);
  // var_dump($objResponse);
  $w = array();
  $step = 20;
  $start = $page * $step;
  for ($k = $start;$k <= $start + $step - 1;$k++) {
    $w = array();
    $w[] = array('<>', 'log_Type', '1024');
    $w[] = array('search', 'log_Content', 'log_Intro', 'log_Title', $files[$k]);
    $ArticleList = $zbp->GetArticleList(
      "",
      $w,
      array('log_PostTime' => 'DESC'),
      array(0, 3),
      null,
      false
    );
    if (count($ArticleList) === 0) {
      $objResponse->files[] = $zbp->host . $files[$k];
    }
    if (isset($files[$k+1]))
      $objResponse->more = 1;
    else {
      $objResponse->more = 0;
      unlink($filesJSON);
      break;
    }
  }
  return json_encode($objResponse);
}

$arrUser = array();
$arrUser[0] = array("","名称","文章","页面","评论","附件");
$arrCate = array();
$arrCate[0] = array("","名称","文章");

if (GetVars('act', 'GET') == "Tags") {
  $p = (int) GetVars('page', 'GET');
  $array = $zbp->GetTagList('', '', array(
    'tag_ID' => 'ASC'
  ), array(
    $p * 50,
    50
  ), '');
  $arrTags = array();
  $strTags = "";
  foreach ($array as $tag) {
    $arrTags[] = $tag->Name;
    $strTags .= "{" . $tag->ID . "}";
  }
  CountTagArrayString($strTags);
  echo implode("\n",$arrTags);
  die();
} elseif (GetVars('act', 'GET') == "Attachs") {
  $p = (int) GetVars('page', 'GET');
  header("Content-type:text/json");
  echo fnCheckAttach($p);
  die();
} else {
  $zbp->BuildTemplate();
  $zbp->LoadCategorys();
  $zbp->AddBuildModule('catalog');
  //$zbp->AddBuildModule('previous');
  //$zbp->AddBuildModule('calendar');
  //$zbp->AddBuildModule('comments');
  //$zbp->AddBuildModule('archives');
  //$zbp->AddBuildModule('tags');
  //$zbp->AddBuildModule('authors');
  $zbp->BuildModule();
  $zbp->SaveCache();
  fnCountUsr($arrUser, GetVars('act', 'GET') == "run");
  fnCountCat($arrCate, GetVars('act', 'GET') == "run");
}

require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle;?></div>
  <div class="SubMenu">
    <a href="main.php"><span class="m-left m-now">首页</span></a>
    <?php require "about.php";?>
  </div>
  <div id="divMain2">
    <style>
    table{
      width: 45%;
    }
    </style>
    <div class="result">
    <table class="tableBorder">
    <tbody><?php fnEcho($arrUser); ?></tbody>
    </table>
    -----
    <table class="tableBorder">
    <?php fnEcho($arrCate); ?>
    </table>
    </div>
    <form action="main.php" method="get">
      <input type="hidden" value="run" name="act">
      <input type="submit" value="统计文章" class="button">
      <input type="button" value="统计Tags" class="button js-countTags">
      <input type="button" value="统计附件" class="button js-countAttachs">
      <a href="main.php">刷新</a>
    </form>
  </div>
</div>
<script>
$(function () {
  $(".js-countAttachs").click(function () {
    $(".result").html("未在<b>文章或页面</b>中引用的附件<br />");
    fnAjax(0, "Attachs")
  });
  $(".js-countTags").click(function () {
    $(".result").html("");
    fnAjax(0, "Tags")
  });
  // $(".js-countAttachs").click();
  function fnAjax(page, act) {
    $.ajax({
      url: zbp.options.bloghost + "zb_users/plugin/xnxf_CountAll/main.php?act=" + act + "&page=" + page,
      type: "get",
      success: function (data) {
        if (act == "Attachs") {
          console.log(data.files);
          for (var item in data.files) {
            $(".result").append('<a href="-url-" target="_blank" title="-url-">-url-</a><br />'.replace(/-url-/g, data["files"][item]));
          }
          if (data.more)
            fnAjax(page + 1, act);
        } else {
          $(".result").append(data);
          $(".result").append("<br />");
          if (data !== "") {
            fnAjax(page + 1, act);
          }
        }
      }
    });
  }
})
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>