<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class BaseForm extends Form
{
    public function isNew()
    {
        return empty($this->model->id);
    }
}
