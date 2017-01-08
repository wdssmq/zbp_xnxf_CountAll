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

$arrUser = array();
$arrUser[0] = array("","名称","文章","页面","评论","附件");
$arrCate = array();
$arrCate[0] = array("","名称","文章");

If (GetVars('act', 'GET') == "CountTags") {
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
} else {
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
  <div class="SubMenu"></div>
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
      <input type="submit" value="提交" class="button"> <input type="button" value="统计Tags" class="button js-countTags"> <a href="main.php">刷新</a>
    </form>
  </div>
</div>
<script>
$(function () {
	$(".js-countTags").click(function(){$(".result").html("");fnAjax(0)});
	function fnAjax(page) {
		$.ajax({
			url: zbp.options.bloghost + "zb_users/plugin/xnxf_CountAll/main.php?act=CountTags&page=" + page,
			type: "get",
			success: function (data) {
				$(".result").append(data);
				$(".result").append("<br />");
        if (data !==""){
          page++;
          fnAjax(page);
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
