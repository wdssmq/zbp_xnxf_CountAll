<?php
function xnxf_CountAll_PickData($obj, $pickList, $lstData = array())
{
  $data = $obj->GetData();
  $rltData = array_intersect_key($data, array_flip($pickList));
  if (count($lstData) > 0) {
    // 对比新旧数据，将变化的差值括号追加到新数据后面
    foreach ($pickList as $key) {
      if (isset($lstData[$key]) && $lstData[$key] != $rltData[$key]) {
        $diff = $rltData[$key] - $lstData[$key];
        $strDiff = $diff > 0 ? "+{$diff}" : $diff;
        $rltData[$key] .= " ({$strDiff})";
      }
    }
  }
  return $rltData;
}

function xnxf_CountAll_ByUsr($reCount = false)
{
  global $zbp;
  $zbp->LoadMembers();

  $arrRlt = array();
  $pickList = array("ID", "Name", "Articles", "Pages", "Comments", "Uploads");

  foreach ($zbp->members as $User) {
    $rltData = xnxf_CountAll_PickData($User, $pickList);
    if ($reCount) {
      CountMember($User);
      $User->Save();
      $rltData = xnxf_CountAll_PickData($User, $pickList, $rltData);
    }
    $arrRlt[] = $rltData;
  }
  return $arrRlt;
}

function xnxf_CountAll_ByCate($reCount = false)
{
  global $zbp;
  $zbp->LoadCategories();

  $arrRlt = array();
  $pickList = array("ID", "Name", "Count");

  foreach ($zbp->categories as $Cate) {
    $rltData = xnxf_CountAll_PickData($Cate, $pickList);
    if ($reCount) {
      CountCategory($Cate);
      $Cate->Save();
      $rltData = xnxf_CountAll_PickData($Cate, $pickList, $rltData);
    }
    $arrRlt[] = $rltData;
  }
  return $arrRlt;
}
