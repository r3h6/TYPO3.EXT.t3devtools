<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Utility;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitor\NameResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\ExtensionScanner\Php\CodeStatistics;
use TYPO3\CMS\Install\ExtensionScanner\Php\MatcherFactory;
use TYPO3\CMS\Install\ExtensionScanner\Php\GeneratorClassesResolver;

class ExtensionScannerUtility
{
    private static $matchers;

    private static function getMatchers()
    {
        if (static::$matchers === null) {

            $files = GeneralUtility::getFilesInDir(
                GeneralUtility::getFileAbsFileName('EXT:t3devtools/Configuration/ExtensionScanner/Php/v9'),
                'php',
                true
            );

            static::$matchers = [];
            foreach ($files as $file) {
                static::$matchers[] = [
                    'class' => 'TYPO3\\CMS\\Install\\ExtensionScanner\\Php\\Matcher\\' . basename($file, '.php'),
                    'configurationFile' => $file,
                ];
            }
        }
        return static::$matchers;
    }

    public static function scanFile($absoluteFilePath)
    {
        $matchers = static::getMatchers();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        // Parse PHP file to AST and traverse tree calling visitors
        $statements = $parser->parse(file_get_contents($absoluteFilePath));

        $traverser = new NodeTraverser();
        // The built in NameResolver translates class names shortened with 'use' to fully qualified
        // class names at all places. Incredibly useful for us and added as first visitor.
        $traverser->addVisitor(new NameResolver());
        // Understand makeInstance('My\\Package\\Foo\\Bar') as fqdn class name in first argument
        $traverser->addVisitor(new GeneratorClassesResolver());
        // Count ignored lines, effective code lines, ...
        $statistics = new CodeStatistics();
        $traverser->addVisitor($statistics);

        // Add all configured matcher classes
        $matcherFactory = new MatcherFactory();
        $matchers = $matcherFactory->createAll($matchers);
        foreach ($matchers as $matcher) {
            $traverser->addVisitor($matcher);
        }

        $traverser->traverse($statements);

        // Gather code matches
        $matches = [[]];
        foreach ($matchers as $matcher) {
            /** @var \TYPO3\CMS\Install\ExtensionScanner\CodeScannerInterface $matcher */
            $matches[] = $matcher->getMatches();
        }
        $matches = array_merge(...$matches);

        return $matches;
    }
}