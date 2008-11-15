<?php

/**
 * This class, meant to be subclassed, is constructed
 * as a utility to allow components to further break
 * down delegates to manage large parts of functionality.
 * A Ford component, for instance, might have a HRHandler,
 * a PartsHandler, and so forth.
 *
 */
abstract class HandlerDelegate
{
    /**
     * The component this delegate is tied to
     *
     * @var Component
     */
    public $component;

    final public function __construct(Component &$component)
    {
        $this->component =& $component;
    }

    public function __get($property)
    {
        if (!property_exists($this->component, $property)) {
            throw new Exception("unknown property $property");
        }

        return $this->component->$property;
    }

    public function __set($property, $value)
    {
        if (!property_exists($this->component, $property)) {
            throw new Exception("unknown property $property");
        }

        $this->component->$property = $value;
    }

    public function __call($name, $args)
    {
        if (!method_exists($this->component, $name)) {
            throw new Exception("unknown method $name");
        }

        return call_user_func_array(array($this->component, $name), $args);
    }
}

?>