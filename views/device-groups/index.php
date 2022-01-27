<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Device Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-group-index">

    <p>
      <?= Html::a('Create Device Group', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

  <?php Pjax::begin(); ?>

  <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'columns' => [
          'id',
          'name',
          'description:ntext',
          'created_at:datetime',
          'updated_at:datetime',
          [
              'class' => 'yii\grid\ActionColumn',
              'options' => [
                  'width' => '60px',
              ],
          ],
      ],
  ]); ?>

  <?php Pjax::end(); ?>

</div>
