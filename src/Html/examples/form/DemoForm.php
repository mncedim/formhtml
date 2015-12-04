<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/11/29
 * Time: 8:47 AM
 */

use Mncedim\Html\Form\Form;

class DemoForm extends Form
{
    /**
     * Add fields
     */
    protected function addFields()
    {
        $this->addField('name', 'text',
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
        )->addField(
            'bio', 'textarea',
            array(
                'label' => 'Bio',
                'attributes' => array('cols' => 25, 'rows' => 3),
                'class' => array('form-control')
            )
        )->addField(
            'favourite_colours', 'multicheckbox',
            array(
                'value' => array('green', 'orange'),
                'options' => array('red' => 'Red', 'orange' => 'Orange', 'green' => 'Green', 'blue' => 'Blue')
            )
        )->addField(
            'communication', 'select',
            array(
                'label' => 'Communication',
                'value' => 'email',
                'class' => array('form-control'),
                'options' => array(
                    'email' => 'Email', 'mobile' => 'Mobile',
                    'Other' => array('snail-mail' => 'Snail Mail', 'fax' => 'Fax') //opt-group
                )
            )
        )->addField('favourite_sport', 'select',
            array(
                'value' => 'football',
                'options' => ['' => 'Select...', 'football' => 'Football', 'cricket' => 'Cricket', 'rugby' => 'Rugby']
            )
        )->addField('terms', 'checkbox',
            array(
                'label' => 'I agree with the terms and conditions.',
                'attributes' => ['required']
            )
        )->addField(
            'save', 'submit', ['value' => 'Submit']
        );
    }

    /**
     * Custom validator for this form
     * No validation is provided by the Form Class,
     * that's for the user to define using whatever validation methods they want within this method
     * @param $fieldName
     * @param $value
     * @return bool
     */
    public function validate($fieldName, $value)
    {
        if ($fieldName == 'name' && empty($value)) {
            $this->getFields($fieldName)->addErrorMessage('This field is required.');
            return false;
        }

        if ($fieldName == 'terms' && !$value) {
            $this->getFields($fieldName)->addErrorMessage('You must agree with the terms and conditions to continue.');
            return false;
        }

        //valid!
        return parent::validate($fieldName, $value);
    }

    /**
     * Form name
     * @return string
     */
    public function getName()
    {
        return 'demo_form';
    }
}