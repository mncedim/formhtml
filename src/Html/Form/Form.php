<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/09/24
 * Time: 7:12 AM
 */

namespace Mncedim\Html\Form;

/**
 * Class Form
 * @package Mncedim\Html\Form
 */
abstract class Form
{
    /**
     * @var string
     */
    protected  $action;

    /**
     * @var string
     */
    protected  $method;

    /**
     * @var array
     */
    protected $attributes = array();

    protected $openTag = '<form action="{{action}}" method="{{method}}" {{attributes}}>';
    protected $closeTag = '</form>';

    protected $elements = array();
    protected $elementWrapper = 'div';

    protected $csrf;

    protected $displayInlineErrors = true;
    protected $hideElementLabels = false;

    protected $formIsValid = null;

    /**
     * @param string $action
     * @param string $method
     */
    public function __construct($action = '', $method = 'post')
    {
        $this->action = $action;
        $this->method = $method;

        $this->addFields();

        //token
        $this->csrf = new CSRF();
        $this->generateCSRFToken(false, true); //to be populated by request
    }

    /**
     * @param bool $overwrite overwrite existing token
     * @param bool $empty - create empty csrf element
     * @return bool
     */
    public function generateCSRFToken($overwrite = false, $empty = false)
    {
        if ($overwrite || !isset($this->elements['_csrf'])) {

            if ($overwrite) {
                //delete last token before creating a new one
                $this->csrf->delete($this->csrf->last());
            }

            $this->addField('_csrf', 'hidden',
                array( 'value' => ($empty ? '' : $this->csrf->set()) )
            );
            return true;
        }

        return false;
    }

    /**
     * Add fields to this form
     */
    protected abstract function addFields();

    /**
     * Get form name
     * @return string
     */
    public abstract function getName();

    /**
     * @param $name - Name of the field
     * @param string $type - Type of the field
     * @param array $properties - [label|value|class|attributes]
     * addField(
     *  'field_name', 'text', array(
     *      'label' => 'Field Name',
     *      'value' => 'default value',
     *      'class' => array('class'),
     *      'attributes' => array('placeholder' => 'Field Name', 'data-variable' => 'my data variable value'),
     *      'options => array('value' => 'Label') //for multi valued field types like select
     *  )
     * )
     * Note: 'attributes' should be given an array of valid attributes of the element like the above placeholder eg.
     * @return $this
     */
    public function addField($name, $type = 'text', array $properties = array())
    {
        $this->elements[$name] = new Element($name, $type, $properties, $this);

        if ($type == 'file' && !isset($this->attributes['enctype'])) {
            $this->attributes['enctype'] = 'multipart/form-data';
        }
        return $this;
    }

    /**
     * Remove field from this form
     * @param $name
     * @return bool
     */
    public function removeField($name)
    {
        if (isset($this->elements[$name])) {
            unset($this->elements[$name]);
            return true;
        }
        return false;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        //more form attributes other than action and menthod
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * html element to wrap each field with.
     * Uses a div tag by default
     * @param $wrapper
     */
    public function setFieldWrapper($wrapper)
    {
        $this->elementWrapper = $wrapper;
    }

    /**
     * @param null $action
     * @param null $method
     * @param array $attributes
     * @return string
     */
    public function open($action = null, $method = null, array $attributes = array())
    {
        if (!empty($action)) {
            $this->action = $action;
        }

        if (!empty($method)) {
            $this->method = $method;
        }

        $this->setAttributes($attributes);
        return $this->render('open');
    }

    /**
     * @return string
     */
    public function close()
    {
        return $this->render('close');
    }

    /**
     * Display inline errors
     * @return $this
     */
    public function showInlineErrors()
    {
        $this->displayInlineErrors = true;
        return $this;
    }

    /**
     * Hide inline errors
     * @return $this
     */
    public function hideInlineErrors()
    {
        $this->displayInlineErrors = false;
        return $this;
    }

    /**
     * Show labels
     * @return $this
     */
    public function showLabels()
    {
        $this->hideElementLabels = false;
        return $this;
    }

    /**
     * Hide labels
     * @return $this
     */
    public function hideLabels()
    {
        $this->hideElementLabels = true;
        return $this;
    }

    /**
     * Prepare form for rendering
     *
     * Call this before calling the render method otherwise the form will
     * be missing the csrf token and will not validate.
     * Add other pre-rendering requirements here
     * @return Form
     */
    public function prepareView()
    {
        //add new form csrf token
        $this->generateCSRFToken(true);
        return $this;
    }

    /**
     * Render this form's HTML
     *
     * @param  string $part [all|open|close]
     * @return string
     */
    public function render($part = 'all')
    {
        $formHtml = '';

        //open form
        if ($part == 'all' || $part == 'open') {

            $open = $this->openTag;
            $open = str_replace('{{action}}', $this->action, $open);
            $open = str_replace('{{method}}', $this->method, $open);

            $attributesHtml = '';
            foreach ($this->attributes as $attribute => $value) {
                if (is_string($attribute)) {
                    $attributesHtml .= sprintf('%s="%s" ', $attribute, $value);
                } else {
                    $attributesHtml .= sprintf('%s ', $value);
                }
            }

            //add form attributes
            $open = str_replace('{{attributes}}', $attributesHtml, $open);

            if ($part == 'open') {
                return $open;
            }

            $formHtml .= $open;
        }

        if ($part == 'close') {
            return $this->closeTag;
        }

        //add form elements/fields
        foreach ($this->elements as $element) {
            $element->hideLabel($this->hideElementLabels);
            $element->displayErrors($this->displayInlineErrors);
            $formHtml .= $element->render('all', $this->elementWrapper);
        }

        //close form
        $formHtml .= $this->closeTag;

        return $formHtml;
    }

    /**
     * Validate this form
     * Always call this to have fields populated with their error messages
     * @return bool
     * @throws \Exception
     */
    public function isValid()
    {

        if (is_bool($this->formIsValid)) {
            //validation already done
            return $this->formIsValid;
        }

        if ($this->csrf->get( $this->getFields('_csrf')->getValue() )) {

            //csrf be valid, now check the rest

            $successfullyValidated = true;
            foreach ($this->elements as $name => $element) {

                //ignore csrf token field
                if ($name == '_csrf') { continue; }

                $valid = $this->validate($name, $element->getValue());
                if (!is_bool($valid)) {
                    throw new \Exception(
                        sprintf('validator for field \'%s\' must return true or false, %s returned.', $name, $valid)
                    );
                }

                if (!$valid) {
                    $successfullyValidated = false;
                }
            }

            //mark form as invalid
            $this->formIsValid = $successfullyValidated;

            //delete csrf token
            $this->csrf->delete( $this->getFields('_csrf')->getValue() );

            return $this->formIsValid;
        }

        //csrf failed to validate
        $this->formIsValid = false;

        return $this->formIsValid;
    }

    /**
     * Validator method for child classes to override and implement form validation
     * @param $fieldName
     * @param $value
     * @return bool
     */
    public function validate($fieldName, $value)
    {
        //override in child class and add your validators
        //return true/false

        //example
        // switch ($fieldName) {

        // 	case 'first_name' :
        // 		if (false) {
        // 			//not valid, add error msg to element
        // 			$this->elements[$fieldName]->addErrorMessage('something went wrong!');
        // 			//or
        // 			//$this->getFields($fieldName)->addErrorMessage('something went wrong!');
        // 			return false;
        // 		}
        // 		break;
        // }

        //default to true
        return true;
    }

    /**
     * Check if form has a field
     * @param $name
     * @return bool
     */
    public function hasField($name)
    {
        return isset($this->elements[$name]);
    }

    /**
     * Get this form's fields
     * @param null $name
     * @return array|Element
     */
    public function getFields($name = null)
    {
        if (is_string($name)) {
            return (isset($this->elements[$name]) ? $this->elements[$name] : null);
        }
        return $this->elements;
    }

    /**
     * Get this form's errors
     * @param bool $all
     * @return array
     */
    public function getErrors($all = false)
    {
        $errors = array();
        foreach ($this->elements as $name => $element) {

            $elementErrors = $element->getErrors(false);
            if (!empty($elementErrors)) {
                $errors[$element->getLabel(false)] = ($all ? $elementErrors : current($elementErrors));
            }
        }

        return $errors;
    }

    /**
     * Get this form's data
     * @return array
     */
    public function getData()
    {
        $data = array();
        foreach ($this->elements as $name => $element)
        {
            $data[$name] = $element->getValue();
        }

        return $data;
    }

    /**
     * Populate form from array
     * @param array $data
     * @return Form
     */
    public function fromArray(array $data)
    {
        //populate fields
        foreach ($data as $elementName => $value) {
            if (isset($this->elements[$elementName])) {
                $this->elements[$elementName]->setValue( $value );
            }
        }
        return $this;
    }

    /**
     * Populate form from POST
     * @return Form
     */
    public function handlePostRequest()
    {
        $data = $_POST[$this->getName()];
        return $this->fromArray( empty($data) ? array() : $data );
    }

    /**
     * Populate form from GET
     * @return Form
     */
    public function handleGetRequest()
    {
        $data = $_GET[$this->getName()];
        return $this->fromArray( empty($data) ? array() : $data );
    }

    /**
     * @param $name
     * @return string
     */
    public function __get($name)
    {
        if (!isset($this->elements[$name])) {
            return '';
        }

        $element = $this->elements[$name];
        $element->hideLabel($this->hideElementLabels);

        return $element;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Create adhoc Form without the need to first create a custom class for it
     * @param $name - name of the form
     * @param null $action - action attribute of the form
     * @param string $method - method attribute of the form
     * @return Instance
     */
    public static function Create($name, $action = null, $method = 'post')
    {
        return new Instance($name, $action, $method);
    }
} 