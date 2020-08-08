<?php
require __DIR__ . "/function.php";
RegisterPlugin("xnxf_CountAll", "ActivePlugin_xnxf_CountAll");

function ActivePlugin_xnxf_CountAll()
{
  Add_Filter_Plugin('Filter_Plugin_Admin_UploadMng_Table', 'xnxf_CountAll_UploadMng');
}
function xnxf_CountAll_UploadMng($upload, &$ret, $tableHeaders)
{
  if (xnxf_CountAll_UploadUnused($upload->Name)) {
    $ret[0] = '<tr style="color: red;">';
  }
}
function InstallPlugin_xnxf_CountAll()
{
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
