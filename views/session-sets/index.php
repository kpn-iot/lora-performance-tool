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

use app\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SessionSetSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Session Sets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="session-set-index">
  <p>
    <?= Html::a('Create Session Set', ['create'], ['class' => 'btn btn-success']) ?>
  </p>
  <?php Pjax::begin(); ?>
  <?=
  GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      'id',
      'name',
      'description:ntext',
      'created_at:dateTime',
      'updated_at:dateTime',
      [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view} {coverage} {geoloc} {update} {delete}',
        'options' => [
          'width' => '100px'
        ],
        'buttons' => [
          'coverage' => function ($url, $model) {
            return Html::a(Html::icon('stats'), ['report-coverage', 'id' => $model->id]);
          },
          'geoloc' => function ($url, $model) {
            return Html::a(Html::icon('equalizer'), ['report-geoloc', 'id' => $model->id]);
          }
        ]
      ]
    ],
  ]);
  ?>
  <?php Pjax::end(); ?>
</div>
