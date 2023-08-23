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

namespace libasynCurl\tasks;

use Closure;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use function is_array;

/**
 * @class DeleteTask
 * @package libasynCurl\tasks
 */
class DeleteTask extends CurlTask {
	/** @var string */
	protected string $args;
	public function __construct(string $url, array|string $args, int $timeout, array $headers, Closure $closure = null) {
		if (is_array($args)) {
			$this->args = json_encode($args, JSON_THROW_ON_ERROR);
		} else {
			$this->args = $args;
		}
		parent::__construct($url, $timeout, $headers, $closure);
	}

	public function onRun(): void {
		$this->setResult(self::deleteUrl($this->url, $this->args, $this->timeout, $this->getHeaders()));
	}

	private static function deleteUrl(string $url, string|array $args, int $time = 10, array $extraHeaders = [], &$err = null): ?InternetRequestResult {
		try {
			return Internet::simpleCurl($url, $time, $extraHeaders, [CURLOPT_CUSTOMREQUEST => "DELETE", CURLOPT_POSTFIELDS => $args]);
		} catch (InternetException $exception) {
			return null;
		}
	}
}