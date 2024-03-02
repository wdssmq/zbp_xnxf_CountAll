<?php
require __DIR__ . "/function.php";
RegisterPlugin("xnxf_CountAll", "ActivePlugin_xnxf_CountAll");

function ActivePlugin_xnxf_CountAll()
{
  global $zbp;
  if ($zbp->CheckPlugin('zbpWCm')) {
    // 在后台，底部引入 alpinejs
    zbpWCm_EnqJS("alpinejs", true, true);
  }
}

function InstallPlugin_xnxf_CountAll()
{
  global $zbp;
  if ($zbp->CheckPlugin('zbpWCm')) {
    // 注册 alpinejs，管理插件将会下载 alpinejs 到本地
    zbpWCm_require("alpinejs", "xnxf_CountAll");
  }
}

function UninstallPlugin_xnxf_CountAll()
{
}

function xnxf_CountAll_a($href, $title, $text = "")
{
  if (empty($text)) {
    $text = $href;
  }
  return "<a target=\"_blank\" href=\"{$href}\" title=\"{$title}\">$text</a>";
}
