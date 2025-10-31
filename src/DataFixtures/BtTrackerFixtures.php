<?php

declare(strict_types=1);

namespace IpCollectionBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use IpCollectionBundle\Entity\BtTracker;

class BtTrackerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $trackers = [
            ['http', 'tracker.opentrackr.org', 1337],
            ['udp', 'tracker.opentrackr.org', 1337],
            ['http', 'tracker.openbittorrent.com', 80],
            ['udp', 'tracker.openbittorrent.com', 6969],
            ['http', 'tracker.publicbt.com', 80],
            ['udp', 'tracker.publicbt.com', 80],
            ['http', 'bt.xxx-tracker.com', 2710],
            ['udp', 'bt.xxx-tracker.com', 2710],
            ['http', '9.rarbg.to', 2710],
            ['udp', '9.rarbg.to', 2710],
        ];

        foreach ($trackers as $index => $trackerData) {
            $tracker = new BtTracker();
            $tracker->setScheme($trackerData[0]);
            $tracker->setHost($trackerData[1]);
            $tracker->setPort($trackerData[2]);

            $manager->persist($tracker);
            $this->addReference('bt-tracker-' . $index, $tracker);
        }

        $manager->flush();
    }
}
