<?php
/**
 * Go! AOP framework
 *
 * @copyright Copyright 2011, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\Aop\Framework;

use Go\Aop\Intercept\MethodInvocation;
use Go\Aop\Support\AnnotatedReflectionMethod;
use ReflectionMethod;

/**
 * Abstract method invocation implementation
 *
 * @see Go\Aop\Intercept\MethodInvocation
 * @package go
 */
abstract class AbstractMethodInvocation extends AbstractInvocation implements MethodInvocation
{

    /**
     * Instance of object for invoking or null
     *
     * @var object
     */
    protected $instance = null;

    /**
     * Instance of reflection method for class
     *
     * @var null|ReflectionMethod
     */
    protected $reflectionMethod = null;

    /**
     * Name of the invocation class
     *
     * @var string
     */
    protected $className = '';

    /**
     * Constructor for method invocation
     *
     * @param string $className Class name
     * @param string $methodName Method to invoke
     * @param $advices array List of advices for this invocation
     */
    public function __construct($className, $methodName, array $advices)
    {
        parent::__construct($advices);
        $this->className        = $className;
        $this->reflectionMethod = $method = new AnnotatedReflectionMethod($this->className, $methodName);

        // Give an access to call protected method
        if ($method->isProtected()) {
            $method->setAccessible(true);
        }
    }

    /**
     * Gets the method being called.
     *
     * <p>This method is a friendly implementation of the
     * {@link Joinpoint::getStaticPart()} method (same result).
     *
     * @return AnnotatedReflectionMethod the method being called.
     */
    public function getMethod()
    {
        return $this->reflectionMethod;
    }

    /**
     * Returns the object that holds the current joinpoint's static
     * part.
     *
     * <p>For instance, the target object for an invocation.
     *
     * @return object|null the object (can be null if the accessible object is
     * static).
     */
    public function getThis()
    {
        return $this->instance;
    }

    /**
     * Returns the static part of this joinpoint.
     *
     * <p>The static part is an accessible object on which a chain of
     * interceptors are installed.
     * @return object
     */
    public function getStaticPart()
    {
        return $this->getMethod();
    }

    /**
     * Invokes current method invocation with all interceptors
     *
     * @param null|object|string $instance Invocation instance (class name for static methods)
     * @param array $arguments List of arguments for method invocation
     *
     * @return mixed Result of invocation
     */
    final public function __invoke($instance = null, array $arguments = array())
    {
        if ($this->level) {
            array_push($this->stackFrames, array($this->arguments, $this->instance, $this->current));
        }

        ++$this->level;

        $this->current   = 0;
        $this->instance  = $instance;
        $this->arguments = $arguments;

        $result = $this->proceed();

        --$this->level;

        if ($this->level) {
            list($this->arguments, $this->instance, $this->current) = array_pop($this->stackFrames);
        }

        return $result;
    }
}
