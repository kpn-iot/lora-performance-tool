<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LocationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Locations';
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
  <?= Html::a('Create Location', ['create'], ['class' => 'btn btn-success']) ?>
</p>
<?php Pjax::begin(); ?>    <?=
GridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'columns' => [
    'name',
    'description:ntext',
    'coordinates:coordinates',
    'nrSessions',
    'created_at:datetime',
    ['class' => 'yii\grid\ActionColumn']
  ],
]);
?>
<?php Pjax::end(); ?>
