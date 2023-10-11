<?php

namespace MVC;

use MVC\Exception\AutoWiringException;

class Container
{
	private array $registry = [];
	private array $factories = [];
	private array $instances = [];

	private static function keyToClass(string $key): string
	{
		return str_replace('.', '\\', ucwords($key, '.'));
	}

	private static function classToKey(string $class): string
	{
		return str_replace('\\', '.', strtolower($class));
	}

	public function set(string $key, callable $resolver): self
	{
		$this->registry[$key] = $resolver;
		return $this;
	}

	public function setInstance(string $key, object $instance): self
	{
		$this->instances[$key] = $instance;
		return $this;
	}

	/**
	 * Used to create new instance of target object using resolver
	 * @param string $key
	 * @param callable $resolver
	 * @return $this
	 */
	public function setFactory(string $key, callable $resolver): self
	{
		$this->factories[$key] = $resolver;
		return $this;
	}

	public function get(string $key): object
	{

		if (isset($this->instances[$key])) {
			return $this->instances[$key];
		}

		if (isset($this->factories[$key])) {
			return $this->factories[$key]();
		}

		if (isset($this->registry[$key])) {
			return $this->registry[$key]();
		}

		return $this->autoWire($key);

	}

	private function autoWire(string $key): object
	{
		if (!class_exists($key)) throw new AutoWiringException("Unable to auto wire `$key` class doesn't exist.");

		$reflection = new \ReflectionClass($key);
		if (!$reflection->isInstantiable()) throw new AutoWiringException("Unable to auto wire `$key` due non instantiable class.");

		$ctr_args = [];
		if ($reflection->getConstructor()) {
			$ctr_params = $reflection->getConstructor()->getParameters();

			foreach ($ctr_params as $param) {
				if (class_exists($param->getType()->getName())) {
					$ctr_args[] = $this->get($param->getType()->getName());
				} elseif ($param->isDefaultValueAvailable()) {
					$ctr_args[] = $param->getDefaultValue();
				} elseif ($param->isOptional()) {
					$ctr_args[] = null;
				} else {
					$param_name = $param->getName();
					$class_name = $param->getDeclaringClass()->getName();
					throw new AutoWiringException("Unable to auto wire `$param_name` in `$class_name` __constructor.");
				}

			}

		}

		$instance = $reflection->newInstanceArgs($ctr_args);

		return $this->instances[$key] = $instance;


	}


}