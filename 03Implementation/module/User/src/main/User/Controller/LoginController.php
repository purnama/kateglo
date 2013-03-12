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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use User\Dao\UserDao;
use User\Authentication\Adapter;
use User\Form\LoginForm;
use Momoku\Form\Annotation\AnnotationBuilder;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
final class LoginController extends AbstractActionController
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
     * @var \User\Authentication\Adapter
     */
    private $adapter;

    /**
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authService;

    /**
     * @Inject
     * @param \User\Dao\UserDao $dao
     * @param \User\Form\LoginForm $form
     * @param \Momoku\Form\Annotation\AnnotationBuilder $annotationBuilder
     * @param \Zend\Authentication\AuthenticationService $authService
     * @param \User\Authentication\Adapter $adapter
     */
    public function __construct(UserDao $dao, LoginForm $form,
                                AnnotationBuilder $annotationBuilder,
                                AuthenticationService $authService,
                                Adapter $adapter)
    {
        $this->dao = $dao;
        $this->form = $annotationBuilder->createForm($form);
        $this->adapter = $adapter;
        $this->authService = $authService->setAdapter($adapter);
    }

    public function indexAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('controller' => 'profile', 'action' => 'index'));
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->form->setData($request->getPost());

            if ($this->form->isValid()) {
                $this->adapter->setIdentity($this->form->getData()['identity']);
                $this->adapter->setPassword(md5($this->form->getData()['password']));
                if ($this->authService->authenticate()->isValid()) {
                    return $this->redirect()->toRoute('user', array('controller' => 'login', 'action' => 'success'));
                } else {
                    return $this->failed();
                }
            } else {
                return $this->failed();
            }
        }

        return new ViewModel(array('form' => $this->form));

    }

    public function successAction()
    {
        if ($this->authService->hasIdentity()) {
            return new ViewModel();
        } else {
            return $this->redirect()->toRoute('user', array('controller' => 'login', 'action' => 'index'));
        }
    }

    private function failed()
    {
        return new ViewModel(array('form' => $this->form, 'messages' => 'Login failed. Please try again.'));
    }

}
