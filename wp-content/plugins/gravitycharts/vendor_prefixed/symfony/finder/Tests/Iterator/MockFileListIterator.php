<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by gravitykit on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityCharts\Symfony\Component\Finder\Tests\Iterator;

class MockFileListIterator extends \ArrayIterator
{
    public function __construct(array $filesArray = [])
    {
        $files = array_map(function ($file) { return new MockSplFileInfo($file); }, $filesArray);
        parent::__construct($files);
    }
}