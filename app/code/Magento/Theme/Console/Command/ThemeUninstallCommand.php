<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\Cache;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\GeneralDependencyChecker;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Filesystem;
use Magento\Theme\Model\Theme\ThemeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\BackupRollback;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ConsoleLogger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Command for uninstalling theme and backup-code feature
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeUninstallCommand extends Command
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_BACKUP_CODE = 'backup-code';
    const INPUT_KEY_THEMES = 'theme';
    const INPUT_KEY_CLEAR_STATIC_CONTENT = 'clear-static-content';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $file;

    /**
     * @var GeneralDependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var ComposerInformation
     */
    private $composer;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Remove
     */
    private $remove;

    /**
     * Theme collection in filesystem
     *
     * @var \Magento\Theme\Model\Theme\Collection
     */
    private $themeCollection;

    /**
     * Provider for themes registered in db
     *
     * @var ThemeProvider
     */
    private $themeProvider;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var State\CleanupFiles
     */
    private $cleanupFiles;

    /**
     * Constructor
     *
     * @param Cache $cache
     * @param State\CleanupFiles $cleanupFiles
     * @param ComposerInformation $composer
     * @param DeploymentConfig $deploymentConfig
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerInterface $objectManager
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Filesystem $filesystem
     * @param GeneralDependencyChecker $dependencyChecker
     * @param \Magento\Theme\Model\Theme\Collection $themeCollection
     * @param ThemeProvider $themeProvider
     * @param Remove $remove
     * @param State $appState
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Cache $cache,
        State\CleanupFiles $cleanupFiles,
        ComposerInformation $composer,
        DeploymentConfig $deploymentConfig,
        MaintenanceMode $maintenanceMode,
        ObjectManagerInterface $objectManager,
        DirectoryList $directoryList,
        File $file,
        Filesystem $filesystem,
        GeneralDependencyChecker $dependencyChecker,
        \Magento\Theme\Model\Theme\Collection $themeCollection,
        ThemeProvider $themeProvider,
        Remove $remove,
        State $appState
    ) {
        $this->cache = $cache;
        $this->cleanupFiles = $cleanupFiles;
        $this->composer = $composer;
        $this->deploymentConfig = $deploymentConfig;
        $this->maintenanceMode = $maintenanceMode;
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->dependencyChecker = $dependencyChecker;
        $this->remove = $remove;
        $this->themeCollection = $themeCollection;
        $this->themeProvider = $themeProvider;
        $this->appState = $appState;
        $this->appState->setAreaCode(Area::AREA_ADMIN);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('theme:uninstall');
        $this->setDescription('Uninstall theme');
        $this->addOption(
            self::INPUT_KEY_BACKUP_CODE,
            null,
            InputOption::VALUE_NONE,
            'Take code backup (excluding temporary files)'
        );
        $this->addArgument(
            self::INPUT_KEY_THEMES,
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Path of the theme'
        );
        $this->addOption(
            self::INPUT_KEY_CLEAR_STATIC_CONTENT,
            'c',
            InputOption::VALUE_NONE,
            'Clear generated static view files. Necessary, if the module(s) have static view files'
        );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                '<error>You cannot run this command because the Magento application is not installed.</error>'
            );
            return;
        }

        $themePaths = $input->getArgument(self::INPUT_KEY_THEMES);
        $validationMessages = $this->validate($themePaths);
        if (!empty($validationMessages)) {
            $output->writeln($validationMessages);
            return;
        }
        $dependencyMessages = $this->checkDependencies($themePaths);
        if (!empty($dependencyMessages)) {
            $output->writeln($dependencyMessages);
            return;
        }

        $output->writeln('<info>Enabling maintenance mode</info>');
        $this->maintenanceMode->set(true);

        try {
            if ($input->getOption(self::INPUT_KEY_BACKUP_CODE)) {
                $backupRollback = new BackupRollback(
                    $this->objectManager,
                    new ConsoleLogger($output),
                    $this->directoryList,
                    $this->file
                );
                $backupRollback->codeBackup();
            }
            $output->writeln('<info>Removing ' . implode(', ', $themePaths) . ' from database');
            $this->removeFromDb($themePaths);
            $output->writeln('<info>Removing ' . implode(', ', $themePaths) . ' from Magento codebase');
            $themePackages = [];
            foreach ($themePaths as $themePath) {
                $themePackages[] = $this->getPackageName($themePath);
            }
            $this->remove->remove($themePackages);
            $this->cleanup($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }

    /**
     * Validate given full theme paths
     *
     * @param string[] $themePaths
     * @return string[]
     */
    private function validate($themePaths)
    {
        $messages = [];
        $unknownPackages = [];
        $unknownThemes = [];
        $installedPackages = $this->composer->getRootRequiredPackages();
        foreach ($themePaths as $themePath) {
            if (array_search($this->getPackageName($themePath), $installedPackages) === false) {
                $unknownPackages[] = $themePath;
            }
            if (!$this->themeCollection->hasTheme($this->themeCollection->getThemeByFullPath($themePath))) {
                $unknownThemes[] = $themePath;
            }
        }
        $unknownPackages = array_diff($unknownPackages, $unknownThemes);
        if (!empty($unknownPackages)) {
            $text = count($unknownPackages) > 1 ?
                ' are not installed composer packages' : ' is not an installed composer package';
            $messages[] = '<error>' . implode(', ', $unknownPackages) . $text . '</error>';
        }
        if (!empty($unknownThemes)) {
            $messages[] = '<error>Unknown theme(s): ' . implode(', ', $unknownThemes) . '</error>';
        }
        return $messages;
    }

    /**
     * Check dependencies to given full theme paths
     *
     * @param string[] $themePaths
     * @return string[]
     */
    private function checkDependencies($themePaths)
    {
        $messages = [];
        $packageToPath = [];
        foreach ($themePaths as $themePath) {
            $packageToPath[$this->getPackageName($themePath)] = $themePath;
        }
        $dependencies = $this->dependencyChecker->checkDependencies(array_keys($packageToPath), true);
        foreach ($dependencies as $package => $dependingPackages) {
            if (!empty($dependingPackages)) {
                $messages[] =
                    '<error>Cannot uninstall ' . $packageToPath[$package] .
                    " because the following package(s) depend on it:</error>" .
                    PHP_EOL . "\t<error>" . implode('</error>' . PHP_EOL . "\t<error>", $dependingPackages)
                    . "</error>";
            }
        }
        return $messages;
    }

    /**
     * Get package name of a theme by its full theme path
     *
     * @param string $themePath
     * @return string
     * @throws \Zend_Json_Exception
     */
    private function getPackageName($themePath)
    {
        $themesDirRead = $this->filesystem->getDirectoryRead(DirectoryList::THEMES);
        if ($themesDirRead->isExist($themePath . '/composer.json')) {
            $rawData = \Zend_Json::decode($themesDirRead->readFile($themePath . '/composer.json'));
            return $rawData['name'];
        }
        return '';
    }

    /**
     * Cleanup after updated modules status
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function cleanup(InputInterface $input, OutputInterface $output)
    {
        $this->cache->clean();
        $output->writeln('<info>Cache cleared successfully.</info>');

        $this->cleanupFiles->clearCodeGeneratedClasses();
        $output->writeln('<info>Generated classes cleared successfully.</info>');
        if ($input->getOption(self::INPUT_KEY_CLEAR_STATIC_CONTENT)) {
            $this->cleanupFiles->clearMaterializedViewFiles();
            $output->writeln('<info>Generated static view files cleared successfully.</info>');
        } else {
            $output->writeln(
                '<error>Alert: Generated static view files were not cleared.'
                . ' You can clear them using the --' . self::INPUT_KEY_CLEAR_STATIC_CONTENT . ' option.'
                . ' Failure to clear static view files might cause display issues in the Admin and storefront.</error>'
            );
        }
    }

    /**
     * Remove all records related to the theme(s) in the database
     *
     * @param string[] $themePaths
     * @return void
     */
    private function removeFromDb(array $themePaths)
    {
        foreach ($themePaths as $themePath) {
            $this->themeProvider->getThemeByFullPath($themePath)->delete();
        }
    }
}
