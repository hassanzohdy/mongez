<?php
namespace HZ\Illuminate\Organizer\Events;

use App;
use HZ\Illuminate\Organizer\Contracts\Events\EventsInterface;

class Events implements EventsInterface
{
    /**
     * Events List
     * 
     * @var array
     */
    protected $eventsList = [];

    /**
     * Classes List
     * 
     * @var array
     */
    protected $classesList = [];

    /**
     * An alias to trigger method
     * 
     * @see $this->trigger
     */
    public static function emit(...$args)
    {
        return $this->trigger(...$args);
    }

    /** 
     * {@inheritDoc}
     */
    public function trigger(string $events, ...$callbackArguments)
    {
        foreach (explode(' ', $events) as $event) {
            if (!isset($this->eventsList[$event])) continue;

            foreach ($this->eventsList[$event] as $callbackString) {
                if (!$this->isLoaded($callbackString)) {
                    $this->load($callbackString);
                }

                list($classObject, $method) = $this->get($callbackString);

                $return = $classObject->$method(...$callbackArguments);

                if ($return === false) return false;

                if (!is_null($return)) return $return;
            }
        }

        return true;
    }

    /**
     * Check if the given class is loaded
     * 
     * @param  string $class
     * @return bool
     */
    protected function isLoaded(string $class): bool
    {
        list($class, $method) = explode('@', $class);

        return isset($this->classesList[$class]);
    }

    /**
     * Load the object of the given class
     * 
     * @param  string $class
     * @return void 
     */
    protected function load(string $class)
    {
        list($class, $method) = explode('@', $class);

        $this->classesList[$class] = App::make($class);
    }

    /**
     * Get the class object and the method for the event
     * If the class doesn't have the method name i.e classPath@methodName
     * the `handle` method will be called instead
     * 
     * @param  string $class
     * @return array [$classObject, $methodName]
     */
    protected function get(string $class): array
    {
        list($class, $method) = explode('@', $class);

        return [$this->classesList[$class], $method ?: 'handle'];
    }

    /**
     * {@inherit}
     */
    public function subscribe(string $events, $eventListener)
    {
        foreach (explode(' ', $events) as $event) {
            if (!isset($this->eventsList[$event])) {
                $this->eventsList[$event] = [];
            }

            if (is_array($eventListener)) {
                $eventListener = implode('@', $eventListener);
            }

            if (!in_array($eventListener, $this->eventsList[$event])) {
                $this->eventsList[$event][] = $eventListener;
            }
        }
    }
}
