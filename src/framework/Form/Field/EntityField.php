<?php

namespace MVC\Form\Field;

class EntityField extends TextField
{

	public function __construct(string $id, mixed $value, \ReflectionProperty $property, object $entity)
	{
		parent::__construct($id, $value, $property, $entity);
	}

	public function build(): self
	{
		$entities = $this->getEntityGetMethod()->invoke($this->entity);
		if (!is_array($entities)) $entities = [$entities];
		foreach ($entities as $entity) {
			$labelvalue = (new \ReflectionMethod($entity, 'get' . ucfirst($this->getOption('entity_label'))))->invoke($entity);
			$field = (self::createFromFormBuilder($this->getOption('entity_value'), TextField::class, $entity, ['label' => (string)(new \ReflectionMethod($entity, 'get' . ucfirst($this->getOption('entity_label'))))->invoke($entity)]));
			$this->children[$labelvalue->getId()] = $field->setName($this->getProperty()->getName() . "[".(new \ReflectionMethod($entity, 'get' . ucfirst($this->getOption('entity_label'))))->invoke($entity)->getId()."]");
		}
		return $this;
	}

	public function render(): string
	{
		return array_reduce($this->children, function ($curs, $field) {
			$curs .= (new FieldView($this->getOption('view_template'), ['field' => $field]))->render();
			return $curs;
		}, "");
	}

}