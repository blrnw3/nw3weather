<?php
/**
 * Passwords for the admin data-edit forms (datamod.php, pondAdmin.php).
 *
 * The values live in secrets.php (git-excluded), see secrets.example.php.
 * Editing is refused while a password is missing, empty or still the
 * placeholder, so a fresh install fails closed.
 */
class AdminAuth {
	const PLACEHOLDER = 'CHANGE_ME_PLACEHOLDER';

	private static function load() {
		if (is_readable(ROOT . 'secrets.php')) {
			require_once ROOT . 'secrets.php';
		}
	}

	/** True if the named password constant is set to a real value. */
	public static function configured($name) {
		self::load();
		if (!defined($name)) {
			return false;
		}
		$v = constant($name);
		return is_string($v) && strlen($v) >= 8 && $v !== self::PLACEHOLDER;
	}

	/** Constant-time check of a submitted password against the named constant. */
	public static function check($name, $given) {
		return self::configured($name) && is_string($given) && hash_equals(constant($name), $given);
	}

	public static function notConfiguredMessage() {
		return 'Editing is not configured on this server (admin password not set). No changes were saved.';
	}
}
