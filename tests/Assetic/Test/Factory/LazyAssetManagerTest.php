<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Factory;

use Assetic\Asset\AssetCollection;
use Assetic\Factory\LazyAssetManager;
use Assetic\Factory\Resource\AssetResource;

class LazyAssetManagerTest extends \PHPUnit_Framework_TestCase
{
    private $factory;
    private $am;

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder('Assetic\\Factory\\AssetFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->am = new LazyAssetManager($this->factory);
    }

    protected function tearDown()
    {
        $this->factory = null;
        $this->am = null;
    }

    public function testGetFromLoader()
    {
        $resource = $this->getMock('Assetic\\Factory\\Resource\\ResourceInterface');
        $loader = $this->getMock('Assetic\\Factory\\Loader\\FormulaLoaderInterface');
        $asset = $this->getMock('Assetic\\Asset\\AssetInterface');

        $formula = array(
            array('js/core.js', 'js/more.js'),
            array('?yui_js'),
            array('output' => 'js/all.js'),
        );

        $loader->expects($this->once())
            ->method('load')
            ->with($resource)
            ->will($this->returnValue(array('foo' => $formula)));
        $this->factory->expects($this->once())
            ->method('createAsset')
            ->with($formula[0], $formula[1], $formula[2] + array('name' => 'foo'))
            ->will($this->returnValue($asset));

        $this->am->setLoader('foo', $loader);
        $this->am->addResource($resource, 'foo');

        $this->assertSame($asset, $this->am->get('foo'), '->get() returns an asset from the loader');

        // test the "once" expectations
        $this->am->get('foo');
    }

    public function testGetResources()
    {
        $loader = $this->getMock('Assetic\\Factory\\Loader\\FormulaLoaderInterface');

        $asset1 = $this->getMock('Assetic\\Asset\\AssetInterface');
        $asset2 = $this->getMock('Assetic\\Asset\\AssetInterface');
        $assetCollection = new AssetCollection(array($asset1, $asset2));

        $resource1 = $this->getMock('Assetic\\Factory\\Resource\\ResourceInterface');
        $resource2 = $this->getMock('Assetic\\Factory\\Resource\\ResourceInterface');

        $formula = array(
            array('js/core.js', 'js/more.js'),
            array('?yui_js'),
            array('output' => 'js/all.js'),
        );

        $loader->expects($this->at(0))
            ->method('load')
            ->with($resource1)
            ->will($this->returnValue(array('baz' => $formula)));
        $loader->expects($this->at(1))
            ->method('load')
            ->with($resource2)
            ->will($this->returnValue(array()));
        $this->factory->expects($this->once())
            ->method('createAsset')
            ->with($formula[0], $formula[1], $formula[2] + array('name' => 'baz'))
            ->will($this->returnValue($assetCollection));

        $this->am->setLoader('foo', $loader);
        $this->am->setLoader('bar', $loader);

        $this->am->addResource($resource1, 'foo');
        $this->am->addResource($resource2, 'bar');

        $ret = $this->am->getResources();

        $this->assertCount(4, $ret);
        $this->assertTrue(in_array($resource1, $ret, true));
        $this->assertTrue(in_array($resource2, $ret, true));
        $this->assertTrue(in_array(new AssetResource($asset1), $ret));
        $this->assertTrue(in_array(new AssetResource($asset2), $ret));
    }

    public function testGetResourcesEmpty()
    {
        $this->am->getResources();
    }

    public function testSetFormula()
    {
        $this->am->setFormula('foo', array());
        $this->am->load();
        $this->assertTrue($this->am->hasFormula('foo'), '->load() does not remove manually added formulae');
    }

    public function testIsDebug()
    {
        $this->factory->expects($this->once())
            ->method('isDebug')
            ->will($this->returnValue(false));

        $this->assertSame(false, $this->am->isDebug(), '->isDebug() proxies the factory');
    }

    public function testGetLastModified()
    {
        $asset = $this->getMock('Assetic\Asset\AssetInterface');

        $this->factory->expects($this->once())
            ->method('getLastModified')
            ->will($this->returnValue(123));

        $this->assertSame(123, $this->am->getLastModified($asset));
    }
}
