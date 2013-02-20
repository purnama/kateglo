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

use net\stubbles\ioc\binding\BindingScopes;

/**
 *
 * @see \net\stubbles\ioc\binding\BindingIndex
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class BindingIndex extends \net\stubbles\ioc\binding\BindingIndex
{

    /**
     *
     * @see     \net\stubbles\ioc\binding\BindingIndex::bind
     * @param   string  $interface
     * @return  ClassBinding
     */
    public function bind($interface)
    {
        $reflection = (new \ReflectionObject($this))->getParentClass();
        /** @var $scopes \ReflectionProperty */
        $scopes = $reflection->getProperty('scopes');
        $scopes->setAccessible(true);
        return $this->addBinding(new ClassBinding($interface,
                $scopes->getValue($this)
            )
        );
    }
}
