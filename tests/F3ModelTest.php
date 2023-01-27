<?php

use F3Model\F3Model;
require __DIR__ . "/../vendor/autoload.php";

$test = new Test();

$f3 = Base::instance();

$db = __DIR__ . "/database.sqlite";
if (is_file($db) ){
    unlink($db);
}
$f3->set('db',new DB\SQL('sqlite:' . $db));

$f3->get('db')->exec([
    "create table if not exists users ( 
	id integer primary key,
	name varchar(255) not null
)
",

"create table if not exists groups (
    id integer primary key,
    name varchar(255) not null
)
",
"create table if not exists  user_group (
    id integer primary key,
    user_id integer not null,
    group_id integer not null
)
"
]);

class User extends F3Model {
    public $_db = 'db';
    public $_table = 'users';
    public function relations() {
	return [
	    'UserGroup' => [
		"UserGroup",
		['user_id = ?', $this->id]
	    ],
	];
    }
}

class Group extends F3Model {
    public $_db = 'db';
    public $_table = 'groups';
    public function relations() {
	return [
	    'UserGroup' => [
		"UserGroup",
		['group_id = ?', $this->id]
	    ],
	    "UserByName" => [
		"UserGroup",
		[
		    'group_id = 3',

		],
		//"order" => "user_id desc"
		"order" => "(select user.name from users as user where user.id = user_id) asc",
	    ]
	];
    }
}
class UserGroup extends F3Model {
    public $_db = 'db';
    public $_table = 'user_group';
    public function relations() { 
	return [
	    'User' => [
		"User",
		['id = ?', $this->user_id]
	    ],
	    'Group' => [
		"Group",
		['id = ?', $this->group_id]
	    ],
	    'NotFamily' => [
		"Group",
		['id != ? and name = ?', 3, 'family']
	    ],
	];
    }
}

$user = new User();
$group = new Group;
$userGroup = new UserGroup;
$userNames = [
    'Brady B',
    'April B',
    'Chart B'
];
$groupNames = [
    'Parents',
    'Children',
    'Family'
];
$userGroups = [
    [1, 1],
    [1, 3],
    [2, 1],
    [2, 3],
    [3, 2],
    [3, 3]
];
foreach($userNames as $userName) {
    $user->reset();
    $user->name = $userName;
    $user->save();
}
foreach($groupNames as $groupName) {
    $group->reset();
    $group->name = $groupName;
    $group->save();
}
foreach($userGroups as $userGroupLine) {
    $userGroup->reset();
    $userGroup->user_id = $userGroupLine[0];
    $userGroup->group_id = $userGroupLine[1];
    $userGroup->save();
}

$user = (new User())
    ->load('id = 1');
$test->expect($user->countRelation('UserGroup') == 2, "count 2 UserGroups");
$test->expect(count($user->findRelation('UserGroup', ['where' => 'id = 1'])) == 1, "1 UserGroup with id of 1 (string based where)");
$test->expect(count($user->findRelation('UserGroup', ['where' => ['id = ?', 1]])) == 1, "1 UserGroup with id of 1 (array based where)");
$test->expect(count($user->findRelation('UserGroup', ['where' => ['id = ?', 1]])) == 1, "1 UserGroup with id of 1 (array based where)");

$userGroup = $user->loadRelation('UserGroup');
$family = $userGroup->loadRelation('NotFamily');
$test->expect(!$family, 'Testing a relation with two bindings');

$family = $user
    ->loadRelation('UserGroup', ['where' => ['group_id = ?', 3]])
    ->loadRelation('Group');

$test->expect($family->name == 'Family', 'Testing a relation with two bindings');


$filters = [
    [
	"id > ?",
	1
    ],
    "id != 2",
    [
	"id < ? or id = ?",
	5,
	5
    ]
];
$expected = [
    "id > ? and id != 2 and id < ? or id = ?",
    1,
    5,
    5
];

$result = [];
foreach($filters as $filter) {
    $result = $user->combineFilter(
	$result, 
	$filter
    );
}

$test->expect (json_encode($result) == json_encode($expected), 'combineFilter test');

$family = (new Group)->load("name = 'Family'");

$users = $family->findRelation('UserByName');

$test->expect($users[0]->name = 'April B', 'User name should be April');
$test->expect($users[1]->name = 'Brady B', 'User name should be Brady');
$test->expect($users[2]->name = 'Chart B', 'User name should be Chart');

$users = $family->findRelation('UserByName', [
    "order" => "(select user.name from users as user where user.id = user_id) desc",
]);

$test->expect($users[2]->name = 'April B', 'User name should be April');
$test->expect($users[1]->name = 'Brady B', 'User name should be Brady');
$test->expect($users[0]->name = 'Chart B', 'User name should be Chart');

print_r($f3->get('db')->log());
// Display the results; not MVC but let's keep it simple
foreach ($test->results() as $result) {
    echo $result['text']."\n";
    if ($result['status'])
	echo 'Pass';
    else
	echo 'Fail ('.$result['source'].')';
    echo "\n";
}
