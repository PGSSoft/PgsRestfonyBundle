<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pgs\RestfonyBundle\Command;

use Pgs\RestfonyBundle\Generator\DoctrineCrudGenerator;
use Pgs\RestfonyBundle\Generator\DoctrineFormGenerator;
use Pgs\RestfonyBundle\Generator\DoctrineManagerGenerator;
use Pgs\RestfonyBundle\Generator\DoctrineRepositoryGenerator;
use Pgs\RestfonyBundle\Generator\DoctrineSerializationConfigGenerator;
use Pgs\RestfonyBundle\Manipulator\RestConfigManipulator;
use Pgs\RestfonyBundle\Manipulator\RoutingManipulator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;

/**
 * Generates a Restful CRUD for a Doctrine entity.
 *
 * @author Lech Groblewicz <lgroblewicz@pgs-soft.com>
 */
class GenerateCrudCommand extends GeneratorCommand
{
    protected $serializationConfigGenerator;
    protected $managerGenerator;
    protected $repositoryGenerator;
    protected $entity;
    protected $bundle;
    private $formGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::OPTIONAL, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>pgs:generate:crud</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php app/console pgs:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console pgs:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>

Every generated file is based on a template. There are default templates but they can be overridden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/crud
APP_PATH/Resources/SensioGeneratorBundle/skeleton/crud</info>

And

<info>__bundle_path__/Resources/SensioGeneratorBundle/skeleton/form
__project_root__/app/Resources/SensioGeneratorBundle/skeleton/form</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )
            ->setName('pgs:generate:crud')
        ;
    }

    /**
     * @see Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $this->entity = $entity;

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');

        $questionHelper->writeSection($output, 'CRUD generation');

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata    = $this->getEntityMetadata($entityClass);
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);
        $this->bundle = $bundle;

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $withWrite, $forceOverwrite);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        // form
        $output->write('Generating the Form code: ');
        if ($this->generateForm($bundle, $entity, $metadata, $forceOverwrite)) {
            $output->writeln('<info>OK</info>');
        } else {
            $output->writeln('<comment>Already exists, skipping</comment>');
        }

        $output->write('Generating the Repository code: ');
        if ($this->generateRepository($bundle, $entity, $forceOverwrite)) {
            $output->writeln('<info>OK</info>');
        } else {
            $output->writeln('<comment>Already exists, skipping</comment>');
        }

        $output->write('Generating the Manager code: ');
        if ($this->generateManager($bundle, $entity, $forceOverwrite)) {
            $output->writeln('<info>OK</info>');
        } else {
            $output->writeln('<comment>Already exists, skipping</comment>');
        }

        $runner($this->updateRouting($questionHelper, $input, $output, $bundle, $format, $entity, $prefix));

        $output->write('Generating the serialization config: ');
        if ($this->generateSerializationConfig($bundle, $entity, $metadata, $forceOverwrite)) {
            $output->writeln('<info>OK</info>');
        } else {
            $output->writeln('<comment>Already exists, skipping</comment>');
        }

        $runner($this->updateRestRouting($questionHelper, $input, $output, $bundle, $entity, $metadata));

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Doctrine2 CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        if ($input->hasArgument('entity') && $input->getArgument('entity') !== '') {
            $input->setOption('entity', $input->getArgument('entity'));
        }

        $question = new Question($questionHelper->getQuestion('The Entity shortcut name', $input->getOption('entity')), $input->getOption('entity'));
        $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'));
        $entity = $questionHelper->ask($input, $output, $question);
        $input->setOption('entity', $entity);
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        // write?
        $withWrite = (bool) $input->getOption('with-write');

        $output->writeln(array(
            '',
            'By default, the generator creates two actions: list and show.',
            'You can also ask it to generate "write" actions: new, update, and delete.',
            '',
        ));
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want to generate the "write" actions', $withWrite ? 'yes' : 'no', '?', $withWrite), $withWrite);

        $withWrite = $questionHelper->ask($input, $output, $question);
        $input->setOption('with-write', $withWrite);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a REST CRUD controller for \"<info>%s:%s</info>\"", $bundle, $entity),
            '',
        ));
    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateSerializationConfig($bundle, $entity, $metadata, $forceOverwrite)
    {
        try {
            $this->getSerializationConfigGenerator($bundle, $entity)->generate($metadata[0], $forceOverwrite);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata, $forceOverwrite)
    {
        try {
            $this->getFormGenerator($bundle, $entity, $metadata[0])->generate($forceOverwrite);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    protected function generateManager($bundle, $entity, $forceOverwrite)
    {
        try {
            $this->getManagerGenerator($bundle, $entity)->generate($forceOverwrite);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    protected function generateRepository($bundle, $entity, $forceOverwrite)
    {
        try {
            $this->getRepositoryGenerator($bundle, $entity)->generate($forceOverwrite);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    protected function updateRestRouting(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, BundleInterface $bundle, $entity, $metadata)
    {
        $output->write('Importing REST config: ');
        $this->getContainer()->get('filesystem')->mkdir($bundle->getPath().'/Resources/config/');
        $config = new RestConfigManipulator($this->getContainer()->get('kernel')->getRootDir().'/config/rest.yml');
        $config->addResource($bundle->getNamespace(), $entity, $metadata);
    }

    protected function updateRouting(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, BundleInterface $bundle, $format, $entity, $prefix)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Confirm automatic update of the Routing', 'yes', '?'), true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('Importing the CRUD routes: ');
        $this->getContainer()->get('filesystem')->mkdir($bundle->getPath().'/Resources/config/');
        $routing = new RoutingManipulator($bundle->getPath().'/Resources/config/rest_routing.yml');

        $resourceAdded = false;

        if ($auto) {
            $resourceAdded = $routing->addResource($bundle->getName(), '/'.$prefix);
        }

        if (!$resourceAdded) {
            $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing/%s.%s\"</comment>\n", $bundle->getName(), strtolower(str_replace('\\', '_', $entity)), $format);
            $help .= sprintf("        <comment>prefix:   /%s</comment>\n", $prefix);

            return array(
                '- Import the bundle\'s routing resource in the bundle routing file',
                sprintf('  (%s).', $bundle->getPath().'/Resources/config/rest_routing.yml'),
                '',
                sprintf('    <comment>%s:</comment>', $bundle->getName().('' !== $prefix ? '_'.str_replace('/', '_', $prefix) : '')),
                $help,
                '',
            );
        }
    }

    protected function getRoutePrefix(InputInterface $input, $entity)
    {
        $prefix = $input->getOption('route-prefix') ?: strtolower(str_replace(array('\\', '/'), '_', $entity));

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    protected function createGenerator()
    {
        return new DoctrineCrudGenerator($this->getContainer()->get('filesystem'), $this->bundle, $this->entity);
    }

    protected function getFormGenerator(BundleInterface $bundle, $entity, $metadata)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($bundle, $entity, $metadata);
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    protected function getSerializationConfigGenerator(BundleInterface $bundle, $entity)
    {
        if (null === $this->serializationConfigGenerator) {
            $this->serializationConfigGenerator = new DoctrineSerializationConfigGenerator($bundle, $entity);
            $this->serializationConfigGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->serializationConfigGenerator;
    }

    public function setFormGenerator(DoctrineFormGenerator $formGenerator)
    {
        $this->formGenerator = $formGenerator;
    }

    protected function getManagerGenerator(BundleInterface $bundle, $entity)
    {
        if (null === $this->managerGenerator) {
            $this->managerGenerator = new DoctrineManagerGenerator($bundle, $entity);
            $this->managerGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->managerGenerator;
    }

    protected function getRepositoryGenerator(BundleInterface $bundle, $entity)
    {
        if (null === $this->repositoryGenerator) {
            $this->repositoryGenerator = new DoctrineRepositoryGenerator($bundle, $entity);
            $this->repositoryGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->repositoryGenerator;
    }
}
