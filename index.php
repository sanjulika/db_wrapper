<?php
include_once('model.php');

$res = new Model();

//$obj = DbWrapper::getInstance();

echo '<pre>';
echo '<br/>';
//print_r($res->find('users', 'all', array('conditions' => array('id' => 501))));
print_r($res->find('users'));

echo '<br/>';
print_r($res->getAllUsers(5));

echo '<br/>list all organisation<br/>';
print_r($res->find('organizations', 'all'));

echo '<br/>List 10 organization whose id is greater than 10<br/>';

print_r($res->find('organizations', 'all', array('conditions' => array('id >' => 10), 'limit' => '10')));

echo '<br/>List Organization whose id is greater than 10 and less than equal to 50<br/>';

print_r($res->find('organizations', 'all', array('conditions' => array('id >=' => 10, 'id <=' => 50))));

echo '<br/> List all organization who has been created after 2013-02-10 00:00:00<br/>';

print_r($res->find('organizations', 'all', array('conditions' => array('created_on >' => '"2013-02-10 00:00:00"'))));

echo '<br/>List all organizations who has id between 10 to 50 and its orders should be descending by name<br/>';

print_r($res->find('organizations', 'all', array('conditions' => array('id >=' => 10, 'id <=' => 50), 'order' => 'name,DESC')));

echo '<br/>Display informations about organization whose id is 70<br/>';

print_r($res->find('organizations', 'all', array('conditions' => array('id' => 70))));

echo '<br/>display informations about organization whose name is "Org Name 30"<br/>';

print_r($res->find('organizations', 'all', array('conditions' => array('name' => '"Org Name 30"'))));

echo '<br/>display all the users of organization_id 30<br/>';

print_r($res->find('users', 'all', array('conditions' => array('organisation_id' => 30))));

echo '<br/>return a count of users per organization with organization name<br/>';

print_r($obj->select('COUNT(U.id)')->select('name')->from(array('users as U', 'organizations as O'))->where(array('U.organisation_id' => 'O.id'))->groupby('organisation_id')->get());

echo '<br/>update users table fname = "abc" and lname = "xyz" of user whose id is 20<br/>';

$saveData = array(
    'id' => 20,
    'fname' => 'abc',
    'lname' => 'xyz',
);
//$res->save('users', $saveData);

//print_r($res->update('users', array('fname' => "abc", 'lname' => "xyz"), array('id' => '21')));

echo '<br/>Delete all users who lives in city "City7"<br/>';

//print_r($res->delete('users', array('id' => '572')));
//print_r($res->delete('users', array('city' => 'City7')));













