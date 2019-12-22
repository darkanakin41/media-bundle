<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Command;

use Darkanakin41\MediaBundle\Model\File;
use Darkanakin41\MediaBundle\Twig\FileInfo;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaRefreshCommand extends Command
{
    protected static $defaultName = 'darkanakin41:media:refresh';

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var FileInfo
     */
    private $fileInfo;

    public function __construct(ManagerRegistry $managerRegistry, FileInfo $fileInfo, string $name = null)
    {
        parent::__construct($name);
        $this->managerRegistry = $managerRegistry;
        $this->fileInfo = $fileInfo;
    }

    protected function configure()
    {
        $this->setDescription('Refresh file meta data');
        $this->setHelp('Refresh file meta data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            'Darkanakin41 Media Refresh',
            '============',
            '',
        ));

        $output->writeln(sprintf('Retrieval of media to update'));

        /** @var File[] $files */
        $files = $this->managerRegistry->getRepository(File::class)->findAll();

        $progressBar = new ProgressBar($output, count($files));
        $progressBar->setFormat('Update media : %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $progressBar->start();
        foreach ($files as $file) {
            $progressBar->display();
            $this->fileInfo->refresh($file);
            $this->managerRegistry->getManager()->flush();
        }
        $progressBar->finish();

        $output->writeln('');
    }
}
