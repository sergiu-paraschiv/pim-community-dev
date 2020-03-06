<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionHandler
{
    /** @var SelectConnectionsEventCountByDayQuery */
    private $selectConnectionsEventCountByDayQuery;

    public function __construct(SelectConnectionsEventCountByDayQuery $selectConnectionsEventCountByDayQuery)
    {
        $this->selectConnectionsEventCountByDayQuery = $selectConnectionsEventCountByDayQuery;
    }

    public function handle(CountDailyEventsByConnectionQuery $query): array
    {
        $dateTimeZone = new \DateTimeZone($query->timezone());

        [$fromUtcDateTime, $upToUtcDateTime] = $this->createUtcDateTimeInterval(
            $query->startDate(),
            $query->endDate(),
            $dateTimeZone
        );

        $hourlyEventsPerConnection = $this
            ->selectConnectionsEventCountByDayQuery
            ->execute($query->eventType(), $fromUtcDateTime, $upToUtcDateTime);

        $weeklyEventCounts = [];
        foreach ($hourlyEventsPerConnection as $connectionCode => $hourlyEvents) {
            $dailyTimezonedEvents = $this->groupByDailyTimezonedEvent($hourlyEvents, $dateTimeZone);

            $weeklyEventCounts[] = new WeeklyEventCounts(
                $connectionCode,
                $query->startDate(),
                $query->endDate(),
                $dailyTimezonedEvents
            );
        }

        return $weeklyEventCounts;
    }

    private function createUtcDateTimeInterval(string $startDate, string $endDate, \DateTimeZone $dateTimeZone): array
    {
        $fromDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate, $dateTimeZone)
            ->setTime(0, 0)
            ->setTimezone(new \DateTimeZone('UTC'));

        $upToDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate, $dateTimeZone)
            ->setTime(0, 0)
            ->add(new \DateInterval('P1D'))
            ->setTimezone(new \DateTimeZone('UTC'));

        return [$fromDateTime, $upToDateTime];
    }

    private function groupByDailyTimezonedEvent(array $hourlyEvents, \DateTimeZone $timezone): array
    {
        return array_reduce($hourlyEvents, function (array $dailyEvents, array $hourlyEvent) use ($timezone) {
            [$eventDateTime, $eventCount] = $hourlyEvent;

            $eventDate = $eventDateTime->setTimezone($timezone)->format('Y-m-d');

            if (false === isset($dailyEvents[$eventDate])) {
                $dailyEvents[$eventDate] = 0;
            }
            $dailyEvents[$eventDate] += $eventCount;

            return $dailyEvents;
        }, []);
    }
}
