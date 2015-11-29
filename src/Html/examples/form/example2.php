<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/11/29
 * Time: 8:47 AM
 */

require_once 'vendor/autoload.php';
require_once 'DemoForm.php';

//required for the csrf to work
session_start();

/*
 * Usage Example
 */
$form = new DemoForm();

//handle POST
if ( isset( $_POST[$form->getName()] ) ) {

    //populates the form with posted data
    $form->handlePostRequest();

    //validate the form using our custom validator defined above
    if ($form->isValid()) {
        //success!
        echo '<strong>' . $form->getName() . ' submitted valid data.</strong><br/>';
        var_dump($_POST);
    }
    //encountered errors will be displayed when the form re-renders
}
echo $form->prepareView()->render();