<?php


namespace app\core\form;


use app\core\db\Model;

abstract class BaseField
{
    public Model $model;
    public string $attribute;
    /**
     * Field constructor.
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }
    abstract public function renderInput(): string;

    public function __toString(): string
    {
        return sprintf('
              <div class="from-group mb-3">
                  <label>%s</label>
                  %s
                  <div class="invalid-feedback">%s</div>
              </div>
        ',
            $this->model->getLabel($this->attribute),
            $this->renderInput(),
            $this->model->getFirstError($this->attribute)

        );
    }

}