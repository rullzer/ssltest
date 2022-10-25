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

namespace OCA\SSLTest\Backgroundjob;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OCP\BackgroundJob\TimedJob;
use OCA\SSLTest\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJobList;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\Notification\IManager;

class StatusJob extends TimedJob {
	
	private IRequest $request;
	private IClientService $clientService;
	private IConfig $config;
	private IGroupManager $groupManager;
	private IManager $notificationManager;
	private ITimeFactory $timeFactory;
	private IJobList $joblist;

	public function __construct(IRequest $request,
								IClientService $clientService,
								IConfig $config,
								IGroupManager $groupManager,
								IManager $notificationManager,
								ITimeFactory $timeFactory,
								IJobList $joblist) {
		parent::__construct($timeFactory);

		//Run once 
		$this->setInterval(3600);

		$this->request = $request;
		$this->clientService = $clientService;
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->notificationManager = $notificationManager;
		$this->timeFactory = $timeFactory;
		$this->joblist = $joblist;
	}

	public function run($argument) {
		$client = $this->clientService->newClient();

		try {
			$response = $client->get(
				'https://api.ssllabs.com/api/v3/analyze',
				[
					'query' => [
						'host' => $this->request->getServerHost(),
						'fromCache' => 'on',
						'maxAge' => 168,
						'publish' => 'off',
					],
					'timeout' => 10,
				]
			);
		} catch (ServerException $e) {
			//Just ignore for now we try again next iteration
			return;
		} catch (ClientException $e) {
			//We messed up :S
			//TODO: log
			return;
		}

		$result = $response->getBody();
		$result = json_decode($result, true);

		if (!isset($result['status']) || $result['status'] !== 'READY') {
			// Scan still running so ignore
			return;
		}

		$data = $result['endpoints'][0];
		$curData = [
			'grade' => $data['grade'],
			'hasWarnings' => $data['hasWarnings'],
		];

		$prevData = $this->config->getAppValue(Application::APP_ID, 'lastData');
		$prevData = json_decode($prevData, true);

		if ($prevData !== null) {
			foreach ($prevData as $k => $v) {
				if ($curData[$k] !== $v) {
					$this->notify($curData['grade'], $curData['hasWarnings'], $this->request->getServerHost());
					break;
				}
			}
		} else {
			$this->notify($curData['grade'], $curData['hasWarnings'], $this->request->getServerHost());
		}

		// Save latest data
		$this->config->setAppValue(Application::APP_ID, 'lastData', json_encode($curData));

		// Remove the job since all is good now
		$this->joblist->remove(StatusJob::class);
	}

	private function notify(string $grade, bool $hasWarnings, string $host) {
		$notification = $this->notificationManager->createNotification();

		$time = $this->timeFactory->getTime();
		$dateTime = new \DateTime();
		$dateTime->setTimestamp($time);

		try {
			$notification->setApp('ssltest')
				->setDateTime($dateTime)
				->setObject(Application::APP_ID, 'ssllabs_result')
				->setSubject('newResult', [$grade, $hasWarnings, $host]);

			$admins = $this->groupManager->get('admin');
			foreach ($admins->getUsers() as $admin) {
				$notification->setUser($admin->getUID());
				$this->notificationManager->notify($notification);
			}
		} catch (\InvalidArgumentException $e) {
			return;
		}
	}
}
