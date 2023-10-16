<?php

namespace MVC\Form;

use MVC\View\View;

class FormView extends View
{

	public AbstractForm $form;

	public static function createFromForm(AbstractForm $form): self
	{
		$view = new self($form->getOption('view_template'));
		$view->form = $form;
		return $view;
	}

}