<?php
#注册插件
RegisterPlugin("xnxf_CountAll", "ActivePlugin_xnxf_CountAll");

function ActivePlugin_xnxf_CountAll()
{
}
function InstallPlugin_xnxf_CountAll()
{
}
function UninstallPlugin_xnxf_CountAll()
{
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