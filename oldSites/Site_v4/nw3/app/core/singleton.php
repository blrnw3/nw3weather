<?php
namespace nw3\app\core;
/**
 *
 * @author http://stackoverflow.com/questions/203336/creating-the-singleton-design-pattern-in-php5
 */
class Singleton {
   private static $instances = [];
    protected function __construct() {}
    protected function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function g() {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }
        return self::$instances[$cls];
    }

	public static function is_set() {
        $cls = get_called_class();
        return isset(self::$instances[$cls]);
	}
}

?>
