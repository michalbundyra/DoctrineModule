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

namespace DoctrineModuleTest\Validator\Adapter;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Validator\ObjectExists;
use Zend\Validator\Exception;

/**
 * Tests for the ObjectExists validator
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \DoctrineModule\Validator\ObjectExists
 */
class ObjectExistsTest extends \PHPUnit_Framework_TestCase
{
    public function testCanValidateWithSingleField()
    {
        $repository = $this->createMock(ObjectRepository::class);

        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->will($this->returnValue(new \stdClass()));

        $validator = new ObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);

        $this->assertTrue($validator->isValid('matchValue'));
        $this->assertTrue($validator->isValid(['matchKey' => 'matchValue']));
    }

    public function testCanValidateWithMultipleFields()
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['firstMatchKey' => 'firstMatchValue', 'secondMatchKey' => 'secondMatchValue'])
            ->will($this->returnValue(new \stdClass()));

        $validator = new ObjectExists([
            'object_repository' => $repository,
            'fields'            => ['firstMatchKey', 'secondMatchKey'],
        ]);
        $this->assertTrue($validator->isValid([
            'firstMatchKey'  => 'firstMatchValue',
            'secondMatchKey' => 'secondMatchValue',
        ]));
        $this->assertTrue($validator->isValid(['firstMatchValue', 'secondMatchValue']));
    }

    public function testCanValidateFalseOnNoResult()
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $validator = new ObjectExists([
            'object_repository' => $repository,
            'fields'            => 'field',
        ]);
        $this->assertFalse($validator->isValid('value'));
    }

    public function testWillRefuseMissingRepository()
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new ObjectExists(['fields' => 'field']);
    }

    public function testWillRefuseNonObjectRepository()
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new ObjectExists(['object_repository' => 'invalid', 'fields' => 'field']);
    }

    public function testWillRefuseInvalidRepository()
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new ObjectExists(['object_repository' => new \stdClass(), 'fields' => 'field']);
    }

    public function testWillRefuseMissingFields()
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new ObjectExists([
            'object_repository' => $this->createMock(ObjectRepository::class),
        ]);
    }

    public function testWillRefuseEmptyFields()
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new ObjectExists([
            'object_repository' => $this->createMock(ObjectRepository::class),
            'fields'            => [],
        ]);
    }

    public function testWillRefuseNonStringFields()
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new ObjectExists([
            'object_repository' => $this->createMock(ObjectRepository::class),
            'fields'            => [123],
        ]);
    }

    public function testWillNotValidateOnFieldsCountMismatch()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Provided values count is 1, while expected number of fields to be matched is 2');

        $validator = new ObjectExists([
            'object_repository' => $this->createMock(ObjectRepository::class),
            'fields'            => ['field1', 'field2'],
        ]);
        $validator->isValid(['field1Value']);
    }

    public function testWillNotValidateOnFieldKeysMismatch()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'Field "field2" was not provided, but was expected since the configured field lists needs it for validation'
        );

        $validator = new ObjectExists([
            'object_repository' => $this->createMock(ObjectRepository::class),
            'fields'            => ['field1', 'field2'],
        ]);

        $validator->isValid(['field1' => 'field1Value']);
    }

    public function testErrorMessageIsStringInsteadArray()
    {
        $validator  = new ObjectExists([
            'object_repository' => $this->createMock(ObjectRepository::class),
            'fields'            => 'field',
        ]);

        $this->assertFalse($validator->isValid('value'));

        $messageTemplates = $validator->getMessageTemplates();

        $expectedMessage = str_replace(
            '%value%',
            'value',
            $messageTemplates[ObjectExists::ERROR_NO_OBJECT_FOUND]
        );
        $messages        = $validator->getMessages();
        $receivedMessage = $messages[ObjectExists::ERROR_NO_OBJECT_FOUND];

        $this->assertTrue($expectedMessage == $receivedMessage);
    }
}
