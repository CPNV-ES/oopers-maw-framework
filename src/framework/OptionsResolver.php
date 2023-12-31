<?php

namespace MVC;

use Exception;

class OptionsResolver
{

    /**
     * The default options
     * @var array
     */
    protected array $defaults = [];

    /**
     * The required options
     * @var array
     */
    protected array $required = [];

    /**
     * The allowed keys options
     * @var array
     */
    protected array $allowed = [];


    /**
     * @throws Exception
     */
    public function setDefaults(array $defaults = []): self
    {
        array_map(fn($value) => $this->isAllowed($value), array_keys($defaults));
        if (!empty($this->defaults)) {
            $this->defaults = array_merge_recursive($this->defaults, $defaults);
        } else {
            $this->defaults = $defaults;
        }
        return $this;
    }


    /**
     * @throws Exception
     */
    private function isAllowed(string $key): true
    {
        if (in_array($key, $this->allowed)) {
            return true;
        }
        throw new Exception("Invalid Option key '$key' use only allowed keys: {$this->getAllowedString()}.");
    }

    private function getAllowedString(): string
    {
        return join("', '", $this->allowed);
    }

    /**
     * @throws Exception
     */
    public function set(string $key, mixed $value): self
    {
        $this->isAllowed($key);
        $this->defaults[$key] = is_array($value) ? array_key_exists($key, $this->defaults) ? array_merge_recursive(
            $this->defaults[$key],
            $value
        ) : $value : $value;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function resolve(): array
    {
        if ($this->isResolvable()) {
            throw new Exception(
                "Unable to resolve due to undefined required options you have to defined following options: {$this->getRequiredGivenDiffString()}"
            );
        }
        return $this->defaults;
    }

    private function isResolvable(): bool
    {
        return empty($this->getRequiredGivenDiff());
    }

    private function getRequiredGivenDiff(): array
    {
        return array_diff_key($this->required, $this->defaults);
    }

    private function getRequiredGivenDiffString(): string
    {
        return join("', '", $this->getRequiredGivenDiff());
    }

    public function setRequired(array $required): OptionsResolver
    {
        if (!empty($this->required)) {
            $this->required = array_merge_recursive($this->required, $required);
        } else {
            $this->required = $required;
        }
        return $this;
    }

    public function setAllowed(array $allowed): OptionsResolver
    {
        if (!empty($this->allowed)) {
            $this->allowed = array_merge_recursive($this->allowed, $allowed);
        } else {
            $this->allowed = $allowed;
        }
        return $this;
    }

}