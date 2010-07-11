<?php

/*
 * This file is part of phpDNSAdmin.
 * (c) 2010 Matthias Lohr - http://phpdnsadmin.sourceforge.net/
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
class HtpasswdAuthentication extends AuthenticationModule {

	/**
	 * @var array Array of username => encrypted password
	 */
	private $users = array();

	/**
	 * @var string filename of .htpasswd file
	 */
	private $filename = null;

	protected function __construct($config) {
		$this->filename = $config['filename'];
		$this->fileRead();
	}

	/**
	 * encrypt the password
	 *
	 * @param string $password password to encrypt
	 * @return string encrypted password
	 */
	private function encryptPassword($password)
	{
		$salt = generateRandomKey(2,'.');
		return crypt($password,$salt);
	}

	/**
	 * Read userdata from .htpasswd file
	 * @throws ModuleRuntimeException if file is not readable
	 */
	private function fileRead() {
		if (!file_exists($this->filename)) throw new ModuleRuntimeException('File '.$this->filename.' does not exist!');
		$lines = file($this->filename);
		foreach ($lines as $line) {
			list($username ,$password) = explode(':',$line,2);
			$this->users[$username] = trim($password);
		}
	}

	/**
	 * Write all userdata to .htpasswd file
	 */
	private function fileWrite() {
		$fp = fopen($this->filename,'w');
		foreach ($this->users as $username => $ePassword) {
			fputs($fp,$username.':'.(($ePassword===null)?'x':$ePassword)."\n");
		}
		fclose($fp);
	}

	public static function getInstance($config) {
		return new HtpasswdAuthentication($config);
	}

	public function listUsers() {
		$result = array();
		foreach ($this->users as $username => $ePassword) {
			$result[] = new User($username);
		}
		return $result;
	}

	public function userAdd(User $user, $password = null) {
		if ($this->userExists($user)) return false;
		if ($password === null) {
			$this->users[$user->getUsername()] = null;
		}
		else {
			$this->users[$user->getUsername()] = $this->encryptPassword($password);
		}
		$this->fileWrite();
		return true;
	}

	public function userCheckPassword(User $user,$password) {
		if (!$this->userExists($user)) throw new NoSuchUserException('No user named '.$user->getUsername().' here!');
		$ePassword = $this->users[$user->getUsername()];
		return (crypt($password,$ePassword) == $ePassword);
	}

	public function userDelete(User $user) {
		if (!$this->userExists($user)) throw new NoSuchUserException('No user named '.$user->getUsername().' here!');
		unset($this->users[$user->getUsername()]);
		$this->fileWrite();
		return true;
	}

	public function userExists(User $user) {
		return (isset($this->users[$user->getUsername()]));
	}

	/**
	 *
	 * @param User $user
	 * @param string $password
	 * @return true on success, false otherwise
	 * @throws NoSuchUserException
	 */
	public function userSetPassword(User $user, $password) {
		if (!$this->userExists($user)) throw new NoSuchUserException('No user named '.$user->getUsername().' here!');
		$this->users[$user->getUsername()] = $this->encryptPassword($password);
		$this->fileWrite();
		return true;
	}
}

?>