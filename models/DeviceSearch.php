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

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Device;

/**
 * DeviceSearch represents the model behind the search form about `app\models\Device`.
 */
class DeviceSearch extends Device {

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['id', 'port_id'], 'integer'],
      [['name', 'device_eui', 'payload_type', 'as_id', 'lrc_as_key', 'description', 'created_at', 'updated_at', 'payload_type', 'totalSessions', 'lastFrame'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios() {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  private function _filter(&$query) {
    $query->andFilterWhere([
      'id' => $this->id,
      'port_id' => $this->port_id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'device_eui', $this->device_eui])
      ->andFilterWhere(['like', 'payload_type', $this->payload_type])
      ->andFilterWhere(['like', 'as_id', $this->as_id])
      ->andFilterWhere(['like', 'lrc_as_key', $this->lrc_as_key])
      ->andFilterWhere(['like', 'description', $this->description]);
  }

  /**
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function filter($params) {
    $query = Device::find();

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $query;
    }

    $this->_filter($query);

    return $query;
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params) {
    $query = Device::find()->with(['sessions']);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'name' => SORT_ASC
        ]
      ]
    ]);

    $dataProvider->sort->attributes['totalSessions'] = [
      'asc' => ['(SELECT COUNT(*) FROM sessions WHERE device_id = devices.id AND deleted_at IS NULL)' => SORT_ASC],
      'desc' => ['(SELECT COUNT(*) FROM sessions WHERE device_id = devices.id AND deleted_at IS NULL)' => SORT_DESC],
      'default' => 'desc'
    ];

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    $this->_filter($query);

    return $dataProvider;
  }

}
