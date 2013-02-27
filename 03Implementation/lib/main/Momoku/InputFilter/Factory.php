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

namespace Momoku\InputFilter;

use Momoku\Ioc\Binder;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\ValidatorChain;
use Zend\InputFilter\Exception;
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class Factory extends \Zend\InputFilter\Factory
{
    protected function populateValidators(ValidatorChain $chain, $validators)
    {
        foreach ($validators as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $chain->attach($validator);
                continue;
            }

            if (is_array($validator)) {
                if (!isset($validator['name'])) {
                    throw new Exception\RuntimeException(
                        'Invalid validator specification provided; does not include "name" key'
                    );
                }
                $breakChainOnFailure = false;
                if (isset($validator['break_chain_on_failure'])) {
                    $breakChainOnFailure = $validator['break_chain_on_failure'];
                }
                $name    = $validator['name'];
                if(class_exists($validator['name']) && in_array("Zend\Validator\ValidatorInterface", class_implements($validator['name']))){
                    $binder = Binder::get();
                    /**@var $validator \Zend\Validator\ValidatorInterface */
                    $validator = $binder->getInjector()->getInstance($validator['name']);
                    $chain->attach($validator, $breakChainOnFailure);
                    continue;
                }
                $options = array();
                if (isset($validator['options'])) {
                    $options = $validator['options'];
                }
                $chain->attachByName($name, $options, $breakChainOnFailure);
                continue;
            }

            throw new Exception\RuntimeException(
                'Invalid validator specification provided; was neither a validator instance nor an array specification'
            );
        }
    }
}
