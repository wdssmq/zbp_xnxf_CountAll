<?php
if (!defined('ZBP_PATH')) {
  exit('Access denied');
}

$theadUsr = array(
  "名称",
  "文章",
  "页面",
  "评论",
  "附件",
);

$tbodyUsr = xnxf_CountAll_ByUsr(true);

?>

<table width="45%" class="tableBorder">
  <thead>
    <tr x-data="thead()">
      <template x-for="item in items" :key="item">
        <th x-text="item"></th>
      </template>
    </tr>
  </thead>
  <tbody x-data="tbody()">
    <template x-for="item in items" :key="item.ID">
      <tr>
        <td x-text="item.Name"></td>
        <td x-text="item.Articles"></td>
        <td x-text="item.Pages"></td>
        <td x-text="item.Comments"></td>
        <td x-text="item.Uploads"></td>
      </tr>
    </template>
  </tbody>
</table>

<script>
  document.addEventListener('alpine:init', () => {
    const theadD = <?php echo json_encode($theadUsr); ?>;
    const tbodyD = <?php echo json_encode($tbodyUsr); ?>;
    console.log(theadD, tbodyD);
    Alpine.data('thead', () => ({
      items: theadD,
    }));
    Alpine.data('tbody', () => ({
      items: tbodyD,
    }));
  });
</script>
