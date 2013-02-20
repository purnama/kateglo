<?php
/**
 *  Momoku Glue Stack Framework
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
 * and is licensed under the LGPL 3.0. For more information, see
 * <http://github.com/purnama/momoku>.
 *
 * @license <http://www.gnu.org/copyleft/lesser.html> LGPL 3.0
 * @link    http://github.com/purnama/momoku
 * @copyright Copyright (c) 2013 Momoku (http://github.com/purnama/momoku)
 */
namespace Momoku\Ioc\Binding;

use net\stubbles\ioc\binding\BindingException;
use net\stubbles\ioc\DefaultInjectionProvider;
use net\stubbles\ioc\InjectionProvider;
use net\stubbles\ioc\Injector;
use net\stubbles\lang\reflect\ReflectionClass;
/**
 *
 * @see     \net\stubbles\ioc\binding\ClassBinding
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class ClassBinding extends \net\stubbles\ioc\binding\ClassBinding
{


    /**
     *
     * @see \net\stubbles\ioc\binding\ClassBinding::getInstance
     * @param   Injector  $injector
     * @param   string    $name
     * @throws \net\stubbles\ioc\binding\BindingException
     * @return  mixed
     */
    public function getInstance(Injector $injector, $name)
    {
        $reflection = (new \ReflectionObject($this))->getParentClass();
        /** @var $instance \ReflectionProperty */
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        if (null !== $instance->getValue($this)) {
            return $instance->getValue($this);
        }
        /** @var $impl \ReflectionProperty */
        $impl = $reflection->getProperty('impl');
        $impl->setAccessible(true);
        if (is_string($impl->getValue($this))) {
            $impl->setValue($this, new ReflectionClass($impl));
        }

        /** @var $scope \ReflectionProperty */
        $scope = $reflection->getProperty('scope');
        $scope->setAccessible(true);
        /** @var $scopes \ReflectionProperty */
        $scopes = $reflection->getProperty('scopes');
        $scopes->setAccessible(true);
        if (null === $scope->getValue($this)) {
            if ($impl->getValue($this)->hasAnnotation('Singleton')) {
                $scope->setValue($this, $scopes->getValue($this)->getSingletonScope());
            }
            if ($impl->getValue($this)->hasAnnotation('Session')) {
                $scope->setValue($this, $scopes->getValue($this)->getSessionScope());
            }
        }

        /** @var $provider \ReflectionProperty */
        $provider = $reflection->getProperty('provider');
        $provider->setAccessible(true);
        /** @var $providerClass \ReflectionProperty */
        $providerClass = $reflection->getProperty('providerClass');
        $providerClass->setAccessible(true);
        /** @var $type \ReflectionProperty */
        $type = $reflection->getProperty('type');
        $type->setAccessible(true);
        if (null === $provider->getValue($this)) {
            if (null != $providerClass->getValue($this)) {
                $providerLocal = $injector->getInstance($providerClass->getValue($this));
                if (!($providerLocal instanceof InjectionProvider)) {
                    throw new BindingException('Configured provider class ' . $providerClass->getValue($this) . ' for type ' . $type->getValue($this) . ' is not an instance of net\stubbles\ioc\InjectionProvider.');
                }

                $provider->setValue($this, $providerLocal);
            } else {
                $provider->setValue($this, new DefaultInjectionProvider($injector, $impl->getValue($this)));
            }
        }

        if (null !== $scope->getValue($this)) {
            return $scope->getValue($this)->getInstance($impl->getValue($this), $provider->getValue($this));
        }

        return $provider->getValue($this)->get($name);
    }
}
