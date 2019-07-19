<?php

namespace Firesphere\CSPHeaders\Tests;

use Firesphere\CSPHeaders\Extensions\ControllerCSPExtension;
use Firesphere\CSPHeaders\View\CSPBackend;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class CSPBackendTest extends SapphireTest
{
    public function testInsertJSTag()
    {
        $js = 'alert("hello world");';

        /** @var CSPBackend $backend */
        $backend = Injector::inst()->get(CSPBackend::class);

        $backend->customScript($js);

        $tags = $backend->getCustomScripts();

        $this->assertContains('alert("hello world");', $tags[0]);
        $this->assertContains($js, ControllerCSPExtension::getInlineJS());
    }
}