<?php 
define('TZString', 1<<0);
define('TZInteger', 1<<1);
define('TZArray', 1<<2);
define('TZDictionary', 1<<3);
define('TZBoolean', 1<<4);
define('TZFloat', 1<<5);
define('TZObject', 1<<6);
define('TZVoid', 1<<7);
//TZCustom 1 << 8 not exposed

class TZObjectException extends Exception{}

class TZObject {
	private $data = array();
	private $fnTypes = array();

	private $defaults = array(
		TZString => "",
		TZInteger => 0,
		TZArray => array(),
		TZDictionary => array(),
		TZBoolean => false,
		TZFloat => 0.0,
		TZObject => null,
	);

	private $stringTypes = array(
		'TZString' => TZString,
		'TZInteger' => TZInteger,
		'TZArray' => TZArray,
		'TZDictionary' => TZDictionary,
		'TZBoolean' => TZBoolean,
		'TZFloat' => TZFloat,
		'TZObject' => TZObject,
		'TZVoid' => TZVoid,
	);

	private $tokenArray = null;

	public function __construct() {
		if (!property_exists($this, 'definition')) {
			throw new TZObjectException("Invalid class: missing definition");
		}

		$this->tokenArray = array_flip($this->stringTypes);

		foreach($this->definition as $var=>$type) {
			if (!array_key_exists($type, $this->defaults)) {
				$this->data[$var] = NULL;
			} else if ($type & TZVoid) {
				throw new TZObjectException("Just methods may have TZVoid type");
			} else {
				$this->data[$var] = $this->defaults[$type];
			}
		}

		$this->_loadFunctionsType();
	}

	public function __set($prop, $value) {
		if (!array_key_exists($prop, $this->data)) {
			throw new TZObjectException("Object of class '" . get_class($this) . "' does not have a property called '{$prop}'");
		}

		if (!$this->_validate($value, $this->definition[$prop])) {
			throw new TZObjectException("Invalid assignment: Wrong type for property '{$prop}'");
		}

		$this->data[$prop] = $value;
	}

	public function __get($prop) {
		if (!array_key_exists($prop, $this->data)) {
			throw new TZObjectException("Object of class '" . get_class($this) . "' does not have a property called '{$prop}'");
		}

		return $this->data[$prop];
	}

	public function __call($method, $args) {
		if (!array_key_exists($method, $this->fnTypes)) {
			throw new TZObjectException("Object of class '" . get_class($this) . "' does not have a method called '{$method}'");
		}

		$type = $this->_stringify($this->fnTypes[$method]);
		$fullMethodName = "{$type}_{$method}";

		$output = call_user_func_array(array($this, $fullMethodName), $args);

		if (!$this->_validate($output, $this->fnTypes[$method])) {
			throw new TZObjectException("Data returned from '" . get_class($this) . '::' . $method . "' has the wrong type!");
		}

		return $output;
	}

	private function _loadFunctionsType() {
		$methods = get_class_methods($this);

		foreach ($methods as $method) {
			if (method_exists('TZObject', $method)) 
				continue;

			if (strpos($method, '_') !== false) {
				list($type, $name) = explode('_', $method);
				$type = $this->_tokenize($type);
			} else {
				throw new TZObjectException("Method '" . get_class($this) . '::' . $method . "' does not declare a type!");
			}
			
			$this->fnTypes[$name] = $type;
		}
	}

	private function _tokenize($type) {
		if (array_key_exists($type, $this->stringTypes)) {
			$type = $this->stringTypes[$type];
		}

		return $type;
	}

	private function _stringify($type) {
		$_type = null;

		if (array_key_exists($type, $this->tokenArray)) {
			$_type = $this->tokenArray[$type];
		}

		return $_type;
	}

	private function _validate(&$data, $type) {
		$valid = false;
		if ($type & TZInteger) {
			$valid = is_int($data);
			$data = (int)$data;
		} else if ($type & TZArray) {
			$valid = is_array($data) && array_sum(array_keys($data)) == (count($data))*((count($data)-1)/2);
			$data = array_values($data);
		} else if ($type & TZDictionary) {
			$valid = is_array($data) && !array_sum(array_keys($data)) == (count($data))*((count($data)-1)/2);
		} else if ($type & TZBoolean) {
			$valid = is_bool($data);
			$data = (bool)$data;
		} else if ($type & TZFloat) {
			$valid = is_numeric($data);
			$data = (float)$data;
		} else if ($type & TZObject) {
			$valid = is_object($data);
		} else if ($type & TZString) {
			$valid = is_string($data);
		} else if ($type & TZVoid) {
			$valid = $data === null;
		} else if (is_string($type)) {
			$valid = $data instanceof $type;
		}

		return $valid;
	}
}
