<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModuleTest\Form\Element;

use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Form\Element\Proxy;

/**
 * Tests for the ObjectSelect element
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @covers  \DoctrineModule\Form\Element\ObjectSelect
 */
class ObjectSelectTest extends ProxyAwareElementTestCase
{
    /**
     * @var ObjectSelect
     */
    protected $element;

    protected function setUp()
    {
        parent::setUp();

        $this->element = new ObjectSelect();

        $this->prepareProxy();
    }

    public function testSetValueWithCollection()
    {
        $this->element->setAttribute('multiple', true);
        $this->element->setValue($this->values);

        $this->assertEquals([1, 2], $this->element->getValue());
    }

    public function testSetValueWithArray()
    {
        $this->element->setAttribute('multiple', true);
        $this->element->setValue($this->values->toArray());

        $this->assertEquals([1, 2], $this->element->getValue());
    }

    public function testSetValueSingleValue()
    {
        $value = $this->values->toArray();
        $this->element->setValue($value[0]);

        $this->assertEquals(1, $this->element->getValue());
    }

    public function testGetValueOptionsDoesntCauseInfiniteLoopIfProxyReturnsEmptyArrayAndValidatorIsInitialized()
    {
        $element = $this->getMockBuilder(get_class($this->element))
                        ->setMethods(['setValueOptions'])
                        ->getMock();

        $options = [];

        $proxy = $this->createMock(Proxy::class);
        $proxy->expects($this->exactly(2))
              ->method('getValueOptions')
              ->will($this->returnValue($options));

        $element->expects($this->never())
                ->method('setValueOptions');

        $this->setProxyViaReflection($proxy, $element);
        $element->getInputSpecification();
        $this->assertEquals($options, $element->getValueOptions());
    }

    public function testGetValueOptionsDoesntInvokeProxyIfOptionsNotEmpty()
    {
        $options = ['foo' => 'bar'];

        $proxy = $this->createMock(Proxy::class);
        $proxy->expects($this->once())
              ->method('getValueOptions')
              ->will($this->returnValue($options));

        $this->setProxyViaReflection($proxy);

        $this->assertEquals($options, $this->element->getValueOptions());
        $this->assertEquals($options, $this->element->getValueOptions());
    }

    public function testOptionsCanBeSetSingle()
    {
        $proxy = $this->createMock(Proxy::class);
        $proxy->expects($this->once())->method('setOptions')->with(['is_method' => true]);

        $this->setProxyViaReflection($proxy);

        $this->element->setOption('is_method', true);
    }
}
