<?php

namespace Scrapy\Traits;

/**
 * Trait HandleCallable.
 *
 * Defines helper functions for closure manipulations.
 *
 * @package Scrapy\Traits
 */
trait HandleCallable
{
    /**
     * Determines if given argument is a function.
     *
     * @param $function mixed Variable to examine.
     * @return bool True if provided argument is a function.
     */
	private function isFunction($function): bool
	{
		return is_callable($function);
	}

    /**
     * Calls given function with provided arguments.
     *
     * @param $function mixed Function to be called.
     * @param mixed ...$params Arguments to be passed to a function.
     * @return mixed|null Returns function result if given argument is a function, null otherwise.
     */
	private function callFunction($function, ...$params)
	{
		if ($this->isFunction($function)) {
			return call_user_func_array($function, $params);
		}

		return null;
	}
}
