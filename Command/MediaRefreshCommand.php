<?php

namespace PLejeune\MediaBundle\Command;

use PLejeune\MediaBundle\Entity\File;
use PLejeune\MediaBundle\Repository\FileRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaRefreshCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('media:refresh');
        $this->setDescription('Rafraichit les méta des medias');
        $this->setHelp('Rafraichit les méta des medias');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $output->writeln(sprintf("Recuperation des medias a mettre a jour"));
        /**
         * @var FileRepository
         */
        $files = $doctrine->getRepository(File::class)->findAll();


        $i = 0;
        foreach ($files as $file) {
            $output->writeln(sprintf("%d/%d : Mise a jour de media", $i++, count($files)));
            $this->getContainer()->get('plejeune.media.fileinfo')->refresh($file);
            $doctrine->getManager()->flush();
        }
    }

}
