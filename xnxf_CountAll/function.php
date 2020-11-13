<?php
function xnxf_CountAll_UploadUnused($file)
{
  global $zbp;
  $w = array();
  $w[] = array('<>', 'log_Type', '1024');
  $w[] = array('search', 'log_Content', 'log_Intro', 'log_Title', $file);
  $ArticleList = $zbp->GetArticleList(
    "",
    $w,
    array('log_PostTime' => 'DESC'),
    array(0, 3),
    null,
    false
  );
  if (count($ArticleList) === 0) {
    return true;
  } else {
    return false;
  }
}
function xnxf_CountAll_CountAttach(&$arrFiles, $dir)
{
  global $zbp;
  foreach (GetFilesInDir($dir, $zbp->option['ZC_UPLOAD_FILETYPE']) as $file) {
    $arrFiles[] = str_replace(ZBP_PATH, "", $file);
  }
  $dirSub = GetDirsInDir($dir);
  if (count($dirSub) > 0) {
    foreach ($dirSub as $d) {
      xnxf_CountAll_CountAttach($arrFiles, "$dir$d/");
    }
  } else {
    $arrFiles[] = str_replace(ZBP_PATH, "", $dir);
  }
}
// 附件检查入口函数
function xnxf_CountAll_CheckAttach($page = 0)
{
  global $zbp;
  $files = array();
  $filesJSON = ZBP_PATH . 'zb_users/cache/xnxf_CountAll_OOYOG7.json';
  if (!file_exists($filesJSON)) {
    $dirUp = ZBP_PATH . 'zb_users/upload/';
    xnxf_CountAll_CountAttach($files, $dirUp);
    file_put_contents($filesJSON, json_encode($files));
  } else {
    $files = json_decode(file_get_contents($filesJSON));
  }
  $objResponse = json_decode('{"more":0,"files":[]}');
  if (count($files) == 0) {
    header("Content-type:text/json");
    JsonReturn($objResponse);
    die();
  }
  $step = 20;
  $start = $page * $step;
  for ($k = $start; $k < $start + $step; $k++) {
    if (xnxf_CountAll_UploadUnused($files[$k])) {
      $objResponse->files[] = $zbp->host . $files[$k];
    }
    if (isset($files[$k + 1])) {
      $objResponse->more = 1;
    } else {
      $objResponse->more = 0;
      unlink($filesJSON);
      break;
    }
  }
  header("Content-type:text/json");
  JsonReturn($objResponse);
  die();
}
// -----
function xnxf_CountAll_Other($p = 0)
{
  global $zbp;
  $array = $zbp->GetArticleList(null, null, null, array(
    $p * 137,
    137
  ), '');
  if (count($array) == 137) {
    echo "<!--#echo-->";
  }
  foreach ($array as $post) {
    // 系统Bug待修复：https://github.com/zblogcn/zblogphp/issues/271
    // echo xnxf_CountAll_FixPostTags($post);
    xnxf_countall_checkNameOrCate($post);
    //xnxf_countall_checkHTML($post);
  }
  if (count($array) < 137) {
    xnxf_CountAll_ZeroTag();
  }
  die();
}
function xnxf_countall_checkHTML(&$article)
{
  global $zbp;
  $arrHtml = array("p", "div");
  $con = $article->Content;
  foreach ($arrHtml as $value) {
    if (substr_count($con, "<{$value}") !== substr_count($con, "</{$value}")) {
      $edt = xnxf_CountAll_a("{$zbp->host}zb_system/cmd.php?act=ArticleEdt&id={$article->ID}", "编辑", "编辑");
      echo xnxf_CountAll_a($article->Url, $article->Title) . "【未闭合】【{$edt}】<br>";
    }
  }
}
function xnxf_countall_checkNameOrCate(&$article)
{
  global $zbp;
  $edt = xnxf_CountAll_a("{$zbp->host}zb_system/cmd.php?act=ArticleEdt&id={$article->ID}", "编辑", "编辑");
  if ($article->Category->Name == "未分类") {
    echo xnxf_CountAll_a($article->Url, $article->Title) . "【未分类】【{$edt}】<br>";
  }
  if ($article->Category->Name == "未命名") {
    echo xnxf_CountAll_a($article->Url, $article->Title) . "【分类未命名】【{$edt}】<br>";
  }
  if ($article->Title == "未命名") {
    echo xnxf_CountAll_a($article->Url, $article->Title) . "【文章未命名】【{$edt}】<br>";
  }
  if (substr($article->Alias, -1) == ".") {
    echo xnxf_CountAll_a($article->Url, $article->Title) . "【别名以.结尾】【{$edt}】<br>";
  }
}
function xnxf_CountAll_FixPostTags(&$article)
{
  global $zbp;
  $tagsIDString = $article->Tag;
  $tagsName = $article->TagsName;
  $zbp->tags = array();
  $zbp->tagsbyname = array();
  $newIDString = PostArticle_CheckTagAndConvertIDtoString($tagsName);
  if ($newIDString !== $tagsIDString) {
    $article->Tag = $newIDString;
    $article->Save();
    CountTagArrayString($newIDString . $tagsIDString);
    return "<div style=\"clear:both;\">{$article->Title}{$article->TagsName}【{$newIDString}←{$tagsIDString}】</div>\n";
  }
  return "";
}
function xnxf_CountAll_ZeroTag()
{
  global $zbp;
  $isTagPlus = $zbp->CheckPlugin('tagsPlus');
  $array = $zbp->GetTagList('', array('=', 'tag_Count', 0), array(
    'tag_ID' => 'ASC'
  ), 137, '');
  $arrTags = array();
  echo "<h3 style=\"clear:both;\">未使用的Tag</h3>";
  foreach ($array as &$tag) {
    $arrTags[$tag->ID] = "{$tag->Name} [{$tag->Count}]";
    $arrTags[$tag->ID] = "<span>{$arrTags[$tag->ID]}</span>";
  }
  echo implode("\n", $arrTags);
  echo "<h3 style=\"clear:both;\">---</h3>";
}
// ----------------
function xnxf_CountAll_ByTag($p = 0)
{
  global $zbp;
  $array = $zbp->GetTagList('', '', array(
    'tag_ID' => 'ASC'
  ), array(
    $p * 137,
    137
  ), '');
  $arrTags = array();
  echo count($array) ? "<!--#echo-->" : "";
  foreach ($array as &$tag) {
    $arrTags[$tag->ID] = xnxf_CountAll_a($tag->Url, $tag->Name, $tag->Name);
    $arrTags[$tag->ID] .= " 丨 {$tag->Count}";
    CountTag($tag);
    $arrTags[$tag->ID] .= "→ {$tag->Count}";
    $tag->Save();
    $arrTags[$tag->ID] = "<span>{$arrTags[$tag->ID]}</span>";
  }
  echo implode("\n", $arrTags);
  die();
}
function xnxf_CountAll_ByUsr($r = false)
{
  global $zbp;
  $zbp->LoadMembers();
  if (!isset($_SESSION["xnxf_CountAll_ByUsr"])) {
    $_SESSION["xnxf_CountAll_ByUsr"] = array();
  }
  $arr = &$_SESSION["xnxf_CountAll_ByUsr"];
  $arr[0] = array("", "名称", "文章", "页面", "评论", "附件");

  $arrID = array();
  foreach ($zbp->members as $User) {
    $arrID[] = $User->ID;
    if (!isset($arr[$User->ID])) {
      $arr[$User->ID][] = '更新前';
    } else {
      $arr[$User->ID][] = '<b>更新后</b>';
    }
    $arr[$User->ID][] = $User->Name;
    $arr[$User->ID][] = $User->Articles;
    $arr[$User->ID][] = $User->Pages;
    $arr[$User->ID][] = $User->Comments;
    $arr[$User->ID][] = $User->Uploads;
    if ($r === true) {
      $arr[$User->ID][] = 'br';
    }
  }
  if ($r === true) {
    CountMemberArray($arrID, array(
      null,
      null,
      null,
      null
    ));
    return xnxf_CountAll_ByUsr();
  } else {
    $rlt = $arr;
    $arr = null;
    return $rlt;
  }
}
function xnxf_CountAll_ByCate($r = false)
{
  global $zbp;
  $zbp->LoadCategorys();
  if (!isset($_SESSION["xnxf_CountAll_ByCate"])) {
    $_SESSION["xnxf_CountAll_ByCate"] = array();
  }
  $arr = &$_SESSION["xnxf_CountAll_ByCate"];
  $arr[0] = array("", "名称", "文章");
  $arrID = array();
  foreach ($zbp->categories as $Cate) {
    if (!isset($arr[$Cate->ID])) {
      $arr[$Cate->ID][] = '更新前';
    } else {
      $arr[$Cate->ID][] = '更新后';
    }
    $arrID[] = $Cate->ID;
    $arr[$Cate->ID][] = $Cate->Name;
    $arr[$Cate->ID][] = $Cate->Count;
    if ($r === true) {
      $arr[$Cate->ID][] = 'br';
    }
  }
  if ($r === true) {
    CountCategoryArray($arrID, null);
    return xnxf_CountAll_ByCate();
  } else {
    $rlt = $arr;
    $arr = null;
    return $rlt;
  }
}
function xnxf_CountAll_Echo($arr)
{
  foreach ($arr as $item) {
    if (is_array($item)) {
      echo "<tr>";
      xnxf_CountAll_Echo($item);
      echo "</tr>";
    } else {
      if ($item !== "br") {
        echo "<td>$item</td>";
      } else {
        echo '</tr><tr style="font-weight:700">';
      }
    }
  }
}
