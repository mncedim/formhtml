<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/09/25
 * Time: 10:36 PM
 */

namespace Mncedim\Html\Form;

/**
 * Class Instance
 * @package Mncedim\Html\Form
 */
final class Instance extends Form
{
    private $name;
    private $externalValidator;

    /**
     * @param string $name
     * @param string $action
     * @param string $method
     */
    public function __construct($name, $action = '', $method = 'post')
    {
        $this->name = $name;
        parent::__construct($action, $method);
    }

    /**
     * Fields get added via the addField method externally
     * @return null
     */
    protected function addFields() { ; }

    /**
     * Name used to identify this form
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * Anonymous function provided to handle form validation
     * @param $anonymousFunction
     *   - function($fieldName, $value) use ($yourDefinedFormVariable) { //your validation code here }
     *   - the anonymous function should return true/false
     * @return Instance
     */
    public function setValidator($anonymousFunction)
    {
        $this->externalValidator = $anonymousFunction;
        return $this;
    }

    /**
     * Form validation using provided anonymous function
     * @param $fieldName
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function validate($fieldName, $value)
    {
        if (is_callable($this->externalValidator)) {
            $response = call_user_func_array($this->externalValidator, array($fieldName, $value));
            if (!is_bool($response)) {
                throw new \Exception('Anonymous function for Form validation must always return true or false.');
            }

            return $response;
        }
        return parent::validate($fieldName, $value);
    }
} 