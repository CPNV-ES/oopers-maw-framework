<div class="field">
	<?php if ($this->field->getOption('label')): ?><label
        for="<?= $this->field->getId() ?>"><?= $this->field->getLabel() ?></label><?php endif; ?>
    <textarea name="<?= $this->field->getName() ?>" id="<?= $this->field->getId() ?>" <?= $this->field->getAttributes() ?>><?= $this->field->getValue() ?></textarea>
	<?php if ($this->field->hasError()): ?><span class="error"><?= $this->field->getErrorMessage() ?></span><?php endif; ?>
</div>