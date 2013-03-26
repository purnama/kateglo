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
use Symfony\Component\DependencyInjection\Container;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\Routing\RouterInterface;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class BaseLink
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     * @InjectParams({
     *  "router" = @Inject("router")
     * })
     */
    public function __construct(RouterInterface $router){
        $this->router = $router;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return Base
     */
    public function get()
    {
        $menu = new Menu(array
        (
            'start' => new Link($this->router->generate('kateglo_default_default_index'), 'start', 'Beranda'),
            'kamus' => new Link($this->router->generate('fos_user_registration_register'), 'index'),
            'tesaurus' => new Link($this->router->generate('fos_user_registration_register'), 'index'),
            'padanan' => new Link($this->router->generate('fos_user_registration_register'), 'index'),
        ));
        $user = new User(array
        (
            'register' => new Link($this->router->generate('fos_user_registration_register'), 'contents', 'register'),
            'login' => new Link($this->router->generate('fos_user_security_login'), 'contents', 'login'),
        ));

        $alphabet = new Alphabet(array
        (
            new Link($this->router->generate('fos_user_registration_register'), 'index', 'a'),
            new Link($this->router->generate('fos_user_security_login'), 'index', 'b'),
        ));

        return new Base($alphabet, $menu, $user);
    }
}
