<?php


namespace app\modules\rest\controllers;


use app\models\Device;
use app\models\DeviceGroup;
use app\models\forms\DeviceGroupGraphForm;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller {

  const MODE_DEVICE_GROUP = 'deviceGroup';
  const MODE_DEVICE = 'device';

  public function behaviors() {
    return [
      'basicAuth' => [
        'class' => \yii\filters\auth\HttpBasicAuth::className(),
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action) {
    \Yii::$app->response->format = Response::FORMAT_JSON;
    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
  }

  public function actionAccuracyHistogram($deviceGroupId = null, $devEUI = null, $startDateTime = null, $endDateTime = null, $bins = null) {
    if ($deviceGroupId === null && $devEUI === null) {
      throw new BadRequestHttpException("At least one of the following query parameters should be set: `deviceGroupId`, `devEUI`.");
    } elseif ($deviceGroupId !== null && $devEUI !== null) {
      throw new BadRequestHttpException("You cannot set both `deviceGroupId` and `devEUI`, one of the query parameters should be omitted.");
    }
    $mode = ($deviceGroupId !== null) ? static::MODE_DEVICE_GROUP : static::MODE_DEVICE;

    if ($mode === static::MODE_DEVICE_GROUP && ($model = DeviceGroup::findOne($deviceGroupId)) === null) {
      throw new NotFoundHttpException('Device group `' . $deviceGroupId . '` not found');
    } elseif ($mode === static::MODE_DEVICE && ($model = Device::findOne(['device_eui' => $devEUI])) === null) {
      throw new NotFoundHttpException('Device `' . $devEUI . '` not found');
    }

    $form = new DeviceGroupGraphForm();
    if ($bins === null) {
      $bins = $form::DEFAULT_BINS;
    }

    $form->setAttributes([
      'startDateTime' => $startDateTime,
      'endDateTime' => $endDateTime,
      'bins' => $bins,
    ]);
    if (!$form->validate()) {
      $errors = $form->getFirstErrors();
      $firstError = reset($errors);
      throw new HttpException(400, $firstError);
    }

    $bins = $model->getAccuracyHistogramData($form);

    if ($bins === null) {
      throw new BadRequestHttpException("There are no frames in the selected date period for the Devices in this Device group. Please widen your date search and reload.");
    }

    $return = [
      'input' => $form->attributes,
    ];
    switch ($mode) {
      case static::MODE_DEVICE:
        $return['device'] = $model->attributes;
        break;
      case static::MODE_DEVICE_GROUP:
        $return['deviceGroup'] = $model->attributes;
        $return['weblink'] = Url::to([
          '/device-groups/histogram',
          'id' => $deviceGroupId,
          'DeviceGroupGraphForm' => [
            'startDateTime' => $form->startDateTime,
            'endDateTime' => $form->endDateTime,
            'bins' => $form->bins,
          ],
        ], true);
        break;

    }
    $return['output'] = $bins;

    return $return;
  }

  /**
   * @param null $deviceGroupId
   * @param null $deviceId
   * @param null $startDateTime
   * @param null $endDateTime
   * @param null $bins
   * @return array
   * @throws BadRequestHttpException
   * @throws HttpException
   * @throws NotFoundHttpException
   * @throws \yii\db\Exception
   */
  public function actionDailyStats($deviceGroupId = null, $devEUI = null, $startDateTime = null, $endDateTime = null, $bins = null) {
    if ($deviceGroupId === null && $devEUI === null) {
      throw new BadRequestHttpException("At least one of the following query parameters should be set: `deviceGroupId`, `devEUI`.");
    } elseif ($deviceGroupId !== null && $devEUI !== null) {
      throw new BadRequestHttpException("You cannot set both `deviceGroupId` and `devEUI`, one of the query parameters should be omitted.");
    }
    $mode = ($deviceGroupId !== null) ? static::MODE_DEVICE_GROUP : static::MODE_DEVICE;

    if ($mode === static::MODE_DEVICE_GROUP && ($model = DeviceGroup::findOne($deviceGroupId)) === null) {
      throw new NotFoundHttpException('Device group `' . $deviceGroupId . '` not found');
    } elseif ($mode === static::MODE_DEVICE && ($model = Device::findOne(['device_eui' => $devEUI])) === null) {
      throw new NotFoundHttpException('Device `' . $devEUI . '` not found');
    }

    $form = new DeviceGroupGraphForm();
    $form->setAttributes([
      'startDateTime' => $startDateTime,
      'endDateTime' => $endDateTime,
    ]);
    if (!$form->validate()) {
      $errors = $form->getFirstErrors();
      $firstError = reset($errors);
      throw new HttpException(400, $firstError);
    }

    $data = $model->getDailyStatsData($form);

    if ($data === null) {
      throw new BadRequestHttpException("There are no sessions in the selected date period for the Devices in this Device group. Please widen your date search and reload.");
    }

    $return = [
      'input' => $form->getAttributes(['startDateTime', 'endDateTime']),
    ];
    switch ($mode) {
      case static::MODE_DEVICE:
        $return['device'] = $model->attributes;
        break;
      case static::MODE_DEVICE_GROUP:
        $return['deviceGroup'] = $model->attributes;
        $return['weblink'] = Url::to([
          '/device-groups/daily-stats',
          'id' => $deviceGroupId,
          'DeviceGroupGraphForm' => [
            'startDateTime' => $form->startDateTime,
            'endDateTime' => $form->endDateTime,
          ]
        ], true);
        break;

    }
    $return['output'] = $data;

    return $return;
  }

}
