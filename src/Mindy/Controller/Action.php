<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 10/06/14.06.2014 13:50
 */

namespace Mindy\Controller;

/**
 * CAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
use Mindy\Base\Interfaces\IAction;
use Mindy\Helper\Traits\BehaviorAccessors;
use Mindy\Helper\Traits\Configurator;
use ReflectionMethod;

/**
 * CAction is the base class for all controller action classes.
 *
 * CAction provides a way to divide a complex controller into
 * smaller actions in separate class files.
 *
 * Derived classes must implement {@link run()} which is invoked by
 * controller when the action is requested.
 *
 * An action instance can access its controller via {@link getController controller} property.
 *
 * @property \Mindy\Controller\BaseController $controller The controller who owns this action.
 * @property string $id Id of this action.
 *
 * @method run() executes action
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.actions
 * @since 1.0
 */
abstract class Action implements IAction
{
    use BehaviorAccessors;

    private $_id;
    private $_controller;

    /**
     * Constructor.
     * @param \Mindy\Controller\BaseController $controller the controller who owns this action.
     * @param string $id id of the action.
     */
    public function __construct($controller, $id)
    {
        $this->_controller = $controller;
        $this->_id = $id;
    }

    /**
     * @return \Mindy\Controller\BaseController the controller who owns this action.
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @return string id of this action
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Runs the action with the supplied request parameters.
     * This method is internally called by {@link CController::runAction()}.
     * @param array $params the request parameters (name=>value)
     * @return boolean whether the request parameters are valid
     * @since 1.1.7
     */
    public function runWithParams($params)
    {
        $method = new ReflectionMethod($this, 'run');
        if ($method->getNumberOfParameters() > 0) {
            return $this->runWithParamsInternal($this, $method, $params);
        } else {
            return $this->run();
        }
    }

    /**
     * Executes a method of an object with the supplied named parameters.
     * This method is internally used.
     * @param mixed $object the object whose method is to be executed
     * @param ReflectionMethod $method the method reflection
     * @param array $params the named parameters
     * @return boolean whether the named parameters are valid
     * @since 1.1.7
     */
    protected function runWithParamsInternal($object, $method, $params)
    {
        $ps = [];
        foreach ($method->getParameters() as $i => $param) {
            $name = $param->getName();
            if (isset($params[$name])) {
                if ($param->isArray()) {
                    $ps[] = is_array($params[$name]) ? $params[$name] : [$params[$name]];
                } elseif (!is_array($params[$name])) {
                    $ps[] = $params[$name];
                } else {
                    return false;
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $ps[] = $param->getDefaultValue();
            } else {
                return false;
            }
        }
        $method->invokeArgs($object, $ps);
        return true;
    }
}
