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
namespace Kateglo\DefaultBundle\Service;

use Kateglo\DefaultBundle\ViewModel\Alphabet;
use Kateglo\DefaultBundle\ViewModel\Base;
use Kateglo\DefaultBundle\ViewModel\Link;
use Kateglo\DefaultBundle\ViewModel\Menu;
use Kateglo\DefaultBundle\ViewModel\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation\Service;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class BaseLink
{

    public function get(GenerateUrlInterface $controller)
    {
        $menu = new Menu(array
        (
            'start' => new Link($controller->generateUrl('kateglo_default_default_index'), 'start', 'Beranda'),
            'kamus' => new Link($controller->generateUrl('fos_user_registration_register'), 'index'),
            'tesaurus' => new Link($controller->generateUrl('fos_user_registration_register'), 'index'),
            'padanan' => new Link($controller->generateUrl('fos_user_registration_register'), 'index'),
        ));
        $user = new User(array
        (
            'register' => new Link($controller->generateUrl('fos_user_registration_register'), 'contents', 'register'),
            'login' => new Link($controller->generateUrl('fos_user_security_login'), 'contents', 'login'),
        ));

        $alphabet = new Alphabet(array
        (
            new Link($controller->generateUrl('fos_user_registration_register'), 'index', 'a'),
            new Link($controller->generateUrl('fos_user_security_login'), 'index', 'b'),
        ));

        return new Base($alphabet, $menu, $user);
    }
}
