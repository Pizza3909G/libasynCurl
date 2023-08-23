<?php

/*
 *
 *  __  __                        _
 * |  \/  | __ _ _   _ _   _ _ __(_)
 * | |\/| |/ _` | | | | | | | '__| |
 * | |  | | (_| | |_| | |_| | |  | |
 * |_|  |_|\__,_|\__, |\__,_|_|  |_|
 *               |___/
 *
 * Copyright (c) 2022-2025 Mayuri and contributors
 *
 * Permission is hereby granted to any persons and/or organizations
 * using this software to copy, modify, merge, publish, and distribute it.
 * Said persons and/or organizations are not allowed to use the software or
 * any derivatives of the work for commercial use or any other means to generate
 * income, nor are they allowed to claim this software as their own.
 *
 * The persons and/or organizations are also disallowed from sub-licensing
 * and/or trademarking this software without explicit permission from Mayuri.
 *
 * Any persons and/or organizations using this software must disclose their
 * source code and have it publicly available, include this license,
 * provide sufficient credit to the original authors of the project (IE: Mayuri),
 * as well as provide a link to the original project.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR
 * PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author Mayuri
 *
 */

declare(strict_types=1);

namespace libasynCurl;

use Closure;
use http\Exception\InvalidArgumentException;
use libasynCurl\tasks\DeleteTask;
use libasynCurl\tasks\GetTask;
use libasynCurl\tasks\PostTask;
use libasynCurl\thread\CurlThreadPool;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

/**
 * @class Curl
 * @package libasynCurl
 */
class Curl {
	use SingletonTrait;
	/** @var bool */
	private bool $registered = false;
	/** @var CurlThreadPool */
	private CurlThreadPool $threadPool;
	public function register(PluginBase $plugin, int $memory_limit = 256, int $pull_size = 2, int $collect_interval = 1, int $garbage_collect_interval = 18000) : void {
		if ($this->isRegistered()) {
			throw new InvalidArgumentException("{$plugin->getName()} attempted to register Curl twice.");
		}
		$server = $plugin->getServer();
		$this->threadPool = ($thread = new CurlThreadPool($pull_size, $memory_limit, $server->getLoader(), $server->getLogger(), $server->getTickSleeper()));
		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($thread): void {
			$thread->collectTasks();
		}), $collect_interval);
		if ($garbage_collect_interval > 0) {
			$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($thread) : void {
				$thread->triggerGarbageCollector();
			}), $garbage_collect_interval);
		}
		$this->registered = true;
	}

	public function isRegistered(): bool {
		return $this->registered;
	}

	private function getThreadPool(): CurlThreadPool {
		return $this->threadPool;
	}

	public function post(string $url, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null): void {
		$this->getThreadPool()->submitTask(new PostTask($url, $args, $timeout, $headers, $closure));
	}

	public function get(string $url, int $timeout = 10, array $headers = [], Closure $closure = null): void {
		$this->getThreadPool()->submitTask(new GetTask($url, $timeout, $headers, $closure));
	}

	public function delete(string $url, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null): void {
		$this->getThreadPool()->submitTask(new DeleteTask($url, $args, $timeout, $headers, $closure));
	}
}