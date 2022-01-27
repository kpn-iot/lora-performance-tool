<?php

use yii\db\Migration;

/**
 * Class m210203_124859_coordinate_distance_function
 */
class m210203_124859_coordinate_distance_function extends Migration {
  /**
   * {@inheritdoc}
   */
  public function safeUp() {
    $this->execute("CREATE FUNCTION `coordinate_distance`(`lat1` VARCHAR(200), `lng1` VARCHAR(200), `lat2` VARCHAR(200), `lng2` VARCHAR(200))
      RETURNS varchar(10)
      begin
        declare distance varchar(10);
        set distance = (select (6371 * acos( 
                        cos( radians(lat2) ) 
                      * cos( radians( lat1 ) ) 
                      * cos( radians( lng1 ) - radians(lng2) ) 
                      + sin( radians(lat2) ) 
                      * sin( radians( lat1 ) )
                        ) ) as distance); 
        if(distance is null) then
         return '';
        else 
          return distance;
        end if;
      end");
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown() {
    $this->execute("DROP FUNCTION coordinate_distance");
  }

}
