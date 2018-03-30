<?php
/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2018 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Device */
/* @var $sessionProps app\models\SessionProperties[] */

$this->title = 'Stats ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Stats';
?>

<p>
  <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
  <?= Html::a('View', ['view', 'id' => $model->id], ['class' => 'btn btn-link']) ?>
</p>
<hr />
<p class="lead">The statistics of previous 30 days.</p>

<script>
  google.charts.load('current', {packages: ['corechart', 'line']});
  google.charts.setOnLoadCallback(draw);
  var info = <?= json_encode($sessionProps); ?>;

  function draw() {
    var data = new google.visualization.DataTable();
    data.addColumn('datetime', 'Timestamp');
    data.addColumn('number', 'Frame reception ratio');
    data.addColumn('number', 'Success rate');
    data.addColumn('number', 'Accuracy Median');
    data.addColumn('number', 'Avg GW Count');

    var rows = [];
    for (i in info) {
      var row = info[i];
      rows.push([
        new Date(row.session_date_at),
        parseFloat(row.frame_reception_ratio),
        parseFloat(row.geoloc_success_rate),
        parseFloat(row.geoloc_accuracy_median),
        parseFloat(row.gateway_count_average)
      ]);
    }
    data.addRows(rows);

    var options = {
      height: 300,
      width: '100%',
      legend: {
        position: 'bottom'
      },
      chartArea: {
        left: 80,
        top: 35,
        right: 10,
        height: 200
      },
      pointSize: 5,
      backgroundColor: 'transparent'
    };

    var chart = new google.visualization.LineChart(document.getElementById('graph'));

    chart.draw(data, options);
  }
</script>
<div id="graph"></div>

<?=
yii\grid\GridView::widget([
  'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => array_reverse($sessionProps)]),
  'columns' => [
    'session_id',
    'session_date_at:dateTime',
    'frame_reception_ratio',
    'geoloc_success_rate',
    'geoloc_accuracy_median',
    'gateway_count_average'
  ]
])
?>