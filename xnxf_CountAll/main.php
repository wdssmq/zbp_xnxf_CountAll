<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('xnxf_CountAll')) {
  $zbp->ShowError(48);
  die();
}

$blogtitle = '统计更新';

function fnCountUsr(&$arrNums, $r = null)
{
  global $zbp;
  $zbp->LoadMembers();
  $arrID = array();
  foreach ($zbp->members as $User) {
    $arrID[] = $User->ID;
    if (!isset($arrNums[$User->ID])) {
      $arrNums[$User->ID][] = '更新前';
    } else {
      $arrNums[$User->ID][] = '更新后';
    }
    $arrNums[$User->ID][] = $User->Name;
    $arrNums[$User->ID][] = $User->Articles;
    $arrNums[$User->ID][] = $User->Pages;
    $arrNums[$User->ID][] = $User->Comments;
    $arrNums[$User->ID][] = $User->Uploads;
    if ($r === true) {
      $arrNums[$User->ID][] = 'br';
    }
  }
  if ($r === true) {
    CountMemberArray($arrID, array(
      null,
      null,
      null,
      null
    ));
    fnCountUsr($arrNums, null);
  }
}

function fnCountCat(&$arrNums, $r = null)
{
  global $zbp;
  $zbp->LoadCategorys();
  $arrID = array();
  foreach ($zbp->categories as $Cate) {
    if (!isset($arrNums[$Cate->ID])) {
      $arrNums[$Cate->ID][] = '更新前';
    } else {
      $arrNums[$Cate->ID][] = '更新后';
    }
    $arrID[] = $Cate->ID;
    $arrNums[$Cate->ID][] = $Cate->Name;
    $arrNums[$Cate->ID][] = $Cate->Count;
    if ($r === true) {
      $arrNums[$Cate->ID][] = 'br';
    }
  }
  if ($r === true) {
    CountCategoryArray($arrID, null);
    fnCountCat($arrNums, null);
  }
}

function fnEcho($arr)
{
  foreach ($arr as $item) {
    if (is_array($item)) {
      echo "<tr>";
      fnEcho($item);
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

function fnCountAttach(&$arrFiles, $dir)
{
  global $zbp;
  foreach (GetFilesInDir($dir, $zbp->option['ZC_UPLOAD_FILETYPE']) as $file) {
    $arrFiles[] = str_replace(ZBP_PATH, "", $file);
  }
  $dirSub = GetDirsInDir($dir);
  if (count($dirSub) > 0) {
    foreach ($dirSub as $d) {
      fnCountAttach($arrFiles, "$dir$d/");
    }
  } else {
    $arrFiles[] = str_replace(ZBP_PATH, "", $dir);
  }
}

function fnCheckAttach($page = 0)
{
  global $zbp;
  $files = array();
  $filesJSON = ZBP_PATH . 'zb_users/cache/OOYOG7.json';

  if (!file_exists($filesJSON)) {
    $dirUp = ZBP_PATH . 'zb_users/upload/';
    fnCountAttach($files, $dirUp);
    file_put_contents($filesJSON, json_encode($files));
  } else {
    $files = json_decode(file_get_contents($filesJSON));
  }
  $objResponse = json_decode('{"more":0,"files":[]}');
  if (count($files) == 0)
    return json_encode($objResponse);
  // var_dump($files);
  // var_dump($objResponse);
  $w = array();
  $step = 20;
  $start = $page * $step;
  for ($k = $start; $k < $start + $step; $k++) {
    if (CountAll_UploadUnused($files[$k])) {
      $objResponse->files[] = $zbp->host . $files[$k];
    }
    if (isset($files[$k + 1]))
      $objResponse->more = 1;
    else {
      $objResponse->more = 0;
      unlink($filesJSON);
      break;
    }
  }
  return json_encode($objResponse);
}

$arrUser = array();
$arrUser[0] = array("", "名称", "文章", "页面", "评论", "附件");
$arrCate = array();
$arrCate[0] = array("", "名称", "文章");

if (GetVars('act', 'GET') == "FixTags") {
  $p = (int) GetVars('page', 'GET');
  $array = $zbp->GetArticleList(null, null, null, array(
    $p * 137,
    137
  ), '');
  foreach ($array as $post) {
    echo CountAll_FixPostTags($post);
  }
  CountAll_DelTags();
  die();
} elseif (GetVars('act', 'GET') == "Tags") {
  $p = (int) GetVars('page', 'GET');
  $array = $zbp->GetTagList('', '', array(
    'tag_ID' => 'ASC'
  ), array(
    $p * 137,
    137
  ), '');
  $arrTags = array();
  // echo count($array);
  foreach ($array as &$tag) {
    $arrTags[$tag->ID] = $tag->Name . $tag->Count;
    CountTag($tag);
    $arrTags[$tag->ID] .= "→" . $tag->Count;
    $tag->Save();
    $arrTags[$tag->ID] = "<span>{$arrTags[$tag->ID]}</span>";
  }
  echo implode("\n", $arrTags);
  die();
} elseif (GetVars('act', 'GET') == "Attachs") {
  $p = (int) GetVars('page', 'GET');
  header("Content-type:text/json");
  echo fnCheckAttach($p);
  die();
} else {
  $zbp->BuildTemplate();
  $zbp->LoadCategorys();
  $zbp->AddBuildModule('catalog');
  //$zbp->AddBuildModule('previous');
  //$zbp->AddBuildModule('calendar');
  //$zbp->AddBuildModule('comments');
  //$zbp->AddBuildModule('archives');
  //$zbp->AddBuildModule('tags');
  //$zbp->AddBuildModule('authors');
  $zbp->BuildModule();
  $zbp->SaveCache();
  fnCountUsr($arrUser, GetVars('act', 'GET') == "run");
  fnCountCat($arrCate, GetVars('act', 'GET') == "run");
}

require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
    <a href="main.php"><span class="m-left m-now">首页</span></a>
    <?php require "about.php"; ?>
  </div>
  <div id="divMain2">
    <style>
      table {
        width: 45%;
      }

      .result span {
        min-width: 12%;
        max-width: 25%;
        display: block;
        float: left;
      }
    </style>
    <div class="result">
      <table class="tableBorder">
        <tbody><?php fnEcho($arrUser); ?></tbody>
      </table>
      -----
      <table class="tableBorder">
        <?php fnEcho($arrCate); ?>
      </table>
    </div>
    <div style="clear:both;"></div>
    <form action="main.php" method="get" id="form">
      <input type="hidden" value="run" name="act">
      <input type="submit" value="统计文章" class="button">
      <input type="button" value="统计Tags" class="button js-Tags">
      <input type="button" value="统计附件" class="button js-Attachs">
      <input type="button" value="修复重复的Tag" class="button js-FixTags">
      <a href="main.php">刷新</a>
    </form>
  </div>
</div>
<script>
  $(function() {
    $(".js-Attachs").click(function() {
      if (confirm('确定：转到附件管理并标红未使用的文件；取消：遍历文件并列出未引用')) {
        window.location.href = zbp.options.bloghost + 'zb_system/admin/index.php?act=UploadMng';
      } else {
        $(".result").html("未在<b>文章或页面</b>中引用的附件（含空文件夹）<br />");
        fnAjax(0, "Attachs");
      }
    });
    $(".js-Tags").click(function() {
      $(".result").html("");
      fnAjax(0, "Tags");
    });
    $(".js-FixTags").click(function() {
      $(".result").html("");
      fnAjax(0, "FixTags");
    });
    // $(".js-countAttachs").click();
    function fnAjax(page, act) {
      $.ajax({
        url: zbp.options.bloghost + "zb_users/plugin/xnxf_CountAll/main.php?act=" + act + "&page=" + page,
        type: "get",
        success: function(data) {
          if (act == "Attachs") {
            console.log(data.files);
            for (var item in data.files) {
              $(".result").append('<a href="-url-" target="_blank" title="-url-">-url-</a><br />'.replace(/-url-/g, data["files"][item]));
            }
            if (data.more)
              fnAjax(page + 1, act);
          } else {
            $(".result").append(data);
            if (data !== "") {
              fnAjax(page + 1, act);
              $(".result").append("<div style=\"clear:both;\">---运行中---</div>");
            } else {
              $(".result").append("<div style=\"clear:both;\">---完成---</div>");
            }
          }
          $('html, body').animate({
            scrollTop: $("#form").offset().top
          }, 300);
        }
      });
    }
  });
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
