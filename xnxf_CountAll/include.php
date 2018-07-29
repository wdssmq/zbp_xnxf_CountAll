<?php
#注册插件
RegisterPlugin("xnxf_CountAll","ActivePlugin_xnxf_CountAll");

function ActivePlugin_xnxf_CountAll() {}
function InstallPlugin_xnxf_CountAll() {}
function UninstallPlugin_xnxf_CountAll() {}

function CountAll_FixPostTags($id){
  global $zbp;
  $article = $zbp->GetPostByID($id);
  $tagsIDString = $article->Tag;
  $tagsName = $article->TagsName;
  $zbp->tags=array();
  $zbp->tagsbyname=array();
  $newIDString = PostArticle_CheckTagAndConvertIDtoString($tagsName);
  if ($newIDString !== $tagsIDString) {
    $article->Tag = $newIDString;
    $article->Save();
    return "{$newIDString}←{$tagsIDString}<br />\n";
  }
  return ".";
}