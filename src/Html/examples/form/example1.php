<?php
require_once 'vendor/autoload.php';

//required for the csrf to work
session_start();

use Mncedim\Html\Form\Form;

$inlineForm = Form::Create('inline_form');

//add form validator -
//this can also be a static method in some class: someClass::inlineFormValidator($fieldName, $value)
//that has access to $inlineForm
$inlineForm->setValidator(function($fieldName, $value) use ($inlineForm) {

    if ($fieldName == 'name' && empty($value)) {
        $inlineForm->getFields($fieldName)->addErrorMessage('This field is required.');
        return false;
    }

    if ($fieldName == 'terms' && !$value) {
        $inlineForm->getFields($fieldName)->addErrorMessage('You must agree the terms and conditions to continue.');
        return false;
    }

    //valid!
    return true;
});

//add some fields to the form
$inlineForm->addField('name', 'text',
    array(
        'label' => 'Your Name',
        'class' => ['class1', 'class2'],
        'extra' => ['required', 'title' => 'Your Name', 'placeholder' => '...']
    )
)->addField('gender', 'radio',
    array(
        'options' => ['m' => 'Male', 'f' => 'Female'],
        'extra' => ['required']
    )
)->addField('favourite_sport', 'select',
    array(
        'value' => 'football',
        'options' => ['' => 'Select...', 'football' => 'Football', 'cricket' => 'Cricket', 'rugby' => 'Rugby']
    )
)->addField('terms', 'checkbox',
    array(
        'label' => 'I agree with the terms and conditions.',
        'extra' => ['required']
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