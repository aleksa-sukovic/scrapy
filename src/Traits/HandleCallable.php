<?php

namespace Scrapy\Traits;

trait HandleCallable
{
	private function isFunction($function): bool
	{
		return is_callable($function);
	}

	private function callFunction($function, ...$params)
	{
		if (is_callable($function)) {
			return call_user_func($function, ...$params);
		}

		return null;
	}
}
