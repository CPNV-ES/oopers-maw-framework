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
        if (!is_array($entities)) {
            $entities = [$entities];
        }
        foreach ($entities as $entity) {
            $labelvalue = (new \ReflectionMethod($entity, 'get' . ucfirst($this->getOption('entity_label'))))->invoke(
                $entity
            );
            $fieldType = $this->resolveFieldType($entity);
            $field = (self::createFromFormBuilder(
                $this->getOption('entity_value'),
                $fieldType,
                $entity,
                [
                    'label' => (string)(new \ReflectionMethod(
                        $entity, 'get' . ucfirst($this->getOption('entity_label'))
                    ))->invoke($entity),
                    'constraint' => $this->getOption("constraint")
                ]
            ));
            $this->children[$labelvalue->getId()] = $field->setName(
                $this->getProperty()->getName() . "[" . (new \ReflectionMethod(
                    $entity,
                    'get' . ucfirst($this->getOption('entity_label'))
                ))->invoke($entity)->getId() . "]"
            );
        }
        return $this;
    }


    private function resolveFieldType($entity): string
    {
        if (!array_key_exists('entity_type', $this->getOptions())) {
            return TextField::class;
        }
        $type = $this->getOption('entity_type');
        if (is_callable($type)) {
            return $type($entity);
        }
        return $type;
    }

    public function hasError(): bool
    {
        foreach ($this->children as $child) {
            if ($child->hasError()) return true;
        }
        return false;
    }

    public function render(): string
    {
        return array_reduce($this->children, function ($curs, $field) {
            $curs .= (new FieldView($field->getOption('view_template'), ['field' => $field]))->render();
            return $curs;
        }, "");
    }

}