<?php
if (!defined('ZBP_PATH')) {
  exit('Access denied');
}

$theadUsr = array(
  "ID" => "ID",
  "Name" => "名称",
  "Articles" => "文章",
  "Pages" => "页面",
  "Comments" => "评论",
  "Uploads" => "附件",
);

$theadCate = array(
  "ID" => "ID",
  "Name" => "名称",
  "Count" => "数量",
);

if ($act == 'CountDef') {
  CheckIsRefererValid();
  $tbodyUsr = xnxf_CountAll_ByUsr(true);
  $tbodyCate = xnxf_CountAll_ByCate(true);
} else {
  $tbodyUsr = xnxf_CountAll_ByUsr();
  $tbodyCate = xnxf_CountAll_ByCate();
}

?>

<template x-data="tableData()" x-for="data in tableData" :key="data.type">
  <table width="45%" class="tableBorder">
    <thead>
      <tr>
        <template x-for="item in data.thead" :key="item">
          <th x-text="item"></th>
        </template>
      </tr>
    </thead>
    <tbody>
      <template x-for="item in data.items" :key="item.ID">
        <tr>
          <template x-for="(value, key) in Object.values(item)" :key="key">
            <td x-text="value"></td>
          </template>
        </tr>
      </template>
    </tbody>
  </table>
</template>

<script>
  document.addEventListener('alpine:init', () => {
    const theadUsr = <?php echo json_encode($theadUsr); ?>;
    const tbodyUsr = <?php echo json_encode($tbodyUsr); ?>;
    const theadCate = <?php echo json_encode($theadCate); ?>;
    const tbodyCate = <?php echo json_encode($tbodyCate); ?>;
    Alpine.data("tableData", () => ({
      tableData: [{
          type: "用户",
          thead: theadUsr,
          items: tbodyUsr
        },
        {
          type: "分类",
          thead: theadCate,
          items: tbodyCate
        }
      ]
    }))
  });
</script>
