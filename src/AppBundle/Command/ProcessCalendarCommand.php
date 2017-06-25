<?php
/**
 * User: acusset
 * Date: 23/06/2017
 * Time: 21:52
 */

namespace AppBundle\Command;


use AppBundle\Entity\Calendar\Calendar;
use AppBundle\Entity\Calendar\Event;
use Doctrine\ORM\EntityManager;
use ICal\ICal;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ProcessCalendarCommand extends ContainerAwareCommand
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Allowed file extensions
     */
    const FIlE_EXTESIONS = ['ics', 'ical'];

    protected function configure()
    {
        // TODO : add arguments to process a specific file or entry ?
        $this
            ->setName('bot:calendar:process')
            ->setDescription("Process and save a calendar to database");
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->fs = new Filesystem();
        $directory = $this->getContainer()->getParameter('ical_upload_directory');

        if (!$this->fs->exists($directory)) {
            $this->fs->mkdir($directory, 0755);
            // TODO : stop execution. No directory implies no files
        }

        $this->finder = new Finder();
        $this->finder->in($directory);

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO : add verbose outputs for debugging | Add Logs with number of events imported etc.
        /**
         * @var SplFileInfo $file
         */
        foreach ($this->finder as $file) {
            if (in_array($file->getExtension(), ProcessCalendarCommand::FIlE_EXTESIONS)) {

                $calendar = new Calendar($file->getFilename());
                $ical = new ICal($file->getRealPath());

                /**
                 * @var \Ical\Event $event
                 */
                foreach ($ical->events() as $event) {
                    $calendarEvent = new Event(
                        $event->uid,
                        $event->description,
                        $event->summary,
                        $event->created,
                        $event->lastmodified,
                        $event->dtstart,
                        $event->dtend,
                        $event->dtstamp
                    );

                    $calendarEvent->setCalendar($calendar);
                    $calendar->addEvent($calendarEvent);

                }
                $this->em->persist($calendar);
                $this->em->flush();
            }
        }
    }
}