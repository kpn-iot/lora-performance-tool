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

use yii\bootstrap\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\QuickSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quicks';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="quick-index">
  <p>
    <?= Html::a('Create Quick', ['create'], ['class' => 'btn btn-success']) ?>
  </p>
  <?php Pjax::begin(); ?>    
  <?=
  GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      'name',
      'typeFormatted:raw',
      'created_at:timeAgo',
      'updated_at:timeAgo',
      [
        'template' => '{coverage} {geoloc} {update} {delete}',
        'buttons' => [
          'coverage' => function ($url, $model) {
            return Html::a(Html::icon('stats'), ['report-coverage', 'id' => $model->id]);
          },
          'geoloc' => function ($url, $model) {
            return Html::a(Html::icon('equalizer'), ['report-geoloc', 'id' => $model->id]);
          }
        ],
        'class' => 'yii\grid\ActionColumn'
      ]
    ]
  ]);
  ?>
  <?php Pjax::end(); ?>
</div>
