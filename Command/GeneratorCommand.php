<?php

namespace Pgs\RestfonyBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand as BaseGeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Here to modify skeleton dirs....need to figure out better way around that.
 * Some items borrowed from the GenerateDoctrineCommand and consolidated.
 */
abstract class GeneratorCommand extends BaseGeneratorCommand
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @param BundleInterface $bundle
     *
     * @return array
     */
    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/PgsRestBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/PgsRestBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $reflectionClass = new \ReflectionClass(get_class($this));

        $skeletonDirs[] = dirname($reflectionClass->getFileName()).'/../Resources/skeleton';
        $skeletonDirs[] = dirname($reflectionClass->getFileName()).'/../Resources';

        return $skeletonDirs;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return class_exists('Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle');
    }

    /**
     * @param $shortcut
     *
     * @return array
     */
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)',
                $entity
            ));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    /**
     * @param $entity
     *
     * @return array
     */
    protected function getEntityMetadata($entity)
    {
        $factory = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        return $factory->getClassMetadata($entity)->getMetadata();
    }

    /**
     * The magic.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->isInteractive() && !$this->getQuestionHelper()->ask(
            $input,
            $output,
            new ConfirmationQuestion('Do you confirm generation of the '.$this->getFileTypeCreated().'? ', false)
        )) {
            $output->writeln('<error>Command aborted</error>');

            return 1;
        }

        $this->entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($this->entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle   = $this->getApplication()->getKernel()->getBundle($bundle);
        $generator = $this->getGenerator();
        $generator->setFilesystem($this->getContainer()->get('filesystem'));

        try {
            $this->setOptions($input);
            $generator->generate($bundle, $entity, $metadata[0], $this->getOptions());

            $output->writeln(sprintf(
                '<info>The new %s %s file has been created under %s.</info>',
                $generator->getGeneratedName(),
                $this->getFileTypeCreated(),
                $generator->getFilePath()
            ));
        } catch (\Exception $e) {
            $output->writeln("<error>".$this->getFailureMessage($e)."</error>");
        }
    }
}
