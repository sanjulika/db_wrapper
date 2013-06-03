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
        if (is_array($tableNames)) {
            $tableNames = implode($tableNames, ',');
        }
        $this->str .= "FROM $tableNames ";
        return $this;
    }

    public function where($conditions = array()) {
        $condtn = $this->getWhere($conditions);
        $this->str .= "WHERE $condtn ";
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

    public function groupBy($fieldName) { //$param = enum (DESC , ASC)
        $this->str .= "GROUP BY $fieldName";
        return $this;
    }

    public function get() {
        $this->str = 'SELECT '.$this->str;
        print_r($this->str);
        $query = $this->str;
        $this->str = '';
        return $this->query($query);
    }

    public function query($query) {
        echo '</br>running query</br><pre>';
        $data = $this->db->query($query);
        return $data->fetchAll();

    }

    public function getWhere($conditions = array()) {

        if (is_array($conditions)) {
            $newCond = '';
            end($conditions);
            $last_key = key($conditions);
            foreach ($conditions as $key => $value) {
//                if (gettype($value) == 'string') {
//                    $value = "'$value'";
//
//                }
                if ($key == 'OR') {
                    end($conditions[$key]);
                    $last_k = key($conditions[$key]);
                    foreach ($conditions[$key] as $k => $v) {
                        if (gettype($v) == 'string') {
                            $v = "'$v'";

                        }
                        $array = explode(' ', $k);
                        if (isset($array[1]) && !empty($array[1])) {
                            $newCond .= "$k $v";
                        } else {
                            $newCond .= "$k = $v";
                        }
                        if ($k != $last_k) {
                            $newCond .= ' OR ';
                        } else if (($k == $last_k) && (count($conditions) > 1) && ($key != $last_key)) {
                            $newCond .= ' AND ';
                        }
                    }
                } else {
                    $array = explode(' ', $key);
                    if (isset($array[1]) && !empty($array[1])) {
                        $newCond .= "$key $value";
                    } else {
                        $newCond .= "$key = $value";
                    }
                    if ($key != $last_key) {
                        $newCond .= ' AND ';
                    }
                }

            }
        }
        return $newCond;

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

$obj = DbWrapper::getInstance();

echo '<pre>';

echo '<br/>list all organisation<br/>';
//print_r($obj->select()->from('organizations')->get());


echo '<br/>List 10 organization whose id is greater than 10<br/>';

print_r($obj->select()->from('organizations')->where(array('id >' => 10))->limit('5')->get());

echo '<br/>List Organization whose id is greater than 10 and less than equal to 50<br/>';

print_r($obj->select()->from('organizations')->where(array('id >' => 10, 'id <=' => 50))->get());

echo '<br/> List all organization who has been created after 2013-02-10 00:00:00<br/>';

print_r($obj->select()->from('organizations')->where(array('created_on >' => '"2013-02-10 00:00:00"'))->get());

echo '<br/>List all organizations who has id between 10 to 50 and its orders should be descending by name<br/>';

print_r($obj->select()->from('organizations')->where(array('id >=' => 10, 'id <=' => 50))->orderBy('name', 'DESC')->get());


echo '<br/>Display informations about organization whose id is 70<br/>';

print_r($obj->select()->from('organizations')->where(array('id' => 70))->get());

echo '<br/>display informations about organization whose name is "Org Name 30"<br/>';

print_r($obj->select()->from('organizations')->where(array('name' => '"Org Name 30"'))->get());


echo '<br/>display all the users of organization_id 30<br/>';

print_r($obj->select()->from('users')->where(array('organisation_id' => 30))->get());


echo '<br/>return a count of users per organization with organization name<br/>';

print_r($obj->select('COUNT(U.id)')->select('name')->from(array('users as U', 'organizations as O'))->where(array('U.organisation_id' => 'O.id'))->groupby('organisation_id')->get());

echo '<br/>update users table fname = "abc" and lname = "xyz" of user whose id is 20<br/>';
//
//$saveData = array(
//    'id' => 20,
//    'fname' => 'abc',
//    'lname' => 'xyz',
//);
//$res->save('users', $saveData);
//
print_r($obj->save('users', array('fname' => "abc", 'lname' => "xyz"), array('id' => '20')));
//
echo '<br/>Delete all users who lives in city "City7"<br/>';
//
////print_r($res->delete('users', array('id' => '572')));
print_r($obj->delete('users', array('city' => 'City7')));














