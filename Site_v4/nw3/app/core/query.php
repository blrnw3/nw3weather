<?php
namespace nw3\app\core;

use nw3\app\core\Db;

/**
 * Db queries
 *
 * @author Ben
 */
class Query {

	const DEFAULT_TBL = 'daily';

	private $tbl;
	private $cols ;
	private $conds = array();
	private $limit;
	private $orders = array();
	private $groups;
	private $db;

	function __construct($args) {
		$this->db = Db::g();
		$this->tbl = self::DEFAULT_TBL;
		$this->fields($this->get_dynamic_args($args));
		return $this;
	}

	function tbl($tbl_name) {
		$this->tbl = $tbl_name;
		return $this;
	}

	/**
	 * Assign the fields to query on
	 * @param type $fields An array of (field name or array(name, alias))
	 * @return \nw3\app\core\Query
	 */
	function fields($fields) {
		$this->cols = $fields;
		return $this;
	}

	function filter() {
		$this->conds = $this->get_dynamic_args(func_get_args());
		return $this;
	}

	function group() {
		$this->groups = $this->get_dynamic_args(func_get_args());
	}

	/**
	 *
	 * @param type $type
	 * @param type $col If null, uses the first field supplied
	 * @return \nw3\app\core\Query
	 */
	function order($type, $col=null) {
		$this->orders[] = array($col, $type);
		return $this;
	}

	function limit($num=1) {
		$this->limit = $num;
		return $this;
	}

	function extreme($type, $col=null) {
		$this->limit();
		$this->order(($type === Db::MAX) ? Db::DESC : Db::ASC, $col);
		return $this;
	}

	function scalar() {
		return $this->select()->fetchColumn();
	}

	function count() {
		$this->cols = array(Db::count(count($this->cols) ? $this->col(0) : '*'));
		return $this->select()->fetchColumn();
	}

	function one() {
		return $this->select()->fetch(\PDO::FETCH_ASSOC);
	}

	function all() {
		return $this->select()->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function select() {
		$conds = $this->get_conds();
		$cols = $this->get_cols();
		$order = $this->get_order();
		$limit = $this->get_limit();
		$q = "SELECT $cols FROM $this->tbl $conds $order $limit";
		return $this->db->execute($q);
	}

	private function get_conds() {
		return Db::where(Db::and_($this->conds));
	}

	private function get_cols() {
		if(count($this->cols)) {
			return implode(', ', array_map(
				function($col) {
					return is_array($col) ? "{$col[0]} AS {$col[1]}" : $col;
				}, $this->cols)
			);
		}
		return '*';
	}

	private function col($index) {
		return is_array($this->cols[$index]) ? $this->cols[$index][1] : $this->cols[$index];
	}

	private function get_order() {
		if (count($this->orders) === 0) {
			return '';
		}
		foreach ($this->orders as &$order) {
			if(is_null($order[0])) {
				$order[0] = $this->col(0);
			}
		}
		return "ORDER BY " . implode(',', array_map(function($order) {
			return "{$order[0]} {$order[1]}";
		}, $this->orders));
	}

	private function get_limit() {
		return isset($this->limit) ? "LIMIT $this->limit" : '';
	}

	/**
	 * Allows passing of multiple args, or first arg being an array of all args
	 * @param array $args Args as given by func_get_args
	 * @return array args as array
	 */
	private function get_dynamic_args($args) {
		return is_array($args[0]) ? $args[0] : $args;
	}
}
