<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2018 Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\SSLTest\AppInfo;

use OCA\SSLTest\Notifications\Notifier;
use OCP\AppFramework\App;
use OCP\L10N\IFactory as L10NFactory;
use OCP\Notification\IManager as NotificationsManager;

class Application extends App {

	const appID = 'ssltest';

	public function __construct() {
		parent::__construct(self::appID);
	}

	public function registerNotifier() {
		$container = $this->getContainer();

		/** @var NotificationsManager $manager */
		$manager = $container->query(NotificationsManager::class);
		$manager->registerNotifier(
			function () use ($container) {
				return $container->query(Notifier::class);
			},
			function () use ($container) {
				/** @var L10NFactory $l10n */
				$l10n = $container->query(L10NFactory::class);
				$l = $l10n->get(self::appID);
				return [
					self::appID,
					$l->t('SSL Labs SSL test'),
				];
			}
		);
	}
}
