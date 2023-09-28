<?php

namespace MVC;

/**
 * Trait to make a class a Singleton
 */
trait Singleton
{

	protected static $_instance;

	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}