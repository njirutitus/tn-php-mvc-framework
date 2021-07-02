<?php


namespace tn\phpmvc\form;


class TextareaField extends BaseField
{

    public function renderInput(): string
    {
        return sprintf('<textarea rows="4" cols="10" name="%s" class="form-control%s col-6">%s</textarea>',
            $this->attribute,
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->model->{$this->attribute},
        );
    }
}