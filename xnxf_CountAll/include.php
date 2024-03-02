<?php
require __DIR__ . "/function.php";
RegisterPlugin("xnxf_CountAll", "ActivePlugin_xnxf_CountAll");

function ActivePlugin_xnxf_CountAll()
{
  global $zbp;
  if ($zbp->CheckPlugin('zbpWCm')) {
    zbpWCm_EnqJS("alpinejs", true, true);
  }
}

function InstallPlugin_xnxf_CountAll()
{
  global $zbp;
  if ($zbp->CheckPlugin('zbpWCm')) {
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
