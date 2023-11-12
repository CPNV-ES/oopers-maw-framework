
<div class="form-control">
	<?php if ($this->field->getOption('label')): ?><label
        for="<?= $this->field->getId() ?>"><?= $this->field->getLabel() ?></label><?php endif; ?>
    <input type="text" id="<?= $this->field->getId() ?>" name="<?= $this->field->getName() ?>" <?= $this->field->getAttributes() ?>
           value="<?= $this->field->getValue() ?>">
	<?php if ($this->field->hasError()): ?><span class="error"><?= $this->field->getErrorMessage() ?></span><?php endif; ?>
</div>