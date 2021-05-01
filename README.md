F3 Model
---

F3 Model strives to create lazy loading relations the fatfree way.  It comes with four functions to add to the mapper object.

<pre>
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

$userGroup = new UserGroup;
$user = $userGroup->loadRelation('User');

</pre>

function findRelation($key, $where, $args) { }

returns array of NotFamily relationship

$where, gives ability to add additional where parameters to related table in same fashion as f3

$args, allows overriding $args given in initial relations function

function loadRelation($key, $where, $args) { }

returns a single object of NotFamily

function countRelation($key, $where, $args) { }

returns a count of NotFamily
