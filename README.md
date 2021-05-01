F3 Model
---

F3 Model strives to create lazy loading relations the fatfree way.  It comes with four functions to add to the mapper object.

<pre>
    function relations() {
        //relationships goes here
        return [
            'User' => [ //key used to reference relationship in other functions
                "User", //object to access
                ['id = ?', $this->user_id] //where parameters
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
</pre>

function findRelation('NotFamily') { }
returns array of NotFamily relationship

function loadRelation('NotFamily') { }
returns a single object of NotFamily

function countRelation('NotFamily') { }
returns a count of NotFamily
