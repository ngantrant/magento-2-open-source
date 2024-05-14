<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Finder for a list of preconfigured words
 */
namespace Magento\TestFramework\Inspection;

class WordsFinder
{
    /**
     * File path public repo changed file list.
     */
    private const CHANGED_PUBLIC_REPO_FILE =
        '/dev/tests/static/testsuite/Magento/Test/_files/changed_files_public.txt';

    /**
     * File path private repo changed file list.
     */
    private const CHANGED_PRIVATE_REPO_FILE =
        '/dev/tests/static/testsuite/Magento/Test/_files/changed_files_private.txt';

    /**
     * @var string
     */
    private $prefix = '/var/www/html/';

    /**
     * List of file extensions, that indicate a binary file
     *
     * @var array
     */
    protected $_binaryExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'swf', 'mp3', 'avi', 'mov', 'flv', 'jar', 'zip',
        'eot', 'ttf', 'woff', 'woff2', 'ico', 'svg',
    ];

    /**
     * Copyright string which must be present in every non-binary private repo file
     *
     * @var string
     */
    private $copyrightAdobeString = 'ADOBE CONFIDENTIAL';

    /**
     * List of extensions for which copyright check must be skipped
     *
     * @var array
     */
    protected $copyrightSkipExtensions = ['csv', 'json', 'lock', 'md', 'txt'];

    /**
     * List of paths where copyright check must be skipped
     *
     * @var array
     */
    protected $copyrightSkipList = [
        'lib/web/legacy-build.min.js'
    ];

    /**
     * Whether copyright presence should be checked or not
     *
     * @var bool
     */
    protected $isCopyrightChecked;

    /**
     * Changed Private Repo files
     *
     * @var array
     */
    private $changedPrivateRepoFileList;

    /**
     * Changed public Repo files
     *
     * @var array
     */
    private $changedPublicRepoFileList;

    /**
     * Changed Private and public Repo files
     *
     * @var array
     */
    private $changedAllFiles;

    /**
     * Words to search for
     *
     * @var array
     */
    protected $_words = [];

    /**
     * Map of whitelisted paths to whitelisted words
     *
     * @var array
     */
    protected $_whitelist = [];

    /**
     * Path to base dir, used to calculate relative paths
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Component Registrar Class
     *
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * Map of phrase to exclude from the file content
     *
     * @var  array
     */
    private $exclude = [];

    /**
     * @param string|array $configFiles
     * @param string $baseDir
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     * @param bool $isCopyrightChecked
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    public function __construct($configFiles, $baseDir, $componentRegistrar, $isCopyrightChecked = false)
    {
        if (!is_dir($baseDir)) {
            throw new \Magento\TestFramework\Inspection\Exception("Base directory {$baseDir} does not exist");
        }
        $this->_baseDir = str_replace('\\', '/', realpath($baseDir));
        $this->componentRegistrar = $componentRegistrar;

        // Load config files
        if (!is_array($configFiles)) {
            $configFiles = [$configFiles];
        }
        foreach ($configFiles as $configFile) {
            $this->_loadConfig($configFile);
        }

        // Add config files to whitelist, as they surely contain banned words
        foreach ($configFiles as $configFile) {
            $configFile = str_replace('\\', '/', realpath($configFile));
            $this->_whitelist[$configFile] = [];
        }

        $this->_normalizeWhitelistPaths();

        // Final verifications
        if (!$this->_words) {
            throw new \Magento\TestFramework\Inspection\Exception('No words to check');
        }

        $this->isCopyrightChecked = $isCopyrightChecked;
        $this->changedPrivateRepoFileList = $this->getChangedPrivateRepoFileList();
        $this->changedPublicRepoFileList = $this->getChangedPublicRepoFileList();
        $this->changedAllFiles =
            array_merge($this->changedPrivateRepoFileList, $this->changedPublicRepoFileList);
    }

    /**
     * Load configuration from file, adding words and whitelisted entries to main config
     *
     * @param string $configFile
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    protected function _loadConfig($configFile)
    {
        if (!file_exists($configFile)) {
            throw new \Magento\TestFramework\Inspection\Exception("Configuration file {$configFile} does not exist");
        }
        try {
            $xml = new \SimpleXMLElement(file_get_contents($configFile));
        } catch (\Exception $e) {
            throw new \Magento\TestFramework\Inspection\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->_extractWords($xml)->_extractWhitelist($xml);
    }

    /**
     * Extract words from configuration xml
     *
     * @param \SimpleXMLElement $configXml
     * @return \Magento\TestFramework\Inspection\WordsFinder
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    protected function _extractWords(\SimpleXMLElement $configXml)
    {
        $words = [];
        $nodes = $configXml->xpath('//config/words/word');
        foreach ($nodes as $node) {
            $words[] = (string)$node;
        }
        $words = array_filter($words);

        $words = array_merge($this->_words, $words);
        $this->_words = array_unique($words);
        return $this;
    }

    /**
     * Extract whitelisted entries and words from configuration xml
     *
     * @param \SimpleXMLElement $configXml
     * @return \Magento\TestFramework\Inspection\WordsFinder
     * @throws \Magento\TestFramework\Inspection\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _extractWhitelist(\SimpleXMLElement $configXml)
    {
        // Load whitelist entries
        $whitelist = [];
        $exclude = [];
        $nodes = $configXml->xpath('//config/whitelist/item');
        foreach ($nodes as $node) {
            $path = $node->xpath('path');
            if (!$path) {
                throw new \Magento\TestFramework\Inspection\Exception(
                    'A "path" must be defined for the whitelisted item'
                );
            }
            $component = $node->xpath('component');
            if ($component) {
                $componentType = $component[0]->xpath('@type')[0];
                $componentName = $component[0]->xpath('@name')[0];
                $path = $this->componentRegistrar->getPath((string)$componentType, (string)$componentName)
                    . '/' . (string)$path[0];
            } else {
                $path = $this->_baseDir . '/' . (string)$path[0];
            }

            // Words
            $words = [];
            $wordNodes = $node->xpath('word');
            if ($wordNodes) {
                foreach ($wordNodes as $wordNode) {
                    $words[] = (string)$wordNode;
                }
            }
            $whitelist[$path] = $words;

            $excludeNodes = $node->xpath('exclude');
            $excludes = [];
            if ($excludeNodes) {
                foreach ($excludeNodes as $extractNode) {
                    $excludes[] = (string)$extractNode;
                }
            }

            if (isset($exclude[$path])) {
                $exclude[$path] = [...$excludes,...$exclude[$path]];
            } else {
                $exclude[$path] = $excludes;
            }
        }

        // Merge with already present whitelist
        foreach ($whitelist as $newPath => $newWords) {
            if (isset($this->_whitelist[$newPath])) {
                $newWords = [...$this->_whitelist[$newPath],...$newWords];
            }
            $this->_whitelist[$newPath] = array_unique($newWords);
        }
        foreach ($exclude as $newPath => $newWords) {
            if (isset($this->exclude[$newPath])) {
                $newWords = [...$this->exclude[$newPath],...$newWords];
            }
            $this->exclude[$newPath] = array_unique($newWords);
        }
        return $this;
    }

    /**
     * Normalize whitelist paths, so that they containt only native directory separators
     */
    protected function _normalizeWhitelistPaths()
    {
        $whitelist = $this->_whitelist;
        $this->_whitelist = [];
        foreach ($whitelist as $whitelistFile => $whitelistWords) {
            $whitelistFile = str_replace('\\', '/', $whitelistFile);
            $this->_whitelist[$whitelistFile] = $whitelistWords;
        }
    }

    /**
     * Checks the file content and name against the list of words. Do not check content of binary files.
     *
     * Exclude whitelisted entries.
     *
     * @param  string $file
     * @return array Words, found
     */
    public function findWords($file)
    {
        $foundWords = $this->_findWords($file);
        if (!$foundWords) {
            return [];
        }

        return self::_removeWhitelistedWords($file, $foundWords);
    }

    /**
     * Checks the file content and name against the list of words. Do not check content of binary files.
     *
     * @param  string $file
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _findWords($file)
    {
        $checkContents = !$this->_isBinaryFile($file);
        $path = $this->getSearchablePath($file);
        $contents = $checkContents ? file_get_contents($file) : '';
        if (isset($this->exclude[$file]) && !empty($this->exclude[$file])) {
            foreach ($this->exclude[$file] as $stringToEliminate) {
                $contents = str_replace($stringToEliminate, "", $contents);
            }
        }

        $foundWords = [];
        foreach ($this->_words as $word) {
            if (stripos($path, $word) !== false || stripos($contents, $word) !== false) {
                $foundWords[] = $word;
            }
        }

        if (substr($file, 0, strlen($this->prefix)) === $this->prefix) {
            $file = substr($file, strlen($this->prefix));
        }

        if ($contents && !$this->isCopyrightCheckSkipped($file)) {
            if (in_array($file, $this->changedPrivateRepoFileList)) {
                if ((strpos($contents, $this->copyrightAdobeString) === false)
                ) {
                    $foundWords[] = 'Copyright content is not valid';
                }
            }

            if (in_array($file, $this->changedAllFiles)) {
                if ($this->isCopyrightYearValid($contents) === false) {
                    $foundWords[] = 'Copyright year is not valid';
                }
            }

            if (in_array($file, $this->changedPublicRepoFileList)) {
                if ((strpos($contents, $this->copyrightAdobeString) !== false)
                ) {
                    $foundWords[] = $this->copyrightAdobeString . ' is not allowed in copyright content.';
                }
            }
        }
        return $foundWords;
    }

    /**
     * Check if copyright check skip
     *
     * @param string $path
     * @return bool
     */
    protected function isCopyrightCheckSkipped($path)
    {
        if (in_array(pathinfo($path, PATHINFO_EXTENSION), $this->copyrightSkipExtensions)) {
            return true;
        }
        foreach ($this->copyrightSkipList as $dir) {
            if (strpos($path, $dir) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check, whether file is a binary one
     *
     * @param string $file
     * @return bool
     */
    protected function _isBinaryFile($file)
    {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), $this->_binaryExtensions);
    }

    /**
     * Removes whitelisted words from array of found words
     *
     * @param  string $path
     * @param  array $foundWords
     * @return array
     */
    protected function _removeWhitelistedWords($path, $foundWords)
    {
        $path = str_replace('\\', '/', $path);
        foreach ($this->_whitelist as $whitelistPath => $whitelistWords) {
            if (strncmp($whitelistPath, $path, strlen($whitelistPath)) != 0) {
                continue;
            }

            if (!$whitelistWords) {
                // All words are permitted there
                return [];
            }
            $foundWords = array_diff($foundWords, $whitelistWords);
        }
        return $foundWords;
    }

    /**
     * Return the path for words search
     *
     * @param string $file
     * @return string
     */
    protected function getSearchablePath($file)
    {
        if (strpos($file, $this->_baseDir) === false) {
            return $file;
        }
        return substr($file, strlen($this->_baseDir) + 1);
    }

    /**
     * Changed Private File List
     *
     * @return array
     */
    private function getChangedPrivateRepoFileList(): array
    {
        $data = [];
        $changedFilesList = BP . self::CHANGED_PRIVATE_REPO_FILE;
        if (file_exists($changedFilesList)) {
            $changedFilesList = file($changedFilesList);
            foreach ($changedFilesList as $file) {
                $data[] = trim($file);
            }
        }
        return $data;
    }

    /**
     * Changed Public File List
     *
     * @return array
     */
    private function getChangedPublicRepoFileList(): array
    {
        $data = [];
        $changedFilesList = BP . self::CHANGED_PUBLIC_REPO_FILE;
        if (file_exists($changedFilesList)) {
            $changedFilesList = file($changedFilesList);
            foreach ($changedFilesList as $file) {
                $data[] = trim($file);
            }
        }
        return $data;
    }

    /**
     * Check is copyright year valid or not
     *
     * @param string $content
     * @return bool
     */
    private function isCopyrightYearValid(string $content): bool
    {
        $pattern = '/Copyright (\d{4}) Adobe/';
        if (preg_match($pattern, $content, $matches)) {
            $year = intval($matches[1]);
            if ($year >= 2010 && $year <= date("Y")) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
