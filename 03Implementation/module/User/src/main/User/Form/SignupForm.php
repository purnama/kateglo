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
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Email;
use Zend\Form\Element\Password;
use Zend\Form\Element\Button;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\I18n\Validator\Alnum;
use Zend\Validator\EmailAddress;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class SignupForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post')->setAttribute('action', '/user/signup');
        $this->add( (new Text())->setName('username')->setAttributes(array(
                'class' => 'input-xlarge',
                'required' => 'required',
                'placeholder' => 'Name Pengguna',
            )));
        $this->add( (new Email())->setName('email')->setAttributes(array(
            'class' => 'input-xlarge',
            'required' => 'required',
            'placeholder' => 'Email',
        )));
        $this->add( (new Password())->setName('password')->setAttributes(array(
            'class' => 'input-xlarge',
            'required' => 'required',
            'placeholder' => 'Kata kunci',
        )));
        $this->add( (new Password())->setName('password-retype')->setAttributes(array(
            'class' => 'input-xlarge',
            'required' => 'required',
            'placeholder' => 'Ulangi kata kunci',
        )));

        $inputFilter = new InputFilter();
        $username = (new Input('username'))->setRequired(true);
        $username->getValidatorChain()->attach(new Alnum());
        $inputFilter->add($username);

        $email = (new Input('email'))->setRequired(true);
        $email->getValidatorChain()->attach(new EmailAddress());
        $inputFilter->add($username);

        $password = (new Input('password'))->setRequired(true);
        $password->getValidatorChain()->attach(new StringLength(array('min' => 6)));
        $inputFilter->add($password);

        $passwordRetype = (new Input('password-retype'))->setRequired(true);
        $passwordRetype->getValidatorChain()
            ->attach(new StringLength(array('min' => 6)))
            ->attach((new Identical())->setToken('password'));
        $inputFilter->add($passwordRetype);
        $this->setInputFilter($inputFilter);

    }
}
