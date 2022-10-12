<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\SSLTest\Notifications;

use OCA\SSLTest\AppInfo\Application;
use OCP\L10N\IFactory as L10NFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var L10NFactory */
	private $L10NFactory;

	public function __construct(L10NFactory $L10NFactory) {
		$this->L10NFactory = $L10NFactory;
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10nFactory->get(Application::APP_ID)->t('Two factor reminder');
	}


	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new \InvalidArgumentException();
		}

		$l = $this->L10NFactory->get(Application::APP_ID, $languageCode);

		switch ($notification->getSubject()) {
			case 'newResult':
				$notification->setParsedSubject($l->t('Your server has received a new SSLLabs rating'));

				$args = $notification->getMessageParameters();

				$message = $l->t('Your new server rating is %s.', [$args[0]]);
				if ($args[1] === true) {
					$message .= $l->t('The scan found some warnings.');
				}
				$message .= $l->t('Please see the full report on ssllabs.com');

				$notification->setParsedMessage($message);

				$action = $notification->createAction();
				$action->setLink(<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\SSLTest\Notifications;

use OCA\SSLTest\AppInfo\Application;
use OCP\L10N\IFactory as L10NFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var L10NFactory */
	private $L10NFactory;

	public function __construct(L10NFactory $L10NFactory) {
		$this->L10NFactory = $L10NFactory;
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10nFactory->get(Application::APP_ID)->t('Two factor reminder');
	}


	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new \InvalidArgumentException();
		}

		$l = $this->L10NFactory->get(Application::APP_ID, $languageCode);

		switch ($notification->getSubject()) {
			case 'newResult':
				$notification->setParsedSubject($l->t('Your server has received a new SSLLabs rating'));

				$args = $notification->getMessageParameters();

				$message = $l->t('Your new server rating is %s.', [$args[0]]);
				if ($args[1] === true) {
					$message .= $l->t('The scan found some warnings.');
				}
				$message .= $l->t('Please see the full report on ssllabs.com');

				$notification->setParsedMessage($message);

				$action = $notification->createAction();
				$action->setLink("https://www.ssllabs.com/ssltest/analyze.html?d=" . $args[2]);
				$action->setLabel("See report on ssllabs.com");

				$notification->addAction($action);

				return $notification;
				break;
			default:
				throw new \InvalidArgumentException();
		}
	}
}
)

				return $notification;
				break;
			default:
				throw new \InvalidArgumentException();
		}
	}
}
