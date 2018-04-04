<?php

namespace Mazeo\Main\Form;

use Mazeo\Request\Request;

interface FormInterface {

    /**
     * Validate current form
     * @return boolean
     */
    public function isValid();

    /**
     * Builds all the form fields and returns an instance of the current form
     * @return \Mazeo\Main\Form\Form
     */
    public function buildView();

    /**
     * Binds the current entity to the the current request
     * @param Request $request - the current request object
     */
    public function bindRequest(Request $request);
}