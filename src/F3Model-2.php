<?php

namespace F3Model;

use \DB\SQL\Mapper;

class F3Model extends Mapper  {
    public $_table = "";
    public $_db = "";
    public $_relations = [];
    public function relations() { }
	public function __construct($db = "", $table = "") {
        $db = $db ?: $this->_db;
        $table = $table ?: $this->_table;
		parent::__construct( \Base::instance()->get($db), $table );
	}
    public function findRelation($relation, $args = []) {
        return $this->onRelation('find', $relation, $args);
    }
    public function loadRelation($relation, $args = []) {
        return $this->onRelation('load', $relation, $args);
    }
    public function countRelation($relation, $args = []) {
        return $this->onRelation('count', $relation, $args);
    }
    public function onRelation($action, $relation, $args = []) {
        $f3 = \Base::instance();
        $relation = $this->relations()[$relation];
        $instance = new $relation[0]; 
        $finalWhere = [[], []];
        $where = (isset($args['where']) ? $args['where'] : []);
        if (is_string($relation[1])) {
            $finalWhere[0][] = $relation[1];
        } else {
            $finalWhere[0][] = array_shift($relation[1]);
            $finalWhere[1] = $relation[1];
        }
        if (is_string($where)) {
            $finalWhere[0][] = $where;
        } elseif (count($where)) {
            $finalWhere[0][] = array_shift($where);
            $finalWhere[1] = array_merge ($finalWhere[1], $where);
        }
        $finalWhere[0] = implode(' and ', $finalWhere[0]);

        $finalArgs = array_merge($relation, $args);
        return $instance->{$action}($finalWhere, $finalArgs, $f3->get('CACHE_TIMEOUT')); 
    }
}
