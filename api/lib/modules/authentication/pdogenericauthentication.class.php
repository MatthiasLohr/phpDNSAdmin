<?php

/*
 * This file is part of phpDNSAdmin.
 * (c) 2011 Matthias Lohr - http://phpdnsadmin.sourceforge.net/
 *
 * phpDNSAdmin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpDNSAdmin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpDNSAdmin. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package phpDNSAdmin
 * @subpackage Authentication
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */

/**
 * @package phpDNSAdmin
 * @subpackage Authentication
 * @author Matthias Lohr <mail@matthias-lohr.net>
 */
class PdoGenericAuthentication extends AuthenticationModule {

	/** @var PDO database instance */
	private $db = null;

	/** @var string user table name */
	private $table = 'users';

	/** @var string username column */
	private $colUsername = 'username';

	/** @var string password column */
	private $colPassword = 'password';

	/** @var string password encryption method */
	private $encryption = 'plain';

	public function __construct($config) {
		try {
			$this->db = new PDO($config['pdo_dsn'], $config['pdo_username'], $config['pdo_password']);
		} catch (PDOException $e) {
			throw new ModuleConfigException('Could not connect to database!');
		}

		if (isset($config['search_path']) && $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
			$this->db->query('SET search_path TO ' . $this->db->quote($config['search_path']));
		}

		if (isset($config['tablename']) && strlen(trim($config['tablename'])) > 0) {
			$this->table = $config['tablename'];
		}
		if (isset($config['col_username']) && strlen(trim($config['col_username'])) > 0) {
			$this->colUsername = $config['col_username'];
		}
		if (isset($config['col_password']) && strlen(trim($config['col_password'])) > 0) {
			$this->colPassword = $config['col_password'];
		}

		if (isset($config['password_encryption'])) {
			$this->encryption = $config['password_encryption'];
		}
	}

	public function userCheckPassword(User $user, $password) {
		// fetch password from DB
		$stm = $this->db->query('SELECT ' . $this->colPassword . ' AS password FROM ' . $this->table . ' WHERE ' . $this->colUsername . ' = ' . $user->getUsername());
		if ($stm->rowCount() == 0) return false;
		$tmpuser = $stm->fetch();
		// check password
		switch ($this->encryption) {
			case 'plain':
				return ($password == $tmpuser['password']);
				break;
			case 'md5':
				return (md5($password) == $tmpuser['password']);
				break;
			case 'sha1':
				return (sha1($password) == $tmpuser['password']);
				break;
			case 'crypt':
				return (crypt($password, $tmpuser['password']) == $tmpuser['password']);
				break;
			default:
				throw new ModuleRuntimeException('password encryption method \'' . $this->encryption . '\' not supported!');
		}
	}

	public function userExists(User $user) {
		$stm = $this->db->query('SELECT * FROM ' . $this->table . ' WHERE ' . $this->colUsername . ' = ' . $this->db->quote($user->getUsername()));
		return ($stm->rowCount() > 0);
	}

	public static function getInstance($config) {
		return new PdoGenericAuthentication($config);
	}
}

?>