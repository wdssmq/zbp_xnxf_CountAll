<?php
#注册插件
RegisterPlugin("xnxf_CountAll", "ActivePlugin_xnxf_CountAll");

function ActivePlugin_xnxf_CountAll()
{
  Add_Filter_Plugin('Filter_Plugin_Admin_UploadMng_Table', 'CountAll__Upload');
}
function CountAll__Upload($upload, &$ret, $tableHeaders)
{
  if (CountAll_UploadUnused($upload->Name)) {
    $ret[0] = '<tr style="color: red;">';
  }
}
function InstallPlugin_xnxf_CountAll()
{ }
function UninstallPlugin_xnxf_CountAll()
{ }
function CountAll_UploadUnused($file)
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
function CountAll_FixPostTags(&$article)
{
  global $zbp;
  // $article = $zbp->GetPostByID($id);
  $tagsIDString = $article->Tag;
  $tagsName = $article->TagsName;
  $zbp->tags = array();
  $zbp->tagsbyname = array();
  $newIDString = PostArticle_CheckTagAndConvertIDtoString($tagsName);
  if ($newIDString !== $tagsIDString) {
    $article->Tag = $newIDString;
    $article->Save();
    CountTagArrayString($newIDString . $tagsIDString);
    return "<div style=\"clear:both;\">{$newIDString}←{$tagsIDString}</div>\n";
  }
  return "\n";
}
function CountAll_DelTags()
{
  global $zbp;
  // $array = $zbp->LoadTagsByIDString($string);
  // $zbp->tags = array();
  // $zbp->tagsbyname = array();
  $array = $zbp->GetTagList('', array('=', 'tag_Count', 0), array(
    'tag_ID' => 'ASC'
  ), 137, '');
  $arrTags = array();
  // echo count($array);
  foreach ($array as &$tag) {
    $arrTags[$tag->ID] = $tag->Name . $tag->Count;
    CountTag($tag, $plus);
    $arrTags[$tag->ID] .= "→" . $tag->Count;
    if ($tag->Count == 0) {
      $tag->Del();
    } else {
      $tag->Save();
    }
    $arrTags[$tag->ID] = "<span>{$arrTags[$tag->ID]}</span>";
  }
  echo implode("\n", $arrTags);
}
