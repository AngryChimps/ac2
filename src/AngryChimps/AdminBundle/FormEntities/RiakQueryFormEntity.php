<?php


namespace AngryChimps\AdminBundle\FormEntities;


class RiakQueryFormEntity {
    protected $class;
    protected $function;
    protected $argument;

    /**
     * @return mixed
     */
    public function getArgument()
    {
        return $this->argument;
    }

    /**
     * @param mixed $argument
     */
    public function setArgument($argument)
    {
        $this->argument = $argument;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $index
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param mixed $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

}