<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('xnxf_CountAll')) {$zbp->ShowError(48);die();}

$blogtitle='统计更新';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';

function fnCountUsr(&$arrNums,$r = null){
  global $zbp;
  $zbp->LoadMembers();

  $arrID = array();
  foreach ($zbp->members as $User){
    $arrID[]=$User->ID;
    if (!isset($arrNums[$User->ID])){
      $arrNums[$User->ID][] = '更新前';
    }else{
      $arrNums[$User->ID][] = '更新后';
    }
    $arrNums[$User->ID][] = '名称：' . $User->Name;
    $arrNums[$User->ID][] = '文章：' . $User->Articles;
    $arrNums[$User->ID][] = '页面：' . $User->Pages;
    $arrNums[$User->ID][] = '评论：' . $User->Comments;
    $arrNums[$User->ID][] = '附件：' . $User->Uploads;
    $arrNums[$User->ID][] = '<br/>';
    if ($r===null){
      $arrNums[$User->ID][] = '<br/>';
    }
  }
  if ($r === true) {
    CountMemberArray($arrID, array(null,null,null,null)); 
    fnCountUsr($arrNums,null);
  }
}
function fnCountCat(&$arrNums,$r = null){
  global $zbp;
  $zbp->LoadCategorys();
  $arrID = array();
  foreach ($zbp->categories as $Cate){
    if (!isset($arrNums[$Cate->ID])){
      $arrNums[$Cate->ID][] = '更新前';
    }else{
      $arrNums[$Cate->ID][] = '更新后';
    }
    $arrID[]=$Cate->ID;
    $arrNums[$Cate->ID][] = '名称：' . $Cate->Name;
    $arrNums[$Cate->ID][] = '文章：' . $Cate->Count;
    $arrNums[$Cate->ID][] = '<br/>';
    if ($r===null){
      $arrNums[$Cate->ID][] = '<br/>';
    }
  }
  if ($r === true) {
    CountCategoryArray($arrID,null); 
    fnCountCat($arrNums,null);
  }
}
function fnEcho($arr){
  foreach ($arr as $item){
    if(is_array($item)){
      fnEcho($item);
    }else{
      echo $item . " ";
    }
  }
}

$arrUser = array();
$arrCate = array();

$array = $zbp->GetTagList(
    '',
    '',
    array('tag_ID' => 'ASC'),
    array(($p->PageNow - 1) * $p->PageCount, $p->PageCount),
    array('pagebar' => $p)
);

fnCountUsr($arrUser,GetVars('act','GET') == "run");

fnCountCat($arrCate,GetVars('act','GET') == "run");

?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle;?></div>
  <div class="SubMenu">
  </div>
  <div id="divMain2">
  <?php
  fnEcho($arrUser);
  echo "-----";
  echo "<br />";
  fnEcho($arrCate);
  ?>
  <form action="main.php" method="get">
    <input type="hidden" value="run" name="act">
    <input type="submit" value="提交" class="button"> <a href="main.php">刷新</a>
    </p>
  </form>
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>