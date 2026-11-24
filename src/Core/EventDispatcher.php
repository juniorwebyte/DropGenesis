<?php

/*
 * ==========================================================================
 * DropGenesis — EventDispatcher
 * Lightweight hook and event bus for the installation pipeline.
 *
 * Built-in events:
 *   before_install, after_install,
 *   before_migration, after_migration,
 *   before_seed, after_seed,
 *   before_finish, after_finish, on_error
 *
 * Usage:
 *   $dispatcher = EventDispatcher::getInstance();
 *   $dispatcher->on('after_install', function(array $payload) {
 *       // your custom logic here
 *   });
 *   $dispatcher->fire('after_install', ['status' => 'ok']);
 * ==========================================================================
 */

namespace DropGenesis\Core;

class EventDispatcher
{
    /** @var array<string, array{callback:callable,priority:int}[]> */
    private array $listeners = [];

    private static ?EventDispatcher $instance = null;

    /** Built-in lifecycle event names */
    public const EVENTS = [
        'before_install',
        'after_install',
        'before_migration',
        'after_migration',
        'before_seed',
        'after_seed',
        'before_finish',
        'after_finish',
        'on_error',
    ];

    private function __construct() {}

    /** Singleton accessor */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a listener for a given event.
     *
     * @param string   $event    Event name (e.g. 'after_install')
     * @param callable $callback Callable receiving array $payload
     * @param int      $priority Higher values run first (default 10)
     */
    public function on(string $event, callable $callback, int $priority = 10): void
    {
        $this->listeners[$event][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        usort(
            $this->listeners[$event],
            static fn($a, $b) => $b['priority'] <=> $a['priority']
        );
    }

    /**
     * Fire an event, calling all registered listeners in priority order.
     * A listener returning (bool) false stops propagation.
     *
     * @param  string $event   Event name
     * @param  array  $payload Data passed to each listener
     * @return bool   false if propagation was stopped, true otherwise
     */
    public function fire(string $event, array $payload = []): bool
    {
        if (empty($this->listeners[$event])) {
            return true;
        }

        foreach ($this->listeners[$event] as $entry) {
            $result = ($entry['callback'])($payload);
            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all listeners for a specific event (or all events if null).
     */
    public function flush(?string $event = null): void
    {
        if ($event !== null) {
            unset($this->listeners[$event]);
        } else {
            $this->listeners = [];
        }
    }

    /**
     * Returns list of events that have at least one listener registered.
     * @return string[]
     */
    public function registeredEvents(): array
    {
        return array_keys($this->listeners);
    }

    /** Total number of registered listeners across all events. */
    public function count(): int
    {
        return (int) array_sum(array_map('count', $this->listeners));
    }
}
