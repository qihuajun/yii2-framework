<?php
/**
 * Object class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Object is the base class that implements the *property* feature.
 *
 * A property is defined by a getter method (e.g. `getLabel`),
 * and/or a setter method (e.g. `setLabel`). For example, the following
 * getter and setter methods define a property named `label`:
 *
 * ~~~
 * private $_label;
 *
 * public function getLabel()
 * {
 *     return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *     $this->_label = $value;
 * }
 * ~~~
 *
 * A property can be accessed like a member variable of an object.
 * Reading or writing a property will cause the invocation of the corresponding
 * getter or setter method. For example,
 *
 * ~~~
 * // equivalent to $label = $object->getLabel();
 * $label = $object->label;
 * // equivalent to $object->setLabel('abc');
 * $object->label = 'abc';
 * ~~~
 *
 * If a property only has a getter method and has no setter method, it is
 * considered as *read-only*. In this case, trying to modify the property value
 * will cause an exception.
 *
 * Property names are *case-insensitive*.
 *
 * One can call [[hasProperty]], [[canGetProperty]] and/or [[canSetProperty]]
 * to check the existence of a property.
 *
 * Besides the property feature, the Object class defines a static method
 * [[create]] which provides a convenient alternative way of creating a new
 * object instance.
 *
 * The Object class also defines the [[evaluateExpression]] method so that a PHP
 * expression or callback can be dynamically evaluated within the context of an object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Object
{
	/**
	 * Returns the value of a object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
	 * @param string $name the property name
	 * @return mixed the property value, event handlers attached to the event,
	 * the named behavior, or the value of a behavior's property
	 * @throws Exception if the property is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter();
		}
		throw new Exception('Getting unknown property: ' . get_class($this) . '.' . $name);
	}

	/**
	 * Sets value of a object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$object->property = $value;`.
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @throws Exception if the property is not defined or read-only.
	 * @see __get
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			return $this->$setter($value);
		}
		if (method_exists($this, 'get' . $name)) {
			throw new Exception('Setting read-only property: ' . get_class($this) . '.' . $name);
		} else {
			throw new Exception('Setting unknown property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Checks if the named property is set (not null).
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `isset($object->property)`.
	 *
	 * Note that if the property is not defined, false will be returned.
	 * @param string $name the property name or the event name
	 * @return boolean whether the named property is set (not null).
	 */
	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) { // property is not null
			return $this->$getter() !== null;
		}
		return false;
	}

	/**
	 * Sets a object property to be null.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `unset($object->property)`.
	 *
	 * Note that if the property is not defined, this method will do nothing.
	 * If the property is read-only, it will throw an exception.
	 * @param string $name the property name
	 * @throws Exception if the property is read only.
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {  // write property
			$this->$setter(null);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new Exception('Unsetting read-only property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Returns a value indicating whether a property is defined.
	 * A property is defined if there is a getter or setter method
	 * defined in the class. Note that property names are case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	public function hasProperty($name)
	{
		return $this->canGetProperty($name) || $this->canSetProperty($name);
	}

	/**
	 * Returns a value indicating whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note that property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty($name)
	{
		return method_exists($this, 'get' . $name);
	}

	/**
	 * Returns a value indicating whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note that property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty($name)
	{
		return method_exists($this, 'set' . $name);
	}

	/**
	 * Evaluates a PHP expression or callback under the context of this object.
	 *
	 * Valid PHP callback can be class method name in the form of
	 * array(ClassName/Object, MethodName), or anonymous function.
	 *
	 * If a PHP callback is used, the corresponding function/method signature should be
	 *
	 * ~~~
	 * function foo($param1, $param2, ..., $object) { ... }
	 * ~~~
	 *
	 * where the array elements in the second parameter to this method will be passed
	 * to the callback as `$param1`, `$param2`, ...; and the last parameter will be the object itself.
	 *
	 * If a PHP expression is used, the second parameter will be "extracted" into PHP variables
	 * that can be directly accessed in the expression.
	 * See [PHP extract](http://us.php.net/manual/en/function.extract.php)
	 * for more details. In the expression, the object can be accessed using `$this`.
	 *
	 * @param mixed $_expression_ a PHP expression or PHP callback to be evaluated.
	 * @param array $_data_ additional parameters to be passed to the above expression/callback.
	 * @return mixed the expression result
	 */
	public function evaluateExpression($_expression_, $_data_=array())
	{
		if (is_string($_expression_)) {
			extract($_data_);
			return eval('return ' . $_expression_ . ';');
		} else {
			$_data_[] = $this;
			return call_user_func_array($_expression_, $_data_);
		}
	}

	/**
	 * Creates a new object instance.
	 *
	 * This method calls [[\Yii::create]] to create the new object instance.
	 *
	 * This method differs from the PHP `new` operator in that it does the following
	 * steps to create a new object instance:
	 *
	 * - Call class constructor (same the `new` operator);
	 * - Initialize the object properties using the name-value pairs given as the
	 *   last parameter to this method;
	 * - Call [[Initable::init|init]] if the class implements [[Initable]].
	 *
	 * Parameters passed to this method will be used as the parameters to the object
	 * constructor.
	 *
	 * Additionally, one can pass in an associative array as the last parameter to
	 * this method. This method will treat the array as name-value pairs that initialize
	 * the corresponding object properties. For example,
	 *
	 * ~~~
	 * class Foo extends \yii\base\Object
	 * {
	 *     public $c;
	 *     public function __construct($a, $b)
	 *     {
	 *         ...
	 *     }
	 * }
	 *
	 * $model = Foo::create(1, 2, array('c' => 3));
	 * // which is equivalent to the following lines:
	 * $model = new Foo(1, 2);
	 * $model->c = 3;
	 * ~~~
	 *
	 * @return object the created object
	 * @throws Exception if the configuration is invalid.
	 */
	public static function create()
	{
		$class = '\\' . get_called_class();
		if (($n = func_num_args()) > 0) {
			$args = func_get_args();
			if (is_array($args[$n-1])) {
				// the last parameter could be configuration array
				$method = new \ReflectionMethod($class, '__construct');
				if ($method->getNumberOfParameters()+1 == $n) {
					$config = $args[$n-1];
					array_pop($args);
				}
			}
			$config['class'] = $class;
			array_unshift($args, $config);
			return call_user_func_array('\Yii::create', $args);
		} else {
			return \Yii::create($class);
		}
	}
}
