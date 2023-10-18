<?php

use MVC\Form\Field\TextField;
use MVC\View\ContextInterface;

/** @var ContextInterface $context */
/** @var TextField $field */
$field = $context->field;

?>
<div class="form-control">
	<?php if ($field->getOption('label')): ?><label
        for="<?= $field->getId() ?>"><?= $field->getLabel() ?></label><?php endif; ?>
    <input type="text" id="<?= $field->getId() ?>" name="<?= $field->getName() ?>" <?= $field->getAttributes() ?>
           value="<?= $field->getValue() ?>">
	<?php if ($field->hasError()): ?><span class="error"><?= $field->getErrorMessage() ?></span><?php endif; ?>
</div>