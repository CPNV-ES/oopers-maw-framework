<?php

namespace MVC\Form\Field;

class ChoiceField extends AbstractField
{

	public function __construct(string $id, mixed $value, \ReflectionProperty $property, object $entity)
	{
		parent::__construct($id, $value, $property, $entity);
		$this->setOption('view_template', 'form.choice-field');
	}

	/**
	 * @return ChoiceParam[]
	 */
	public function getChoices(): array
	{
		return $this->getOption('choices');
	}

    public function setValue(mixed $value): AbstractField
    {
        if ($this->getOption('constraint')) {
            $this->setError($this->getOption('constraint')($value));
        }
        $this->value = $value;
        $this->updateChoices($this->getOption('choices'), $this->value);
        $this->updateEntity();
        return $this;
    }

	public function build(): self
	{
		$value = $this->getEntityGetMethod()->invoke($this->entity);
		$this->setOption('choices', $this->updateChoices($this->getOption('choices'), $value));
		return $this;
	}

    private function updateChoices(array $choices, int|string|null $value): array
    {
        return array_map(function ($choice) use ($value) {
            return $choice->defineAsSelected($value);
        }, $choices);
    }

}