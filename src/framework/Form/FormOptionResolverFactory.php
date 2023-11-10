<?php

namespace MVC\Form;

use MVC\OptionsResolver;

class FormOptionResolverFactory
{


	public static function create(): OptionsResolver
	{
		return (new OptionsResolver())
			->setAllowed([
				'view_template',
				'action_route',
				'attributes',
				'method',
			])
		;
		
	}

}