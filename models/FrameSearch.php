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
use yii\db\ActiveQuery;
use app\models\Frame;

/**
 * FrameSearch represents the model behind the search form about `app\models\Frame`.
 */
class FrameSearch extends Frame {

  public $gatewayCountMin, $gatewayCountMax, $createdAtMin, $createdAtMax;

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['id', 'session_id', 'count_up', 'gateway_count', 'location_age_lora', 'gatewayCountMin', 'gatewayCountMax'], 'integer'],
      [['payload_hex', 'information', 'latitude', 'longitude', 'time', 'latitude_lora', 'longitude_lora', 'created_at', 'updated_at', 'gatewayCountMin', 'gatewayCountMax', 'createdAtMin', 'createdAtMax', 'channel', 'sf'], 'safe'],
    ];
  }

  public function attributeLabels() {
    return parent::attributeLabels() + [
      'gatewayCountMin' => 'Minimal Gateway Count',
      'gatewayCountMax' => 'Maximal Gateway Count',
      'createdAtMin' => 'Frame received as early as',
      'createdAtMax' => 'Frame received until'
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios() {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  private function _filter(&$query, $sessionIds = null) {

    $query->andFilterWhere(['>=', 'gateway_count', $this->gatewayCountMin]);
    $query->andFilterWhere(['<=', 'gateway_count', $this->gatewayCountMax]);

    $query->andFilterWhere(['>=', 'created_at', ($this->createdAtMin == null) ? null : date('Y-m-d H:i', strtotime($this->createdAtMin))]);
    $query->andFilterWhere(['<=', 'created_at', ($this->createdAtMax == null) ? null : date('Y-m-d H:i', strtotime($this->createdAtMax))]);

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'session_id' => ($sessionIds === null) ? $this->session_id : $sessionIds,
      'count_up' => $this->count_up,
      'gateway_count' => $this->gateway_count,
      'sf' => $this->sf,
      'channel' => $this->channel,
      'location_age_lora' => $this->location_age_lora,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'payload_hex', $this->payload_hex])
      ->andFilterWhere(['like', 'information', $this->information])
      ->andFilterWhere(['like', 'latitude', $this->latitude])
      ->andFilterWhere(['like', 'longitude', $this->longitude])
      ->andFilterWhere(['like', 'time', $this->time])
      ->andFilterWhere(['like', 'latitude_lora', $this->latitude_lora])
      ->andFilterWhere(['like', 'longitude_lora', $this->longitude_lora]);
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveQuery
   */
  public function filter($params, $sessionIds = null) {
    $query = Frame::find()->with(['session']);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $query;
    }

    $this->_filter($query, $sessionIds);

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
    $query = Frame::find()->with(['session', 'session.device', 'reception', 'reception.gateway']);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'created_at' => SORT_DESC
        ]
      ]
    ]);

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
