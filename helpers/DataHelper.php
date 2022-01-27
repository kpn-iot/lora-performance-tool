<?php


namespace app\helpers;


class DataHelper {

  /**
   * @param $attribute
   * @param $id
   * @param $formModel
   * @return array|null
   * @throws \yii\db\Exception
   */
  public static function getAccuracyHistogramData($attribute, $id, $formModel) {
    $data = static::createCommand('accuracyHistogram', $attribute, $id)
      ->bindValues(['start_date' => $formModel->startDateTime, 'end_date' => $formModel->endDateTime])->queryAll();

    if (count($data) === 0) {
      return null;
    }

    $binBoundaries = explode(',', $formModel->bins);
    $binsTemplate = [];
    foreach ($binBoundaries as $boundary) {
      $binsTemplate[] = [
        "label" => "< " . $boundary,
        "upperBoundary" => (int)$boundary,
        "nrFrames" => 0,
      ];
    }
    $binsTemplate[] = [
      "label" => ">= " . $binBoundaries[count($binBoundaries) - 1],
      "upperBoundary" => null,
      "nrFrames" => 0,
    ];
    $dailyBins = [];
    $dailyTotals = [];
    $aggregatedBins = $binsTemplate;
    $aggregatedTotal = 0;

    foreach ($data as $point) {
      if (!isset($dailyBins[$point['date']])) {
        $dailyBins[$point['date']] = $binsTemplate;
        $dailyTotals[$point['date']] = 0;
      }

      for ($i = 0; $i < count($binsTemplate); $i++) {
        if ($i == count($binsTemplate) - 1 || $point['accuracy'] < $binsTemplate[$i]['upperBoundary']) {
          $dailyBins[$point['date']][$i]['nrFrames'] += 1;
          $dailyTotals[$point['date']] += 1;
          $aggregatedBins[$i]['nrFrames'] += 1;
          $aggregatedTotal += 1;
          break;
        }
      }
    }

    foreach ($aggregatedBins as &$bin) {
      $bin['percentage'] = 100 * $bin['nrFrames'] / $aggregatedTotal;
    }
    foreach ($dailyBins as $day => &$bins) {
      foreach ($bins as &$bin) {
        $bin['percentage'] = 100 * $bin['nrFrames'] / $dailyTotals[$day];
      }
    }

    return [
      'aggregated' => $aggregatedBins,
      'daily' => $dailyBins,
    ];
  }

  public static function getDailyStatsData($attribute, $id, $formModel) {
    $dailyData = static::createCommand('dailyStats', $attribute, $id)
      ->bindValues(['start_date' => $formModel->startDateTime, 'end_date' => $formModel->endDateTime])->queryAll();

    foreach ($dailyData as &$line) {
      foreach (['nr_sessions', 'nr_frames'] as $attr) {
        $line[$attr] = (int)$line[$attr];
      }
      foreach (['drop_rate', 'geoloc_acc_median_avg', 'geoloc_acc_avg', 'geoloc_success_rate', 'gw_count_avg', 'snr_avg'] as $attr) {
        $line[$attr] = (double)$line[$attr];
      }
    }

    $aggregatedData = ['nr_frames' => 0];
    $attributes = ['drop_rate', 'geoloc_acc_median_avg', 'geoloc_acc_avg', 'geoloc_success_rate', 'gw_count_avg', 'snr_avg'];
    foreach ($attributes as $attr) {
      $aggregatedData[$attr] = 0;
    }

    foreach ($dailyData as $dayData) {
      $aggregatedData['nr_frames'] += $dayData['nr_frames'];
      foreach ($attributes as $attr) {
        $aggregatedData[$attr] += $dayData['nr_frames'] * $dayData[$attr];
      }
    }

    if ($aggregatedData['nr_frames'] === 0) {
      return null;
    }

    foreach ($attributes as $attr) {
      $aggregatedData[$attr] /= $aggregatedData['nr_frames'];
    }

    return [
      'aggregated' => $aggregatedData,
      'daily' => $dailyData,
    ];
  }

  /**
   * @param $query
   * @param $attribute
   * @param $value
   * @return \yii\db\Command
   * @throws \Exception
   */
  public static function createCommand($query, $attribute, $value) {
    switch ($query) {
      case 'dailyStats':
        if (!in_array($attribute, ['deviceGroup', 'device'])) {
          throw new \Exception("Input attribute {$attribute} not known for query {$query}");
        }

        $sql = "SELECT `date`, COUNT(DISTINCT id) AS nr_sessions, SUM(nr_frames) as nr_frames, (1 - SUM(frr_w)/SUM(nr_frames)) * 100 AS drop_rate, 
        SUM(geoloc_acc_median_w)/SUM(nr_frames) AS geoloc_acc_median_avg, SUM(geoloc_acc_avg_w)/SUM(nr_frames) AS geoloc_acc_avg, 
        SUM(geoloc_success_w)/SUM(nr_frames) AS geoloc_success_rate, SUM(gw_count_w)/SUM(nr_frames) AS gw_count_avg, SUM(snr_avg_w)/SUM(nr_frames) AS snr_avg 
      FROM (SELECT s.id, date(p.session_date_at) AS `date`, nr_frames, nr_frames * frame_reception_ratio/100 AS frr_w, 
            gateway_count_average * nr_frames AS gw_count_w, snr_average * nr_frames AS snr_avg_w, geoloc_accuracy_median * nr_frames as geoloc_acc_median_w, geoloc_accuracy_average * nr_frames as geoloc_acc_avg_w, 
            geoloc_success_rate * nr_frames/100 as geoloc_success_w FROM devices d ";

        if ($attribute === 'deviceGroup') {
          $sql .= "INNER JOIN device_group_links l ON l.device_id = d.id ";
        }

        $sql .= "INNER JOIN sessions s ON s.device_id = d.id
        INNER JOIN session_properties p ON p.session_id = s.id
        WHERE (deleted_at IS NULL) ";

        if ($attribute === 'deviceGroup') {
          $sql .= "AND l.group_id = :id ";
        } elseif ($attribute === 'device') {
          $sql .= "AND d.id = :id ";
        }

        $sql .= "AND session_date_at >= :start_date AND session_date_at <= :end_date) AS x
      GROUP BY `date`";

        $params = ['id' => $value];

        break;

      case 'accuracyHistogram':
        if (!in_array($attribute, ['deviceGroup', 'device'])) {
          throw new \Exception("Input attribute {$attribute} not known for query {$query}");
        }

        $sql = "SELECT `date`, round(coordinate_distance(measurement_latitude, measurement_longitude, reference_latitude, reference_longitude)*1000) AS accuracy
        FROM (SELECT DATE(session_date_at) AS `date`, if(sessions.type='static' AND sessions.latitude IS NOT NULL AND sessions.longitude IS NOT NULL,
          if(location_report_source='gps', f.latitude, f.latitude_lora), f.latitude_lora) AS measurement_latitude,
        if(sessions.type='static' AND sessions.latitude IS NOT NULL AND sessions.longitude IS NOT NULL,
          if(location_report_source='gps', f.longitude, f.longitude_lora), f.longitude_lora) AS measurement_longitude,
        if(sessions.type='static' AND sessions.latitude IS NOT NULL AND sessions.longitude IS NOT NULL,
          if(location_report_source='gps', sessions.latitude, sessions.latitude), f.latitude) AS reference_latitude,
        if(sessions.type='static' AND sessions.latitude IS NOT NULL AND sessions.longitude IS NOT NULL,
          if(location_report_source='gps', sessions.longitude, sessions.longitude), f.longitude) AS reference_longitude,
         `sessions`.*
        FROM `sessions`
        LEFT JOIN `session_properties` ON `sessions`.`id` = `session_properties`.`session_id`
        INNER JOIN `devices` ON `sessions`.`device_id` = `devices`.`id` ";

        if ($attribute === 'deviceGroup') {
          $sql .= "INNER JOIN `device_group_links` ON `devices`.`id` = `device_group_links`.`device_id` ";
        }

        $sql .= "INNER JOIN frames f ON f.session_id = sessions.id WHERE (deleted_at IS NULL) ";

        if ($attribute === 'deviceGroup') {
          $sql .= "AND group_id = :id ";
        } elseif ($attribute === 'device') {
          $sql .= "AND devices.id = :id ";
        }

        $sql .= "AND session_date_at >= :start_date AND session_date_at <= :end_date) AS x
    WHERE measurement_latitude IS NOT NULL and measurement_longitude is not null and reference_latitude IS NOT NULL and reference_longitude IS NOT null";

        $params = ['id' => $value];

        break;
      default:
        throw new \Exception("Query {$query} not known");
    }

    return \Yii::$app->db->createCommand($sql, $params);
  }

}
