<?php
/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2017 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

use app\models\Frame;

$frames = Frame::find()
  ->select('COUNT(*) AS count, DATE(created_at) AS date')
  ->andWhere('created_at > DATE_SUB(CURDATE(), INTERVAL 2 WEEK)')
  ->groupBy('DATE(created_at)')
  ->orderBy(['DATE(created_at)' => SORT_DESC])
  ->all();

$lines = [];
foreach ($frames as $frame) {
  $lines[] = [$frame->date, (int) $frame->count];
}
?>
<script>
  google.charts.load('current', {packages: ['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawThis);
  var lines = <?= json_encode($lines); ?>;
  function drawThis() {

    var data = new google.visualization.DataTable();
    data.addColumn('date', 'Timestamp');
    data.addColumn('number', 'Count');

    var rows = [];
    for (i in lines) {
      lines[i][0] = new Date(lines[i][0]);
      rows.push(lines[i]);
    }
    data.addRows(rows);

    var options = {
      title: 'Frames per day',
      theme: "maximized",
      height: 330,
      width: '100%',
      bar: {groupWidth: "95%"},
      colors: ['#4CAF50'],
      legend: {
        position: 'none'
      },
      backgroundColor: 'transparent'
    };

    var chart = new google.visualization.ColumnChart(
            document.getElementById('line_div'));

    chart.draw(data, options);
  }
</script>
<div class="well">
  <div id="line_div"></div>
</div>