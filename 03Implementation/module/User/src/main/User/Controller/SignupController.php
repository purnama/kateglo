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

namespace User\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Dao\UserDao;
use User\Form\SignupForm;
use User\Entity\User;
use Momoku\Form\Annotation\AnnotationBuilder;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
final class SignupController extends AbstractActionController
{

    /**
     * @var \User\Dao\UserDao
     */
    private $dao;

    /**
     * @var \Zend\Form\Form
     */
    private $form;

    /**
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authService;

    /**
     * @Inject
     * @param UserDao $dao
     * @param SignupForm $form
     * @param AnnotationBuilder $annotationBuilder
     * @param AuthenticationService $authService
     */
    public function __construct(UserDao $dao, SignupForm $form,
                                AnnotationBuilder $annotationBuilder,
                                AuthenticationService $authService)
    {
        $this->dao = $dao;
        $this->form = $annotationBuilder->createForm($form);
        $this->authService = $authService;
    }

    public function indexAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('controller' => 'profile', 'action' => 'index'));
        }

        /**@var $request \Zend\Http\PhpEnvironment\Request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->form->setData($request->getPost());

            if ($this->form->isValid()) {
                $user = new User();
                $this->exchangeArrayToObject($user, $this->form->getData());
                $this->dao->persist($user);
                $this->dao->flush();

                return $this->redirect()->toRoute('user',  array('controller' => 'signup', 'action' => 'success'));
            }
        }

        return new ViewModel(array('form' => $this->form));
    }

    public function successAction()
    {
        return new ViewModel();
    }

    private function exchangeArrayToObject(User $user, array $data)
    {
        $user->setMail(isset($data['email']) ? $data['email'] : null);
        $user->setName(isset($data['name']) ? $data['name'] : null);
        $user->setPassword(isset($data['password']) ? md5($data['password']) : null);
        $user->setSince(new \DateTime('now'));
    }
}
