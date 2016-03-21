<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/09/24
 * Time: 9:00 PM
 */

namespace Mncedim\Html\Form\Twig;

use Mncedim\Html\Form\Form;
use Mncedim\Html\Form\Element;

/**
 * Class Extension
 * @package app\classes\twig
 */
class Extension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'mncedim_form', array($this, 'mncedimHtmlForm'), array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'mncedim_form_open', array($this, 'mncedimHtmlFormOpen'), array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'mncedim_form_close', array($this, 'mncedimHtmlFormClose'), array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'mncedim_form_field', array($this, 'mncedimHtmlFormField'), array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'mncedim_form_field_element',
                array($this, 'mncedimHtmlFormFieldElement'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'mncedim_form_field_label',
                array($this, 'mncedimHtmlFormFieldLabel'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'mncedim_form_field_errors',
                array($this, 'mncedimHtmlFormFieldErrors'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * Render app form
     * @param Form $form
     * @return string
     */
    public function mncedimHtmlForm(Form $form)
    {
        return $form->render();
    }

    /**
     * @param Form $form
     * @param null $action
     * @param null $method
     * @param array $attributes
     * @return string
     */
    public function mncedimHtmlFormOpen(Form $form, $action = null, $method = null, $attributes = array())
    {
        return $form->open($action, $method, $attributes);
    }

    /**
     * @param Form $form
     * @return string
     */
    public function mncedimHtmlFormClose(Form $form)
    {
        $csrf = $form->getFields('_csrf');
        return ($csrf ? $csrf->render() : '') . $form->close();
    }

    /**
     * @param Element $field
     * @param string $classes
     * @param null $attributes
     * @param string $wrapper
     * @return string
     */
    public function mncedimHtmlFormField(Element $field, $classes = '', $attributes = null, $wrapper = 'div')
    {
        if ($classes != '') {
            if (is_array($classes)) {
                $classes = implode(' ', $classes);
            }
            $field->addClass($classes);
        }

        if (is_array($attributes)) {
            foreach ($attributes as $name => $value) {
                $field->addAttribute($name, $value);
            }
        }
        return $field->render('all', $wrapper);
    }

    /**
     * @param Element $field
     * @param string $classes
     * @param null $attributes
     * @return string
     */
    public function mncedimHtmlFormFieldElement(Element $field, $classes = '', $attributes = null)
    {
        if ($classes != '') {
            if (is_array($classes)) {
                $classes = implode(' ', $classes);
            }
            $field->addClass($classes);
        }

        if (is_array($attributes)) {
            foreach ($attributes as $name => $value) {
                $field->addAttribute($name, $value);
            }
        }
        return $field->render('field');
    }

    /**
     * @param Element $field
     * @return string
     */
    public function mncedimHtmlFormFieldLabel(Element $field)
    {
        return $field->render('label');
    }

    /**
     * @param Element $field
     * @return string
     */
    public function mncedimHtmlFormFieldErrors(Element $field)
    {
        return $field->render('errors');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return  'mncedim_html_form_twig_extension';
    }
} 