<?php
namespace Elo\Controller;

use Exception;

class AuthCtrl
{
	public static function isLogged()
	{
		return isset($_SESSION['isLogged']);
	}

	public static function assertIsLogged()
	{
		if (!AuthCtrl::isLogged()) {
			throw new Exception('Not logged');
		}
	}

	public function login($password)
	{
		$accessPassword = '613b88d193a2be96cb728060933ed74166db46f7';
		$isPass = sha1($password) === $accessPassword;
		if ($isPass) {
			$_SESSION['isLogged'] = true;
		}

		return $isPass;
	}
}
