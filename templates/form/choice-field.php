<?php

use MVC\Form\Field\ChoiceField;

/** @var ChoiceField $field */
$field = $this->field;

?>
<div class="form-control">
	<?php if ($field->getOption('label')): ?><label
        for="<?= $field->getId() ?>"><?= $field->getLabel() ?></label><?php endif; ?>
    <select name="<?= $field->getName() ?>" id="<?= $field->getId() ?>">
		<?php foreach ($field->getChoices() as $choice): ?>
			<?php if (is_array($choice->value)): ?>
                <optgroup label="<?= $choice->label ?>">
					<?php foreach ($choice->value as $subChoice): ?>
                        <option value="<?= $subChoice->value ?>" <?= $subChoice->isSelected() ? 'selected' : '' ?>><?= $subChoice->label ?></option>
					<?php endforeach; ?>
                </optgroup>
			<?php else: ?>
                <option value="<?= $choice->value ?>" <?= $choice->isSelected() ? 'selected' : '' ?>><?= $choice->label ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
    </select>
	<?php if ($field->hasError()): ?><span class="error"><?= $field->getErrorMessage() ?></span><?php endif; ?>
</div>