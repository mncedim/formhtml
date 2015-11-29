<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/11/29
 * Time: 9:49 AM
 *
 * Render form fields individually
 */

require_once 'vendor/autoload.php';
require_once 'DemoForm.php';

//required for the csrf to work
session_start();

/*
 * Usage Example
 */
$form = new DemoForm();

$form->setAttributes(['novalidate']);

//handle POST
if ( isset( $_POST[$form->getName()] ) ) {

    //populates the form with posted data
    $form->handlePostRequest();

    //validate the form using our custom validator defined above
    if ($form->isValid()) {
        //success!
        echo '<strong>' . $form->getName() . ' submitted valid data.</strong><br/>';
    }
    //encountered errors will be displayed when the form re-renders
}


//display errors at the top instead of inline
$formErrors = $form->getErrors();
if (sizeof($formErrors) > 0) {

    echo '<h3>Form errors:</h3><ul>';
    foreach ($formErrors as $field => $error) {
        echo sprintf('<li>%s - %s</li>', $field, $error);
    }
    echo '</ul>';
}

echo $form->prepareView()->render('open');
echo $form->getFields('_csrf')->render();

//render name field label, field, errors separately
echo '<div>';
echo $form->getFields('name')->render('label');
echo $form->getFields('name')->render('field');
//hide this fields errors
//echo $form->getFields('name')->render('errors');
echo '</div>';

echo $form->getFields('gender')->render();
echo $form->getFields('favourite_sport')->render();
//hide this fields errors
echo $form->getFields('terms')->displayErrors(false)->render();
echo $form->getFields('save')->render();

echo $form->render('close');

