<?php
require_once 'vendor/autoload.php';
require_once 'DemoForm.php';

$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => false
));

$ext = new \Mncedim\Html\Form\Twig\Extension();
$twig->addExtension($ext);

//required for the csrf to work
session_start();

/*
 * Usage Example
 */
$form = new DemoForm();
echo $twig->render('twig-example.html.twig', ['form' => $form->prepareView()]);
