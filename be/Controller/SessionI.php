<?php
namespace Elo\Controller;

interface SessionI
{

	public function set($key, $val);

	public function get($key);
}
