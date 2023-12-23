<?php

namespace MVC\Form;

use MVC\Form\Field\AbstractField;
use MVC\View\View;

class FormView extends View
{

    public AbstractForm $form;

    public static function createFromForm(AbstractForm $form): self
    {
        $view = new self($form->getOption('view_template'));
        $view->form = $form;
        $view->add('form', $view);
        return $view;
    }

    public function field(string $name): AbstractField
    {
        return $this->form->getField($name);
    }

    public function fields(): string
    {
        $out = "";
        foreach ($this->form->getFields() as $field) {
            $out .= $field->render();
        }
        return $out;
    }

    public function start(array $attributes = []): string
    {
        return <<<HTML
			<form action="{$this->form->getOption('action_route')}" {$this->form->getAttributes(
        )} method="{$this->form->getOption('method')}">
		HTML;
    }

    public function end(): string
    {
        return <<<HTML
			</form>
		HTML;
    }

}