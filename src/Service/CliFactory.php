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

use DoctrineModule\Module;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * CLI Application ServiceManager factory responsible for instantiating a Symfony CLI application
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class CliFactory implements FactoryInterface
{
    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $events;

    /**
     * @var \Symfony\Component\Console\Helper\HelperSet
     */
    protected $helperSet;

    /**
     * @var array
     */
    protected $commands = array();

    /**
     * @param  ContainerInterface $container
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager(ContainerInterface $container)
    {
        if (null === $this->events) {
            /* @var $events \Zend\EventManager\EventManagerInterface */
            $events = $container->get('EventManager');

            $events->addIdentifiers(array(__CLASS__, 'doctrine'));

            $this->events = $events;
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $cli = new Application;
        $cli->setName('DoctrineModule Command Line Interface');
        $cli->setVersion(Module::VERSION);
        $cli->setHelperSet(new HelperSet);
        $cli->setCatchExceptions(true);
        $cli->setAutoExit(false);

        // Load commands using event
        $this->getEventManager($container)->trigger('loadCli.post', $cli, array('ServiceManager' => $container));

        return $cli;
    }

    /**
     * {@inheritDoc}
     * @return Application
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Application::class);
    }
}
