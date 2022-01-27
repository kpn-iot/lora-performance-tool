<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */
/** @var $formModel \app\models\forms\DeviceGroupGraphForm */
/** @var $accuracyGraphData array */
/** @var $successRateGraphData array */
/** @var $dropRateGraphData array */
/** @var $nrSessions int */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Raw data';
\yii\web\YiiAsset::register($this);
?>

<?= $this->render('_view_header', [
    'model' => $model,
    'activeTab' => 'raw'
]) ?>

<?= $this->render('_view_filter', [
    'formModel' => $formModel
]) ?>
<br/>
<?php if ($nrSessions === 0): ?>
    <div class="alert alert-warning">
        There are no sessions in the selected date period. Please widen your date search and reload.
    </div>
<?php endif ?>
</div>
<?php if ($nrSessions > 0): ?>
    <div class="container-fluid">
        <div id="frr-graph"></div>
        <div id="accuracy-graph"></div>
        <div id="success-graph"></div>

        <script>
            google.charts.load('current', {packages: ['corechart', 'line']});
            google.charts.setOnLoadCallback(drawThis);

            function drawThis() {
                lineGraph('accuracy-graph', {
                    title: 'LocSolve median accuracy over time',
                    height: 300,
                    data: <?= json_encode($accuracyGraphData) ?>,
                    yAxisTitle: 'Accuracy [m]'
                });
                lineGraph('success-graph', {
                    title: 'LocSolve success rate over time',
                    height: 300,
                    data: <?= json_encode($successRateGraphData) ?>,
                    yAxisTitle: 'Success rate [%]'
                });
                lineGraph('frr-graph', {
                    title: 'Drop rate over time',
                    height: 300,
                    data: <?= json_encode($dropRateGraphData) ?>,
                    yAxisTitle: 'Drop rate [%]'
                });
            }

            function lineGraph(id, props) {
                const data = new google.visualization.DataTable();
                data.addColumn('datetime', 'Timestamp');
                for (let i in props.data.columns) {
                    data.addColumn('number', props.data.columns[i]);
                }

                let rows = [], dateTimeTemp;
                for (let i in props.data.lines) {
                    props.data.lines[i][0] = props.data.lines[i][0].replace(' ', 'T');
                    dateTimeTemp = (props.data.lines[i][0].substr(-1) === 'Z') ? props.data.lines[i][0] : props.data.lines[i][0] + "Z";
                    props.data.lines[i][0] = new Date(dateTimeTemp);
                    rows.push(props.data.lines[i]);
                }
                data.addRows(rows);

                const options = {
                    title: props.title,
                    height: props.height,
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
                    vAxis: {title: props.yAxisTitle, minValue: 0},
                    pointSize: 5,
                    backgroundColor: 'transparent'
                };
                const chart = new google.visualization.LineChart(document.getElementById(id));

                chart.draw(data, options);
            }
        </script>
    </div>
<?php endif ?>
<div class="container">
