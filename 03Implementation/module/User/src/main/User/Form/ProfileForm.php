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

use Zend\Form\Annotation;
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Annotation\Name("profile")
 * @Annotation\Attributes({"method":"post", "action": "/user/profile"})
 */
class ProfileForm
{
    /**
     * @Annotation\Name("name")
     * @Annotation\Type("Text")
     * @Annotation\Required(true)
     * @Annotation\Attributes({"class": "input-xlarge", "required": "required", "autocomplete": "off", "placeholder": "Nama Pengguna"})
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Validator({"name": "Alnum", "break_chain_on_failure": true})
     * @Annotation\Validator({"name": "User\Validator\EditNameNotExistValidator"})
     * @var string
     */
    protected $name;

    /**
     * @Annotation\Name("email")
     * @Annotation\Type("Email")
     * @Annotation\Required(true)
     * @Annotation\Attributes({"class": "input-xlarge", "required": "required", "autocomplete": "off", "placeholder": "Email"})
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Validator({"name": "EmailAddress", "break_chain_on_failure": true})
     * @Annotation\Validator({"name": "User\Validator\EditEmailNotExistValidator"})
     * @var string
     */
    protected $mail;

}
