<?php
namespace nw3\app\core;

use nw3\app\core\Db;

/**
 * Db queries
 *
 * @author Ben
 */
class Query implements \Iterator {

	const DEFAULT_TBL = 'daily';

	private $tbl;
	private $cols = [];
	private $limit;
	private $conds = [];
	private $joins = [];
	private $orders = [];
	private $groups = [];
	private $debug = false;
	private $no_nulls = false;
	private $db;

	/* For iteration */
	private $all;
	private $all_count;
	private $index;

	function __construct($args) {
		$this->db = Db::g();
		$this->tbl = self::DEFAULT_TBL;
		$this->fields($args);
		return $this;
	}

	function tbl($tbl_name) {
		$this->tbl = $tbl_name;
		return $this;
	}

	function nest($query) {
		$sql = $query->sql();
		$this->tbl = "($sql)t";
		return $this;
	}

	/**
	 * Assign the fields to query on
	 * @param type $fields An array of (field name or [name, alias)]
	 * @return \nw3\app\core\Query
	 */
	function fields($fields) {
		// Allow passing of arrays of cols
		foreach($fields as $field) {
			if(is_array($field)) {
				foreach($field as $f) {
					$this->cols[] = $f;
				}
			} else {
				$this->cols[] = $field;
			}
		}
		return $this;
	}

	function filter() {
		$this->conds = array_merge($this->conds, func_get_args());
		return $this;
	}

	function group() {
		$this->groups = func_get_args();
		return $this;
	}

	function join($tbl, $on) {
		$this->joins[] = [$tbl, $on];
		return $this;
	}

	/**
	 *
	 * @param type $type
	 * @param type $col If null, uses the first field supplied
	 * @return \nw3\app\core\Query
	 */
	function order($type, $col=null) {
		$type = ($type === MAX) ? Db::DESC : Db::ASC;
		$this->orders[] = [$col, $type];
		return $this;
	}

	function limit($num=1) {
		$this->limit = $num;
		return $this;
	}

	function extreme($type, $col=null) {
		$this->limit();
		$this->order($type, $col);
		return $this;
	}

	function scalar() {
		return $this->select()->fetchColumn();
	}

	function count() {
		$this->cols = [Db::count(count($this->cols) ? $this->col(0) : '*')];
		return $this->select()->fetchColumn();
	}

	function one() {
		return $this->select()->fetch(\PDO::FETCH_ASSOC);
	}

	function all() {
		return $this->select()->fetchAll(\PDO::FETCH_ASSOC);
	}

	function debug() {
		$this->debug = true;
		return $this;
	}

	function no_nulls() {
		$this->no_nulls = true;
		return $this;
	}

	function sql() {
		$conds = $this->get_conds();
		$cols = $this->get_cols();
		$order = $this->get_order();
		$group = $this->get_group();
		$join = $this->get_join();
		$limit = $this->get_limit();
		$q = "SELECT $cols FROM $this->tbl $join $conds $group $order $limit";
		return $q;
	}

	private function select() {
		$q = $this->sql();
		return $this->db->execute($q, $this->debug);
	}

	private function get_conds() {
		return Db::where(Db::and_($this->conds));
	}

	private function get_cols() {
		if(count($this->cols)) {
			return implode(', ', array_map(
				function($col) {
					return is_string($col) ? $col : $col->sql();
				}, $this->cols)
			);
		}
		return '*';
	}

	private function col($index) {
		return $this->cols[$index];
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
		if($this->no_nulls) {
			$null_order = $this->orders[0][0] . ' IS NULL';
			array_unshift($this->orders, [$null_order, Db::ASC]);
		}
		return "ORDER BY " . implode(',', array_map(function($order) {
			return "{$order[0]} {$order[1]}";
		}, $this->orders));
	}

	private function get_limit() {
		return isset($this->limit) ? "LIMIT $this->limit" : '';
	}

	private function get_group() {
		if (count($this->groups) === 0) {
			return '';
		}
		return "GROUP BY ". implode(', ', $this->groups);
	}

	private function get_join() {
		if (count($this->joins) === 0) {
			return '';
		}
		if (count($this->joins) > 1) {
			throw new \Exception('Not implemented. Stick to single joins');
		}
		$join = $this->joins[0];
		return "JOIN {$join[0]} ON {$join[1]}";
	}

	public function current() {
		return $this->all[$this->index];
	}

	public function key() {
		return $this->index;
	}

	public function next() {
		$this->index++;
	}

	public function rewind() {
		$this->index = 0;
		$this->all = $this->all();
		$this->all_count = count($this->all);
	}

	public function valid() {
		return $this->index < $this->all_count;
	}
}
