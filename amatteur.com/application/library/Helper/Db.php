<?php

class Helper_Db {
    
    /**
     * @param string $table
     * @return array 
     */
    public static function describeTable($table) {
        $db = JO_Db::getDefaultAdapter();
        $result = $db->describeTable($table);
        $data = array();
        foreach($result AS $res) {
            $data[$res['COLUMN_NAME']] = $res['DEFAULT'];
        }
        return $data;
    }
    
    /**
     * @param string $table
     * @param array $where
     * @return number
     */
    public static function delete($table, $where) {
        $db = JO_Db::getDefaultAdapter();
        return $db->delete($table, $where);
    }
    
    /**
     * @param string $table
     * @param array $data
     * @return number|string
     */
    public static function create($table, $data) {
        $db = JO_Db::getDefaultAdapter();
        
        $rows = self::describeTable($table);
        
        $insert = array();
        
        foreach($rows AS $row => $default) {
            if( array_key_exists($row, $data) ) {
                $insert[$row] = is_null($data[$row]) ? JO_Db::NULL_TO_STRING : $data[$row];
            } else {
            	$insert[$row] = is_null($default) ? JO_Db::NULL_TO_STRING : $default;
            }
        }
        
        if(!$insert) {
            return 0;
        }
        
        $db->insert($table, $insert);
        
        return $db->lastInsertId();   
    }
    
    /**
     * @param string $table
     * @param array $data
     * @param array $where
     * @return boolean|number
     */
    public static function update($table, $data, $where) {
        $db = JO_Db::getDefaultAdapter();
        
        $rows = self::describeTable($table);
        
        $update = array();
        
        foreach($rows AS $row => $default) {
            if( array_key_exists($row, $data) ) {
                $update[$row] = is_null($data[$row]) ? JO_Db::NULL_TO_STRING : $data[$row];
            } else {
            	$update[$row] = is_null($default) ? JO_Db::NULL_TO_STRING : $default;
            }
        }
        
        if(!$update) {
            return false;
        }
        
        return $db->update($table, $update, $where);   
    }

}

?>