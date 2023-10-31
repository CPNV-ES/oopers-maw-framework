<?php

namespace MVC\View;

use MVC\Kernel;

/**
 * Class used represent a view and render using ob PHP functions
 */
class View implements ViewInterface
{

	public ?string $views_path = '%kernel.project_dir%/views/';

	protected string $template;

	protected ?ContextInterface $context;

	public function __construct(string $template, ?ContextInterface $context = null)
	{
		$this->template = $template;
		$this->context = $context ?? new Context();
	}

	public function render(Context|array $context = []): string
	{
		if (is_array($context) && is_null($this->context)) {
			$context = (new Context())->setVars($context);
		} elseif(is_array($context) && !is_null($this->context)) {
			$context = $this->context->mergeVars($context);
		}

		$path = $this->views_path;

		$path = str_replace(['.'], ['/'], Kernel::kernelVarsToString($path . $this->template));

		if (!str_ends_with($this->template, '.php')) {
			$path .= '.php';
		}

		try {
			ob_start();
			require $path;
			$content = ob_get_clean();
		} catch (\Throwable $e) {
			return "Unable to render view";
		}
		return $content;
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function setContext(ContextInterface $context): ViewInterface
	{
		$this->context = $context;
		return $this;
	}
}