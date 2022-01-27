<?php


namespace app\modules\rest\controllers;


use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;

class FrameController extends ActiveController {

  public $modelClass = 'app\models\Frame';
  public $serializer = [
    'class' => 'yii\rest\Serializer',
    'collectionEnvelope' => 'items',
  ];

  public function behaviors() {
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
      'class' => HttpBasicAuth::className(),
    ];
    return $behaviors;
  }

  /**
   * {@inheritdoc}
   */
  public function actions() {
    return [
      'index' => [
        'class' => 'yii\rest\IndexAction',
        'modelClass' => $this->modelClass,
        'checkAccess' => [$this, 'checkAccess'],
      ],
      'view' => [
        'class' => 'yii\rest\ViewAction',
        'modelClass' => $this->modelClass,
        'checkAccess' => [$this, 'checkAccess'],
      ],
      'options' => [
        'class' => 'yii\rest\OptionsAction',
      ],
    ];
  }

}
