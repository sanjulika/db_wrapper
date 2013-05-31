<?php

Class DbWrapper {
    private $db_info = array("host" => "localhost", "dbname" => "test", "username" => "root", "password" => "webonise6186");
    private $db;
    public static $instance = NULL;

    public function __construct() {
        $this->db = new PDO('mysql:host='.$this->db_info['host'].';dbname='.$this->db_info['dbname'].'', $this->db_info['username'], $this->db_info['password']);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->str = "";
        echo 'connection created</br>';

    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            echo 'first time initialization</br>';
            self::$instance = new DbWrapper();
        }
        return self::$instance;


    }

    public function select($fields = 'all') {
        if ($fields != 'all') {
            if ($this->str != '') {
                $this->str .= ',';
            }
            if (is_array($fields)) {
                $field = implode($fields, ',');
                $this->str .= "$field ";
            } else {
                $this->str .= "$fields ";
            }
        } else {
            $this->str = "* ";
        }
        return $this;
    }

    public function from($tableNames) {
        $this->str .= "FROM $tableNames ";
        return $this;
    }

    public function where($conditions = array()) {
//        $condtn = $this->getWhere($conditions);
        $this->str .= "WHERE $conditions ";
        return $this;
    }

    public function limit($limit, $offset = null) {
        $this->str .= "LIMIT $limit ";
        if ($offset != null) {
            $this->str .= "OFFSET $offset ";
        }
        return $this;
    }

    public function orderBy($fieldName, $param = 'ASC') { //$param = enum (DESC , ASC)
        $this->str .= "ORDER BY $fieldName $param";
        return $this;
    }

    public function get() {
        $this->str = 'SELECT '.$this->str;
        print_r($this->str);
        return $this->query($this->str);
    }

    public function query($query) {
        echo 'running query</br><pre>';
        $data = $this->db->query($query);
        return $data->fetchAll();

    }

    public function getWhere($conditions = array()) {
        print_r($conditions);

    }

    public function save($tableName, $setParameters = array(), $conditions = array()) {


        if (empty($conditions)) {
            $columns = array_keys($setParameters);
            $test = $this->query("SHOW INDEX FROM $tableName WHERE Key_name = 'PRIMARY'");

            if (in_array($test[0]['Column_name'], $columns)) {
                $pk = $test[0]['Column_name'];
                $condition = array($pk => $setParameters[$pk]);
                unset($setParameters[$pk]);
                $this->save($tableName, $setParameters, $condition);
            } else {
                $keys = implode(array_keys($setParameters), ',');
                $key_string = '';
                end($setParameters);
                $last_key = key($setParameters);
                foreach ($setParameters as $key => $val) {
                    $key_string .= ":$key";
                    if ($key != $last_key) {
                        $key_string .= ",";

                    }
                }
                $stmt = $this->db->prepare("INSERT INTO $tableName ($keys) VALUES($key_string)");

                foreach ($setParameters as $col => $val) {
                    if ($val == '') {
                        $val = null;
                    }
                    $stmt->bindValue(":$col", $val);
                }
                if ($stmt->execute()) {
                    print_r('Insert succeeded');
                    return true;
                } else {
                    print_r('Insert failed');
                }
            }

        } else {
            if (!empty($setParameters)) {
                $key_string = '';
                end($setParameters);
                $last_key = key($setParameters);
                foreach ($setParameters as $key => $value) {
                    $key_string .= "$key = :$key";
                    if ($key != $last_key) {
                        $key_string .= ",";

                    }
                }
            }

            $cond_key_string = '';
            end($conditions);
            $last_cond_key = key($conditions);
            foreach ($conditions as $c_ky => $c_val) {
                $cond_key_string .= "$c_ky = :$c_ky";
                if ($c_ky != $last_cond_key) {
                    $cond_key_string .= ",";

                }
            }

            $stmt = $this->db->prepare("UPDATE $tableName SET $key_string WHERE $cond_key_string");

            foreach ($setParameters as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }
            foreach ($conditions as $cond_key => $cond_val) {
                $stmt->bindValue(":$cond_key", $cond_val);
            }
            if ($stmt->execute()) {
                print_r('Updated succeeded');
                return true;
            } else {
                print_r('Updated failed');
            }
        }
        return false;
    }

    public function delete($tableName, $conditions = array()) {
        if (!empty($conditions)) {
            $cond_key_string = '';
            end($conditions);
            $last_cond_key = key($conditions);
            foreach ($conditions as $c_ky => $c_val) {
                $cond_key_string .= "$c_ky = :$c_ky";
                if ($c_ky != $last_cond_key) {
                    $cond_key_string .= " AND ";

                }
            }
            $stmt = $this->db->prepare("DELETE FROM $tableName WHERE $cond_key_string");
            foreach ($conditions as $cond_key => $cond_val) {
                $stmt->bindValue(":$cond_key", $cond_val);
            }
            if ($stmt->execute()) {
                print_r('Delete succeeded');
                return true;
            } else {
                print_r('Delete failed');
            }
            return $stmt->rowCount();
        }
        return false;
    }

}

//$obj = DbWrapper::getInstance();
//print_r($obj->select('city')->select(array('id', 'fname'))->select('lname')->from('users')->where('id=501')->get());
//die;
//$test = $obj->query('SHOW INDEX FROM users WHERE Key_name = "PRIMARY"');
//print_r($test);
//die;
//$test = $obj->query('select * from users order by `fname` desc limit 5');
//foreach ($test as $key => $val) {
//    echo $val['id'].'==>>'.$val['organisation_id'].'==>>'.$val['fname'].'==>>'.$val['lname'].'==>>'.$val['city'];
//    echo '</br>';
//    print_r(implode(array_keys($val), ','));
//    echo '</br>';
//    print_r(implode(array_values($val), ','));
//}
//echo "<pre>";
//$saveAllData = array(
//    array(
//        'organisation_id' => '20',
//        'fname' => 'abc',
//        'lname' => 'xyz',
//        'city' => 'mumbai'
//    ),
//    array(
//        'organisation_id' => '21',
//        'fname' => 'abcddd',
//        'lname' => 'xyzdddd',
//        'city' => 'pune'
//    ),
//);
//$saveData = array(
//    'organisation_id' => 99,
//    'fname' => 'abc',
//    'lname' => 'xyz',
//    'city' => 'mumbai'
//);
//$obj->save('users', $saveData);

//print_r($obj->query('select * from users WHERE `organisation_id`=200'));