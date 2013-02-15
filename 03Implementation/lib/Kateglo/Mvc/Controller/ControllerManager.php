<?php
/**
 *  Kateglo: Kamus, Tesaurus dan Glosarium bahasa Indonesia.
 *
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
 * and is licensed under the GPL 2.0. For more information, see
 * <http://code.google.com/p/kateglo/>.
 *
 * @license <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html> GPL 2.0
 * @link    http://code.google.com/p/kateglo/
 * @copyright Copyright (c) 2009 Kateglo (http://code.google.com/p/kateglo/)
 */
namespace Kateglo\Mvc\Controller;

use net\stubbles\ioc\Binder;
use net\stubbles\ioc\binding\BindingScopes;
use Kateglo\Ioc\Binding\BindingIndex;
use Kateglo\Ioc\Binding\SessionBindingScope;
use Zend\Mvc\Exception\InvalidControllerException;

/**
 *
 * @see \Zend\ServiceManager\AbstractPluginManager
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class ControllerManager extends \Zend\Mvc\Controller\ControllerManager
{

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::createFromInvokable
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return null|\stdClass
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException|\Zend\Mvc\Exception\InvalidControllerException If resolved class does not exist or if creationOptions is defined
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];

        if (null === $this->creationOptions
            || (is_array($this->creationOptions) && empty($this->creationOptions))
        ) {
            $binder = new Binder(new BindingIndex(new BindingScopes(null, new SessionBindingScope())));
            $configuration = $this->serviceLocator->get("ApplicationConfig");
            //TODO: finish entity manager
            $entityManager = \Doctrine\ORM\EntityManager::create(
                $configuration['doctrine']['database'],
                \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($configuration['doctrine']['metadata'],
                    $_SERVER['APPLICATION_ENV'] === 'development'));
            $instance = $binder->getInjector()->getInstance($invokable);
        } else {
            throw new InvalidControllerException(sprintf(
                'Controller of type %s is invalid; must not have creation options',
                (is_object($invokable) ? get_class($invokable) : gettype($invokable))
            ));
            //$instance = new $invokable($this->creationOptions);
        }

        return $instance;
    }
}
