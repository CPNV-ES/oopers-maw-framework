<?php

namespace MVC\Form\Field;

class TextField extends AbstractField
{

	private string $view_template = "form.text_type";

	public function render(): string
	{
		return "";
	}
}