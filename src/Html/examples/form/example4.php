<?php
require_once 'vendor/autoload.php';

//required for the csrf to work
session_start();

use Mncedim\Html\Form\Form;

/**
 * EXTEND THE ELEMENT CLASS TO CREATE A CUSTOM TYPE
 * Class MyCustomElement
 */
class MyCustomElement extends Mncedim\Html\Form\Element
{
    /**
     * Handler for our new type of Element named 'myfield'
     * @param MyCustomElement $element
     * @param $fieldHtml
     */
    protected static function myfield(MyCustomElement &$element, &$fieldHtml)
    {
        //update the template placeholders with received data

        //use the existing text type handler to replace the different {{placeholders}} in our custom template
        static::text($element, $fieldHtml);

        //or do them separately here
        /*
        $fieldHtml = str_replace('{{value}}', $element->getValue(), $fieldHtml);
        $fieldHtml = str_replace('{{id}}', $element->getId(), $fieldHtml);
        $fieldHtml = str_replace('{{attributes}}', 'title="This is a title" ', $fieldHtml);
        $fieldHtml = str_replace('{{class}}', 'class-name', $fieldHtml);
        */
    }

    /**
     * alternative to: MyCustomElement::addTemplate('myfield', '<p>this is my custom field - {{value}}</p>');
     * @return string
     */
    public function getTemplate()
    {
        //return a custom template when the new Element type is being built
        if ($this->getType() == 'myfield') {
            return "<p data-name='{{name}}' id='{{id}}' class='{{class}}' {{attributes}} >
                This is the template of the custom Element type named 'myfield' - <strong>{{value}}</strong></p>";
        }

        return parent::getTemplate();
    }
}
//end extended class


$inlineForm = Form::Create('inline_form');

//update the form to use the extended Element class for building its fields
$inlineForm->setCustomElementBuilder('\\MyCustomElement');

//add some fields to the form
$inlineForm->addField('name', 'text',
    array(
        'label' => 'Your Name',
        'class' => ['class1', 'class2'],
        'attributes' => ['required', 'title' => 'Your Name', 'placeholder' => '...']
    )
)->addField('gender', 'radio',
    array(
        'options' => ['m' => 'Male', 'f' => 'Female'],
        'attributes' => ['required']
    )
)->addField('my_custom_field', 'myfield', //Add a field to the form using our custom Element type
    array(
        'label' => false, //hide the label
        'value' => 'This is the value of this field',
        'attributes' => ['style' => "color:red;"]
    )
);

$inlineForm->addField(
    'save', 'submit', ['value' => 'Submit']
);

//disable html5 validator for this form
$inlineForm->setAttributes(['novalidate']);

//handle POST
if ( isset( $_POST[$inlineForm->getName()] ) ) {

    //populates the form with posted data
    $inlineForm->handlePostRequest();

    //validate the form using our custom validator defined above
    if ($inlineForm->isValid()) {
        //success!
        echo $inlineForm->getName() . ' submitted valid data.<br/>';
    }
    //encountered errors will be displayed when the form re-renders
}

//render form
echo $inlineForm->prepareView()->render();