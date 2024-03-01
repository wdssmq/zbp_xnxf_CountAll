<?php

function xnxf_CountAll_ByUsr($reCount = false)
{
  global $zbp;
  $zbp->LoadMembers();
  $pickList = array("ID", "Name", "Articles", "Pages", "Comments", "Uploads");
  $arrRlt = array();

  foreach ($zbp->members as $User) {
    $userData = $User->GetData();
    $rltData = array_intersect_key($userData, array_flip($pickList));
    $arrRlt[] = $rltData;
  }
  return $arrRlt;
}
