<?php

namespace F3Model;

use \Base;
use \DB\SQL\Mapper;

class F3Model extends Mapper  {
    public $_table = "";
    public $_db = "";
    protected $_relations = [];
    public function relations() { }
	public function __construct($db = "", $table = "") {
        $db = $db ?: $this->_db;
        if (is_string($db)) {
            $db = Base::instance()->get($db);
        }
        $table = $table ?: $this->_table;
		parent::__construct( $db, $table );
	}
    /*
     * @function findRelation
     */
    public function findRelation($relation, $args = []) {
        return $this->onRelation('find', $relation, $args);
    }
    public function loadRelation($relation, $args = []) {
        return $this->onRelation('load', $relation, $args);
    }
    public function countRelation($relation, $args = []) {
        return $this->onRelation('count', $relation, $args);
    }
    /*
     * @function combineFilter
     * @return filter
     * @var $filter filter
     * @var $filter2 filter
     *
     * Combines two fatfree filters into a single filter
     *
     */
    public function combineFilter($filter, $filter2) {
        $finalWhere = [[], []];
        if (is_string($filter)) {
            $finalWhere[0][] = $filter;
        } else {
            $finalWhere[0][] = array_shift($filter);
            $finalWhere[1] = $filter;
        }
        if (is_string($filter2)) {
            $finalWhere[0][] = $filter2;
        } elseif (count($filter2)) {
            $finalWhere[0][] = array_shift($filter2);
            $finalWhere[1] = array_merge ($finalWhere[1], $filter2);
        }
        $finalWhere[0] = implode(' and ', $finalWhere[0]);
	return $finalWhere;
    }
    public function onRelation($action, $relation, $args = []) {
        $f3 = \Base::instance();
        if (!count($this->_relations)) {
            $this->_relations = $this->relations();
        }
        $relation = $this->_relations[$relation];
        $instance = new $relation[0]; 
        $where = (isset($args['where']) ? $args['where'] : []);
	$finalWhere = $this->combineFilter($relation[1], $where); 

        $finalArgs = array_merge($relation, $args);
        return $instance->{$action}($finalWhere, $finalArgs, $f3->get('CACHE_TIMEOUT')); 
    }
}

