<?php

namespace MVC\Http\Routing;

use Exception;
use InvalidArgumentException;
use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Kernel;
use ORM\DatabaseOperations;
use ORM\Mapping\Table;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Interpret Controller action parameters to bind request parameters in called method
 */
class ParamConverter
{

    private ReflectionMethod $reflectionMethod;

    /**
     * @throws ReflectionException
     */
    public function __construct(
        private readonly AbstractRoute $route
    ) {
        $this->reflectionMethod = new ReflectionMethod(
            $this->route->getController(),
            $this->route->getControllerMethod()
        );
    }

    /**
     * Loop threw method parameters and parameters with same names will be return in array <key as $variable, value as parameter passed value>
     * @param Request $request
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getParams(Request $request): array
    {
        $methodParams = $this->reflectionMethod->getParameters();
        $params = $request->params;

        return array_reduce($methodParams, function ($past, $item) use ($params) {
            /** @var ReflectionParameter $item */
            $table = null;
            if ($item->getType() instanceof ReflectionUnionType) {
                foreach ($item->getType()->getTypes() as $type) {
                    if (!class_exists($type->getName())) {
                        continue;
                    }
                    $entity = $type->getName();
                    $table = (new ReflectionClass($type->getName()))->getAttributes(Table::class)[0] ?? null;
                }
            } elseif (class_exists($item->getType()->getName())) {
                $table = (new ReflectionClass($item->getType()->getName()))->getAttributes(Table::class)[0] ?? null;
                $entity = $item->getType()->getName();
            }

            if ($table) {
                $validParamsNames = [];
                $parts = explode('\\', $entity);
                $entity_name = $parts[array_key_last($parts)];
                $validParamsNames[] = strtolower($entity_name[0] . "_id");
                $validParamsNames[] = strtolower($entity_name . "_id");
                $validParamsNames[] = 'id';
                $validParamsNames[] = strtolower($entity_name[0]) . "Id";
                $validParamsNames[] = strtolower($entity_name) . "Id";
                $validParamsNames[] = strtolower($entity_name);

                foreach ($validParamsNames as $name) {
                    if ($params->get($name)) {
                        $past[$item->getName()] = Kernel::container()->get(DatabaseOperations::class)->fetchOneOrThrow(
                            $entity,
                            ['id' => $params->get($name)->value]
                        );
                        $params->removeElement($params->get($name));
                    }
                }
            }

            if ($params->containsKey($item->getName())) {
                $past[$item->getName()] = $params->get($item->getName())->value;
            } elseif (!isset($past[$item->getName()])) {
                $past[$item->getName()] = match ($item->getType()->getName()) {
                    HTTPStatus::class => $this->convertStatus($item->getName()),
                    default => Kernel::getInstance()->container->get($item->getType()->getName())
                };
            }

            return $past;
        }, []);
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function convertStatus(string $name): mixed
    {
        if (!$this->reflectionMethod->getAttributes(\MVC\Http\Routing\Annotation\ErrorRoute::class)[0]) {
            throw new InvalidArgumentException(
                "Parameter `$name` in {$this->reflectionMethod->class} in method named `{$this->reflectionMethod->name}`"
            );
        }
        $attribute = $this->reflectionMethod->getAttributes(\MVC\Http\Routing\Annotation\ErrorRoute::class)[0];
        /** @var \MVC\Http\Routing\Annotation\ErrorRoute $attrInstance */
        $attrInstance = $attribute->newInstance();
        return $attrInstance->status;
    }

}