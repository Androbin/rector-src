<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 *
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Class_\RenameSpecFileToTestFileRector\RenameSpecFileToTestFileRectorTest
 */
final class RenameSpecFileToTestFileRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/r1VkPt/1
     */
    private const SPEC_REGEX = '#\/spec\/#';

    /**
     * @var string
     * @see https://regex101.com/r/WD4U43/1
     */
    private const SPEC_SUFFIX_REGEX = '#Spec\.php$#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename "*Spec.php" file to "*Test.php" file',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
// tests/SomeSpec.php
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// tests/SomeTest.php
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        $oldPathname = $smartFileInfo->getPathname();

        // ends with Spec.php
        if (! Strings::match($oldPathname, self::SPEC_SUFFIX_REGEX)) {
            return null;
        }

        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);

        $newPathName = $this->createPathName($oldPathname);
        $addedFileWithContent = new AddedFileWithContent($newPathName, $smartFileInfo->getContents());

        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);

        return null;
    }

    private function createPathName(string $oldRealPath): string
    {
        // suffix
        $newRealPath = Strings::replace($oldRealPath, self::SPEC_SUFFIX_REGEX, 'Test.php');

        // directory
        return Strings::replace($newRealPath, self::SPEC_REGEX, '/tests/');
    }
}
