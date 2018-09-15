<?php
namespace Elo\Controller;

class AuthCtrl
{

	const IS_LOGGED_KEY = "isLogged";

	public static function isLogged($session)
	{
		return $session->get(self::IS_LOGGED_KEY, false);
	}

	public function login($password, $session)
	{
		$accessPassword = '613b88d193a2be96cb728060933ed74166db46f7';
		$isPass = sha1($password) === $accessPassword;
		if ($isPass) {
			$session->set(self::IS_LOGGED_KEY, true);
		}
		return $isPass;
	}
}
