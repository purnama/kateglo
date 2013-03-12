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
namespace User\Validator;

use Zend\Validator\ValidatorInterface;
use User\Dao\UserDao;
use Zend\Validator\Exception\RuntimeException;
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class EmailNotExistValidator implements ValidatorInterface
{

    /**
     * @var \User\Dao\UserDao
     */
    private $dao;

    /**
     * @Inject
     * @param \User\Dao\UserDao $dao
     */
    public function __construct(UserDao $dao){
        $this->dao = $dao;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  string $value
     * @return bool
     * @throws \Zend\Validator\Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if(!is_string($value)){
            throw new RuntimeException("Value is not string.");
        }
        return !$this->dao->isEmailExist($value);
    }

    /**
     * Returns an array of messages that explain why the most recent isValid()
     * call returned false. The array keys are validation failure message identifiers,
     * and the array values are the corresponding human-readable message strings.
     *
     * If isValid() was never called or if the most recent isValid() call
     * returned true, then this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return array('This email address is already taken');
    }
}
