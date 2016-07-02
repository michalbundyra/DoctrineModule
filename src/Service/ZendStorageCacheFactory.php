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

namespace DoctrineModule\Service;

use Doctrine\Common\Cache\Cache;
use DoctrineModule\Cache\ZendStorageCache;
use DoctrineModule\Options\Cache as CacheOptions;
use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\StorageInterface;

/**
 * ZendStorageCache ServiceManager factory
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ZendStorageCacheFactory extends CacheFactory
{
    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     * @return Cache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $options CacheOptions */
        $options  = $this->getOptions($container, 'cache');
        $instance = $options->getInstance();

        if (! $instance) {
            // @todo move this validation to the options class
            throw new \RuntimeException('ZendStorageCache must have a referenced cache instance');
        }

        $cache = $container->get($instance);

        if (! $cache instanceof StorageInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Retrieved storage "%s" is not a Zend\Cache\Storage\StorageInterface instance, %s found',
                    $instance,
                    is_object($cache) ? get_class($cache) : gettype($cache)
                )
            );
        }

        return new ZendStorageCache($cache);
    }
}
