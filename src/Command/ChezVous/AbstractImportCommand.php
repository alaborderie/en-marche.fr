<?php

namespace App\Command\ChezVous;

use App\Entity\ChezVous\City;
use App\Repository\ChezVous\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractImportCommand extends Command
{
    protected const BATCH_SIZE = 500;

    protected $em;
    protected $cityRepository;
    protected $storage;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(
        EntityManagerInterface $em,
        CityRepository $cityRepository,
        FilesystemInterface $storage
    ) {
        $this->em = $em;
        $this->cityRepository = $cityRepository;
        $this->storage = $storage;

        parent::__construct();
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function findCity(string $inseeCode): ?City
    {
        return $this->cityRepository->findOneByInseeCode($inseeCode);
    }

    protected function createReader(string $filePath, ?int $headerOffset = 0): Reader
    {
        $csvContent = $this->storage->read($filePath);

        $reader = Reader::createFromString($csvContent);

        if (null !== $headerOffset) {
            $reader->setHeaderOffset($headerOffset);
        }

        return $reader;
    }
}
